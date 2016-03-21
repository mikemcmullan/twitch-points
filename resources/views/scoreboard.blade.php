@extends('layouts.master')

@section('heading', 'Scoreboard')

@section('content')
<section class="content" id="currency">

    @include('partials.flash')

    @if ($page == 1)
    <div class="row">
        <div class="col-md-{{ $user ? 6 : 12 }}">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Check {{ $channel->getSetting('currency.name') }}</h3>
                </div><!-- .box-header -->

                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            {!! Form::open(['method' => 'get', 'class' => 'points-results-form']) !!}
                                <div class="form-group">
                                    {!! Form::label('handle', 'Chat Handle:'); !!}
                                    {!! Form::text('handle', $handle, ['class' => 'form-control']) !!}
                                    <p class="help-block">Enter your twitch username into the box above and click 'Check {{ $channel->getSetting('currency.name') }}'.</p>
                                </div>

                                {!! Form::submit('Check ' . $channel->getSetting('currency.name'), ['class' => 'btn btn-primary', 'id' => 'check-points']) !!}
                            {!! Form::close() !!}
                            <br>
                            @if ( ! $chatter && $handle !== '')
                                <div class="alert alert-warning">
                                    Handle not found.
                                </div>
                            @endif

                            @if ($chatter)
                                @include("../partials/point-results-table")
                            @endif
                        </div><!-- .col -->
                    </div><!-- .row -->
                </div><!-- .box-body -->
            </div><!-- .box -->
        </div><!-- .col -->

        @if ($user)
        <div class="col-md-6">
            <currency-settings inline-template>
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Currency Settings</h3>
                    </div><!-- .box-header -->

                    <div class="box-body">
                        <validator name="settingsValidation">
                            <form class="form-horizontal" @submit.prevent @submit="submit" novalidate>
                                <div class="form-group">
                                    <label for="control-amount" class="col-sm-3 control-label">Status</label>
                                    <div class="col-sm-9">
                                        <label class="radio-inline">
                                            {!! Form::radio('current-status', 'on', $status, ['v-model' => 'currentStatus']) !!} On
                                        </label>

                                        <label class="radio-inline">
                                            {!! Form::radio('current-status', 'off', !$status, ['v-model' => 'currentStatus']) !!} Off
                                        </label>
                                    </div>
                                </div><!-- .form-group -->

                                <div class="form-group" v-bind:class="{ 'has-error': !$settingsValidation.keyword.valid }">
                                    <label for="control-keyword" class="col-sm-3 control-label">Keyword</label>
                                    <div class="col-sm-9">

                                        <input type="text" name="keyword" id="control-keyword" class="form-control" v-model="keyword" value="{{ $channel->getSetting('currency.keyword') }}" v-validate:keyword="{ keywordFormat: true }">

                                        <span class="help-block" v-show="!$settingsValidation.keyword.valid">Keyword must be a single word and may be prepended with a !, maximum of 20 chatacters.</span>
                                        <span class="help-block">Viewers will enter this keyword to check how much {{ lcfirst($channel->getSetting('currency.name')) }} they have.</span>
                                    </div>
                                </div><!-- .form-group -->

                                <div class="form-group" v-bind:class="{ 'has-error': !$settingsValidation.amount.valid }">
                                    <label for="control-amount" class="col-sm-3 control-label">Amount</label>
                                    <div class="col-sm-9">
                                        {!! Form::number('amount', $channel->getSetting('currency.awarded'), ['class' => 'form-control', 'id' => 'control-amount', 'v-model' => 'amount', 'v-validate:amount' => "{ isInteger: true, min: 1, max: 1000, required: true }"]) !!}

                                        <span class="help-block" v-show="!$settingsValidation.amount.valid">Amount must a number and between 0 and 1000.</span>
                                        <p class="help-block">The amount of currency that is awards every interval. Min: 1, max: 1000</p>
                                    </div>
                                </div><!-- .form-group -->

                                <div class="form-group" v-bind:class="{ 'has-error': !$settingsValidation.timeinterval.valid }">
                                    <label for="control-time-interval" class="col-sm-3 control-label">Time Interval</label>
                                    <div class="col-sm-9">
                                        {!! Form::number('time-interval', $channel->getSetting('currency.interval'), ['class' => 'form-control', 'id' => 'control-time-interval', 'v-model' => 'timeInterval', 'v-validate:timeInterval' => "{ isInteger: true, min: 1, max: 60, required: true }"]) !!}

                                        <span class="help-block" v-show="!$settingsValidation.timeinterval.valid">Time interval must a number and between 0 and 60.</span>
                                        <p class="help-block">How often is currency awarded? This value is in minutes, min: 1, max: 60</p>
                                    </div>
                                </div><!-- .form-group -->

                                <div class="form-group">
                                    <div class="col-sm-offset-3 col-sm-9">
                                        <div class="checkbox">
                                            <label>
                                                {!! Form::checkbox('sync-status', 'yes', $channel->getSetting('currency.sync-status'), ['v-model' => 'syncStatus']) !!} Sync Status
                                            </label>
                                            <p class="help-block">Shound the currency system sync with the status of the stream? This is useful if you want to award currency when the stream is offline.</p>
                                        </div>
                                    </div>
                                </div><!-- .form-group -->

                                <div class="form-group">
                                    <div class="col-sm-offset-3 col-sm-9">
                                        <button type="submit" class="btn btn-primary" v-bind:disabled="saving || !$settingsValidation.valid">Save</button>
                                        <a v-show="alert.visible" class="animated btn btn-link" v-bind:class="alert.class" role="alert" transition="fade" stagger="2000">@{{ alert.text }}</a>
                                    </div>
                                </div><!-- .form-group -->
                            </validator>
                        </form>
                    </div><!-- .box-body -->
                </div><!-- .box -->
            </currency-settings>
        </div><!-- .col -->
        @endif

    </div><!-- .row -->
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Scoreboard</h3>
                    <em class="pull-right">({{ $count }} records in total)</em>
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

                                <tbody>
                                @forelse ($chatters as $chatter)
                                    <tr>
                                        <td>{{ $chatter['rank'] or '--'}}</td>
                                        <td>{{ $chatter['handle'] }} {!! $chatter['moderator'] ? '<span class="label label-primary">MOD</span>' : '' !!}</td>
                                        <td>{{ presentTimeOnline($chatter['minutes']) }}</td>
                                        <td>{{ floor($chatter['points']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">No data available.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table><!-- .body -->

                            <div class="text-center">
                                {!! $chatters->links() !!}
                            </div>
                        </div><!-- .col -->
                    </div><!-- .row -->
                </div><!-- .box-body -->
            </div><!-- .box -->
        </div><!-- .col -->
    </div><!-- .row -->
</scection><!-- .content -->
@endsection

@if ($user)
    @section('after-js')
        <script>
            var options = {
                api: {
                    token: '{{ $apiToken }}',
                    root: '//{{ config('app.api_domain') }}/{{ $channel->slug }}'
                },
                channel: '{{ $channel->slug }}'
            };
        </script>

        <script src="/assets/js/admin.js"></script>
    @endsection
@endif
