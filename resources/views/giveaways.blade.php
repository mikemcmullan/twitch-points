@extends('layouts.master')

@section('heading', 'Giveaways')

@section('content')
<section class="content" id="giveaway">

    @include('partials.flash')

    <div class="row">
        <div class="col-md-6">
            <giveaway-control-panel inline-template>
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Control Panel</h3>
                    </div><!-- .box-header -->
                    <div class="box-body">
                        <p>Status: <span class="label text-uppercase" :class="{
                            'label-primary' : isStatusRunning,
                            'label-danger'  : isStatusStopped
                        }">@{{ status }}</span></p>

                        <div class="btn-group btn-group-justified giveaway-controls">
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary" @click="startGiveaway" :disabled="disableButtons">Start</button>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-danger" @click="stopGiveaway" :disabled="disableButtons">Stop</button>
                            </div>
                        </div>

                        <h4 class="text-center">The winner is: @{{ winner }}</h4>

                        <div class="btn-group btn-group-justified giveaway-select">
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary" :disabled="disableButtons" @click="selectWinner">Select Winner</button>
                            </div>
                        </div>
                    </div><!-- .box-body -->
                </div><!-- .box -->
            </giveaway-control-panel>

            <giveaway-entries inline-template>
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Entries (@{{ entries.length }})</h3>
                        <button type="button" class="btn btn-primary btn-xs pull-right" :disabled="disableButtons" @click="clearEntries">Clear Entries</button>
                    </div><!-- .box-header -->

                    <div class="box-body">
                        <ul class="nav nav-pills nav-stacked">
                            <li v-if="entries.length === 0"><a>No entries.</a></li>
                            <li v-for="entry in entries"><a>@{{ entry.handle }} <span class="text-green pull-right" v-if="entry.tickets > 0">@{{ entry.tickets }} tickets</span></a></li>
                        </ul>
                    </div><!-- .box-body -->
                </div><!-- .box -->
            </giveaway-entries>
        </div><!-- .col -->

        <div class="col-md-6">
            <giveaway-settings inline-template>
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Settings</h3>
                    </div><!-- .box-header -->

                    <div class="box-body" id="giveaway-settings">
                        <form @submit.prevent @submit="submit" novalidate>
                            <!-- <div class="form-group">
                                <label for="giveaway-type">Type</label><br>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default" @click="setType('regular')" v-bind:class="{ 'btn-active': type === 'regular' }">Regular</button>
                                    <button type="button" class="btn btn-default" @click="setType('currency')" v-bind:class="{ 'btn-active': type === 'currency' }">Currency Based</button>
                                </div>
                            </div> -->

                            <div class="form-group" v-bind:class="{ 'has-error': errors.has('giveaway__keyword') }">
                                <label for="giveaway-keyword">Keyword</label>
                                {!! Form::text('keyword', $channel->getSetting('giveaway.keyword'), ['id' => 'giveaway-keyword', 'class' => 'form-control', 'v-model' => 'keyword']) !!}

                                <span class="help-block" v-if="errors.has('giveaway__keyword')" v-text="errors.get('giveaway__keyword')"></span>

                                <span class="help-block" v-if="useTickets">Viewers would use this keyword + the amount of tickets to enter the giveaway. ex, <code>@{{ keyword }} #-of-tickets</code></span>
                                <span class="help-block" v-if="!useTickets">Viewers would use this keyword to enter the giveaway. ex, <code>@{{ keyword }}</code></span>
                            </div><!-- .form-group -->

                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        {!! Form::checkbox('use-tickets', 'yes', $channel->getSetting('giveaway.use-tickets'), ['v-model' => 'useTickets']) !!} Use tickets?
                                    </label>
                                    <p class="help-block">Should viewers be required to buy tickets using currency to enter the giveaway?</p>
                                </div>
                            </div>


                            <div class="form-group" v-show="useTickets" v-bind:class="{ 'has-error': errors.has('giveaway__ticket-cost') }">
                                <label for="giveaway-ticket-cost">Ticket Cost:</label>
                                {!! Form::number('time-interval', $channel->getSetting('giveaway.ticket-cost'), ['class' => 'form-control', 'id' => 'giveaway-ticket-cost', 'min' => 0, 'max' => 1000, 'v-model' => 'ticketCost']) !!}

                                <span class="help-block" v-if="errors.has('giveaway__ticket-cost')" v-text="errors.get('giveaway__ticket-cost')"></span>
                                <span class="help-block">How many 1UPs will a ticket cost. Max 1000</span>
                            </div>

                            <div class="form-group" v-show="useTickets" v-bind:class="{ 'has-error': errors.has('giveaway__ticket-max') }">
                                <label for="giveaway-ticket-max">Ticket Max:</label>
                                {!! Form::number('time-interval', $channel->getSetting('giveaway.ticket-max'), ['class' => 'form-control', 'id' => 'giveaway-ticket-max', 'min' => 0, 'max' => 100, 'v-model' => 'ticketMax']) !!}

                                <span class="help-block" v-if="errors.has('giveaway__ticket-max')" v-text="errors.get('giveaway__ticket-max')"></span>
                                <span class="help-block">The maximum amount of tickets a user may purchase. Max 100.</span>
                            </div>

                            <div class="form-group" v-bind:class="{ 'has-error': errors.has('giveaway__started-text') }">
                                <label for="giveaway-started">Giveaway Started Text</label>
                                {!! Form::textarea('started-text', $channel->getSetting('giveaway.started-text'), ['cols' => 30, 'rows' => 3, 'class' => 'form-control', 'id' => 'giveaway-started', 'v-model' => 'startedText']) !!}

                                <span class="help-block" v-if="errors.has('giveaway__started-text')" v-text="errors.get('giveaway__started-text')"></span>
                                <span class="help-block">The bot will display this message when the giveaway starts. Max characters 250.</span>
                            </div>

                            <div class="form-group" v-bind:class="{ 'has-error': errors.has('giveaway__stopped-text') }">
                                <label for="giveaway-stopped">Giveaway Stopped Text</label>
                                {!! Form::textarea('stopped-text', $channel->getSetting('giveaway.stopped-text'), ['cols' => 30, 'rows' => 3, 'class' => 'form-control', 'id' => 'giveaway-stopped', 'v-model' => 'stoppedText']) !!}

                                <span class="help-block" v-if="errors.has('giveaway__stopped-text')" v-text="errors.get('giveaway__stopped-text')"></span>
                                <span class="help-block">The bot will display this message when the giveaway is stopped. Max characters 250.</span>
                            </div>

                            <button type="submit" class="btn btn-primary" v-bind:disabled="saving">Save</button>
                            <span v-show="alert.visible" class="animated btn" v-bind:class="alert.class" role="alert" transition="fade" stagger="2000">@{{ alert.text }}</span>
                        </form>
                    </div><!-- .box-body -->
                </div><!-- .box -->
            </giveaway-settings>
        </div><!-- .col -->
    </div><!-- .row -->
</section>
@endsection

@if ($user)
    @section('after-js')
        <script>
            var options = {
                api: {
                    token: '{{ $apiToken }}',
                    root: '{{ makeDomain(config('app.api_domain'), '//') }}/{{ $channel->slug }}'
                },
                csrf_token: '{{ csrf_token() }}',
                giveaway: {
                    status: '{{ $status }}'
                },
                echo: {
                    url: '{{ config('services.echo.url') }}'
                },
                pusher: {
                    key: '{{ config('services.pusher.key') }}'
                },
                channel: '{{ $channel->slug }}'
            };
        </script>

        <script src="//js.pusher.com/3.0/pusher.min.js"></script>
        <script src="{{ config('services.echo.url') }}/socket.io/socket.io.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.7/jquery.slimscroll.min.js"></script>
        <script src="{{ elixir('assets/js/admin-vendor.js') }}"></script>
        <script src="{{ elixir('assets/js/admin.js') }}"></script>
    @endsection
@endif
