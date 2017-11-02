@extends('layouts.master')

@section('heading', 'Scoreboard')

@section('content')
<section class="content" id="currency">

    @include('partials.flash')

    <div class="row">
        @can('admin-channel', $channel)
        <div class="col-md-6">
        @else
        <div class="col-md-12">
        @endcan
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Check {{ $channel->getSetting('currency.name') }}</h3>
                </div><!-- .box-header -->

                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            @can('admin-channel', $channel)
                            <edit-currency-modal></edit-currency-modal>
                            <delete-chatter-modal></delete-chatter-modal>
                            @endcan

                            {!! Form::open(['method' => 'get', 'class' => 'points-results-form']) !!}
                                <div class="form-group">
                                    <label for="username">Chat Username:</label>
                                    <input type="text" name="username" id="username" class="form-control" value="{!! $username !!}" v-model="username">
                                    <p class="help-block">Enter your twitch username into the box above and click 'Check {{ $channel->getSetting('currency.name') }}'.</p>
                                </div>

                                <table class="table table-bordered points-results-table hide" v-show="!viewer.error && viewer.username">
                                    <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Name</th>
                                        <th>Minutes Online</th>
                                        <th>Points</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td>@{{ viewer.rank }}</td>
                                            <td>@{{ viewer.display_name }}</td>
                                            <td>@{{ viewer.time_online }} <span class="label label-primary" v-if="viewer.moderator">MOD</span></td>
                                            <td>
                                                @{{ viewer.points }}
                                                @can('admin-channel', $channel)
                                                <div class="pull-right">
                                                    <a href="#" @click.prevent="editCurrencyModal(viewer.username)">
                                                        <i class="fa fa-plus" aria-hidden="true"></i>
                                                        <i class="fa fa-minus" aria-hidden="true"></i>
                                                    </a>
                                                    &nbsp;
                                                    <a href="#" @click.prevent="deleteChatterModal(viewer.username)">
                                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                                    </a>
                                                </div>
                                                @endcan
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                {!! Form::submit('Check ' . $channel->getSetting('currency.name'), ['class' => 'btn btn-primary', 'id' => 'check-points']) !!}
                            {!! Form::close() !!}

                            <br>

                            <div class="alert alert-warning" v-if="viewer.error">
                                @{{ viewer.message }}
                            </div>
                        </div><!-- .col -->
                    </div><!-- .row -->
                </div><!-- .box-body -->
            </div><!-- .box -->
        </div><!-- .col -->

        @can('admin-channel', $channel)
        <div class="col-md-6">
            <currency-settings inline-template>
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Currency Settings</h3>
                    </div><!-- .box-header -->

                    <div class="box-body">
                        <named-rankings-modal :ranks='{!! json_encode($channel->getSetting('named-rankings', [])) !!}'></named-rankings-modal>

                        <div class="callout callout-warning clearfix" v-if="!currencyStatus">
                            Currency system is disabled, no currency is being awarded.
                            <a class="btn btn-xs btn-primary pull-right" @click="enableCurrency()" style="text-decoration: none">Enable</a>
                        </div>

                        <form class="form-horizontal" @submit.prevent @submit="submit" novalidate>

                            <div class="form-group">
                                <div class="col-sm-offset-2 col-md-10">
                                    <em>Currency is only awarded while the stream is online.</em>
                                </div>
                            </div>

                            <div class="form-group" v-bind:class="{ 'has-error': errors.has('currency__name') }">
                                <label for="currency-name" class="col-sm-2 control-label">Name</label>

                                <div class="col-sm-10">
                                    <input type="text" name="currency-name" id="currency-name" class="form-control" v-model="currencyName">

                                    <span class="help-block" v-if="errors.has('currency__name')" v-text="errors.get('currency__name')"></span>
                                    <span class="help-block">The name of your currency, 15 characters max.</span>
                                </div>
                            </div><!-- .form-group -->

                            <div class="form-group" v-bind:class="{ 'has-error': errors.has('currency__keyword') }">
                                <label for="control-keyword" class="col-sm-2 control-label">Command</label>
                                <div class="col-sm-10">
                                    <input type="text" name="keyword" id="control-keyword" class="form-control" v-model="keyword" value="{{ $channel->getSetting('currency.keyword') }}">

                                    <span class="help-block" v-if="errors.has('currency__keyword')" v-text="errors.get('currency__keyword')"></span>
                                    <span class="help-block">Viewers will enter this command to check how much {{ lcfirst($channel->getSetting('currency.name')) }} they have. Must be a single word and may be preceeded by a !.</span>
                                </div>
                            </div><!-- .form-group -->

                            <div class="form-group" v-bind:class="{ 'has-error': errors.has('currency__active-minutes') }">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" v-model="activeChatters"{{ $channel->getSetting('currency.only-active-chatters', false) === true ? ' checked=checked' : '' }}> Only award {{ lcfirst($channel->getSetting('currency.name')) }} to active chatters?
                                        </label>
                                    </div>

                                    <div v-if="activeChatters" style="padding-left: 20px; padding-top: 10px">
                                        Chatter must send a message every <input v-model="activeChattersMins" type="number" style="display: inline; width: 50px; padding-left: 5px; padding-right: 5px; text-align: center" class="form-control" min="1" max="60" value="{{ $channel->getSetting('currency.active-minutes', 15) }}"> minutes to remain active.
                                        <span class="help-block" v-if="errors.has('currency__active-minutes')">Number must be between 0 and 60.</span>
                                    </div>
                                </div>
                            </div><!-- .form-group -->

                            <div class="form-group">
                                <label class="col-sm-2 control-label">Rate</label>

                                <div class="col-sm-10">
                                    <div class="currency-rate-slider">
                                        <div class="left">
                                            <small>1 / 10 mins</small>
                                        </div>
                                        <div class="slider">
                                            <div id="currencyRateSliderOnline"></div>
                                            <span class="rating-string">
                                                <strong>@{{ currencyRateString }}</strong>
                                            </span>
                                        </div>
                                        <div class="right">
                                            <small>10 / 1 mins</small>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- .form-group -->

                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <button type="submit" class="btn btn-primary" v-bind:disabled="saving">Save</button>
                                    <a @click="openRankingsModal()" class="btn btn-default">Edit Rankings</a>
                                    <a v-show="alert.visible" class="animated btn btn-link" v-bind:class="alert.class" role="alert" transition="fade" stagger="2000">@{{ alert.text }}</a>

                                    <a class="btn btn-link pull-right" v-if="currencyStatus" @click="disableCurrency()">Disable Currency</a>
                                </div>
                            </div><!-- .form-group -->
                        </form>
                    </div><!-- .box-body -->
                </div><!-- .box -->
            </currency-settings>
        </div><!-- .col -->
        @endif

    </div><!-- .row -->

    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Scoreboard</h3>
                    <em class="pull-right">(@{{ pagination.total }} records in total)</em>
                </div><!-- .box-header -->

                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered points-results-table">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Name</th>
                                        <th>Time Online</th>
                                        <th>Points</th>
                                    </tr>
                                </thead>

                                <tbody class="hide">
                                    <tr v-for="chatter in items">
                                        <td>@{{ chatter.rank }}</td>
                                        <td>@{{ chatter.display_name }} <span class="label label-primary" v-if="chatter.moderator">MOD</span></td>
                                        <td>@{{ chatter.time_online }}</td>
                                        <td>
                                            @{{ chatter.points }}
                                            @can('admin-channel', $channel)
                                            <div class="pull-right">
                                                <a href="#" @click.prevent="editCurrencyModal(chatter.username)">
                                                    <i class="fa fa-plus" aria-hidden="true"></i>
                                                    <i class="fa fa-minus" aria-hidden="true"></i>
                                                </a>
                                                &nbsp;
                                                <a href="#" @click.prevent="deleteChatterModal(chatter.username)">
                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                            @endcan
                                        </td>
                                    </tr>

                                    <tr v-if="items.length === 0 && loading === false">
                                        <td colspan="4">No records found.</td>
                                    </tr>
                                </tbody>

                                <tbody v-if="loading">
                                    <tr>
                                        <td colspan="4" class="text-center"><img src="/assets/img/loader.svg" width="32" height="32" alt="Loading..."></td>
                                    </tr>
                                </tbody>
                            </table><!-- .body -->

                            <div class="text-center" v-if="pagination.total >= pagination.per_page">
                                <pagination :pagination="pagination" :callback="loadData"></pagination>
                            </div>
                        </div><!-- .col -->
                    </div><!-- .row -->
                </div><!-- .box-body -->
            </div><!-- .box -->
        </div><!-- .col -->
    </div><!-- .row -->
</scection><!-- .content -->
@endsection

@section('after-js')
    <script>
        var scoreboard = {!! $scoreboard !!};
        var viewer = {!! $chatter !!}
        var streaming = {{ $streaming ? 'true' : 'false' }};
        var currencyName = '{{ $channel->getSetting('currency.name') }}';
        var onlineAmount = '{{ $channel->getSetting('currency.online-awarded') }}';
        var onlineTimeInterval = '{{ $channel->getSetting('currency.online-interval') }}';
        var currencyStatus = {{ $channel->getSetting('currency.status') ? 'true' : 'false' }};

        var options = {
            api: {
                token: '{{ $apiToken }}',
                root: '//{{ config('app.api_domain') }}/{{ $channel->slug }}'
            },
            channel: '{{ $channel->slug }}'
        };
    </script>


    @can('admin-channel', $channel)
        <script src="{{ elixir('assets/js/admin-vendor.js') }}"></script>
        <script src="{{ elixir('assets/js/admin.js') }}"></script>
    @else
        <script src="{{ elixir('assets/js/public.js') }}"></script>
    @endcan
@endsection
