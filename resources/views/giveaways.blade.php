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
                        <validator name="settingsValidation">
                            <form @submit.prevent @submit="submit" novalidate>
                                <div class="form-group" v-bind:class="{ 'has-error': !$settingsValidation.keyword.valid }">
                                    <label for="giveaway-keyword">Keyword</label>
                                    <div class="input-group">
                                        {!! Form::text('keyword', $channel->getSetting('giveaway.keyword'), ['id' => 'giveaway-keyword', 'class' => 'form-control', 'v-model' => 'keyword', 'v-validate:keyword' => "{ keywordFormat: true }"]) !!}
                                        <span class="input-group-addon">#-number-of-tickets</span>
                                    </div>

                                    <span class="help-block" v-show="!$settingsValidation.keyword.valid">Keyword must be a single word and may be prepended with a !, maximum of 20 chatacters.</span>
                                    <span class="help-block">Viewers would enter the keyword + the amount of tickets to enter the giveaway. ex, <code>@{{ keyword }} #-of-tickets</code></span>
                                </div><!-- .form-group -->

                                <div class="form-group" v-bind:class="{ 'has-error': !$settingsValidation.startedtext.valid }">
                                    <label for="giveaway-started">Giveaway Started Text</label>
                                    {!! Form::textarea('started-text', $channel->getSetting('giveaway.started-text'), ['cols' => 30, 'rows' => 3, 'class' => 'form-control', 'id' => 'giveaway-started', 'v-model' => 'startedText', 'v-validate:startedText' => "{ maxlength: 250 }"]) !!}

                                    <span class="help-block" v-show="!$settingsValidation.startedtext.valid">Started text cannot be longer than 250 characters.</span>
                                    <span class="help-block">The bot will display this message when the giveaway starts. Max characters 250.</span>
                                </div>

                                <div class="form-group" v-bind:class="{ 'has-error': !$settingsValidation.stoppedtext.valid  }">
                                    <label for="giveaway-stopped">Giveaway Stopped Text</label>
                                    {!! Form::textarea('stopped-text', $channel->getSetting('giveaway.stopped-text'), ['cols' => 30, 'rows' => 3, 'class' => 'form-control', 'id' => 'giveaway-stopped', 'v-model' => 'stoppedText', 'v-validate:stoppedText' => "{ maxlength: 250 }"]) !!}

                                    <span class="help-block" v-show="!$settingsValidation.stoppedtext.valid">Stopped text cannot be longer than 250 characters.</span>
                                    <span class="help-block">The bot will display this message when the giveaway is stopped. Max characters 250.</span>
                                </div>

                                <div class="form-group" v-bind:class="{ 'has-error': !$settingsValidation.ticketcost.valid }">
                                    <label for="giveaway-ticket-cost">Ticket Cost:</label>
                                    {!! Form::number('time-interval', $channel->getSetting('giveaway.ticket-cost'), ['class' => 'form-control', 'id' => 'giveaway-ticket-cost', 'min' => 0, 'max' => 1000, 'v-model' => 'ticketCost', 'v-validate:ticketCost' => "{ isInteger: true, min: 0, max: 1000, required: true }"]) !!}

                                    <span class="help-block" v-show="!$settingsValidation.ticketcost.valid">Ticket cost must a number and between 0 and 1000.</span>
                                    <span class="help-block">How many 1UPs will a ticket cost. Max 1000</span>
                                </div>

                                <div class="form-group" v-bind:class="{ 'has-error': !$settingsValidation.ticketmax.valid }">
                                    <label for="giveaway-ticket-max">Ticket Max:</label>
                                    {!! Form::number('time-interval', $channel->getSetting('giveaway.ticket-max'), ['class' => 'form-control', 'id' => 'giveaway-ticket-max', 'min' => 0, 'max' => 100, 'v-model' => 'ticketMax', 'v-validate:ticketMax' => "{ isInteger: true, min: 0, max: 100, required: true }"]) !!}

                                    <span class="help-block" v-show="!$settingsValidation.ticketmax.valid">Ticket max must be a number between 0 and 100.</span>
                                    <span class="help-block">The maximum amount of tickets a user may purchase. Max 100.</span>
                                </div>

                                <button type="submit" class="btn btn-primary" v-bind:disabled="saving || !$settingsValidation.valid">Save</button>
                                <span v-show="alert.visible" class="animated btn" v-bind:class="alert.class" role="alert" transition="fade" stagger="2000">@{{ alert.text }}</span>
                            </form>
                        </validator>
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
                pusher: {
                    key: '{{ config('services.pusher.key') }}'
                },
                channel: '{{ $channel->slug }}'
            };
        </script>

        <script src="//js.pusher.com/3.0/pusher.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.7/jquery.slimscroll.min.js"></script>
        <script src="/assets/js/admin.js"></script>
    @endsection
@endif
