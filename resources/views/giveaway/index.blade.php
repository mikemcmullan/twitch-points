@extends('layouts.master')

@section('content')

    <div class="container">

        @include('partials.flash')

        <div class="row">
            <div class="col-md-12">

                <div class="page-header">
                    <h1>Giveaway</h1>
                </div><!-- .page-header -->

                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-default" id="giveaway-control-panel">
                            <div class="panel-heading">Control Panel</div>
                            <div class="panel-body">
                                <p>Status:
                                    <span
                                            v-text="status"
                                            class="label text-uppercase"
                                            v-class="
                                                label-primary : isStatusRunning,
                                                label-danger  : isStatusStopped
                                            "
                                    ></span>
                                </p>

                                <div class="btn-group btn-group-justified giveaway-controls">
                                    <div class="btn-group">
                                        <button type="button" v-attr="disabled: disableButtons" v-on="click: startGiveAway" class="btn btn-primary">Start</button>
                                    </div>
                                    <div class="btn-group">
                                        <button type="button" v-attr="disabled: disableButtons" v-on="click: stopGiveAway" class="btn btn-warning">Stop</button>
                                    </div>
                                    <div class="btn-group">
                                        <button type="button" v-attr="disabled: disableButtons" v-on="click: resetGiveAway" class="btn btn-danger">Reset</button>
                                    </div>
                                </div>

                                <div class="well giveaway-winner">
                                    <h4 class="text-center">The winner is: <span v-text="winner"></span></h4>
                                </div>

                                <div class="btn-group btn-group-justified giveaway-select">
                                    <div class="btn-group">
                                        <button type="button" v-attr="disabled: disableButtons" v-on="click: selectWinner" class="btn btn-primary">Select Winner</button>
                                    </div>
                                </div>
                            </div><!-- .panel-body -->
                        </div><!-- .panel -->

                        <div class="panel panel-default" id="settings">
                            <div class="panel-heading">Settings</div>
                            <div class="panel-body">
                                <div class="alert alert-success" v-class="hide : ! showAlert" v-text="messageText"></div>

                                {!! Form::open(['route' => ['giveaway_save_settings_path', $channel->slug], 'method' => 'post', 'v-on' => 'submit:saveSettings']) !!}
                                    <div class="form-group">
                                        <label for="ticket-cost">Ticket Cost:</label>
                                        <input type="number" id="ticket-cost" class="form-control" min="0" max="1000" v-model="ticketCost" value="{{ $channel->getSetting('giveaway.ticket-cost') }}" name="ticket-cost">
                                        <p class="help-block">How many {{ $channel->getSetting('currency.name') }} will a ticket cost. Max 1000</p>
                                    </div>

                                    <div class="form-group">
                                        <label for="ticket-max">Ticket Max:</label>
                                        <input type="number" id="ticket-max" class="form-control" min="0" max="100" v-model="ticketMax" value="{{ $channel->getSetting('giveaway.ticket-max') }}" name="ticket-max">
                                        <p class="help-block">The maximum amount of tickets a user may purchase. Max 100.</p>
                                    </div>

                                    <div class="form-group">
                                        <label for="giveaway-started">Giveaway Started Text</label>
                                        <textarea id="giveaway-started" class="form-control" v-model="giveawayStartedText" maxlength="250" cols="30" rows="3">{{ $channel->getSetting('giveaway.started-text') }}</textarea>
                                        <p class="help-block">The bot will display this message when the giveaway starts. Max characters 250.</p>
                                    </div>

                                    <div class="form-group">
                                        <label for="giveaway-stopped">Giveaway Stopped Text</label>
                                        <textarea id="giveaway-stopped" class="form-control" v-model="giveawayStoppedText" maxlength="250" cols="30" rows="3">{{ $channel->getSetting('giveaway.stopped-text') }}</textarea>
                                        <p class="help-block">The bot will display this message when the giveaway is stopped. Max characters 250.</p>
                                    </div>

                                    <button type="submit" v-attr="disabled : formSubmitting" class="btn btn-primary">Save</button>
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-default" id="giveaway-entries">
                            <div class="panel-heading">Entries - <span v-text="entriesCount">0</span> Total</div>
                            <div class="panel-body">
                                <div class="well bot-log">
                                    <ul class="bot-log-list">
                                        <li v-repeat="entries" v-text="handle + ' - ' + tickets + ' Tickets'">Loading...</li>
                                    </ul>
                                </div>
                            </div><!-- .panel-body -->
                        </div><!-- .panel -->
                    </div>
                </div>
            </div><!-- .col-*-* -->
        </div><!-- .row -->

    </div><!-- .container -->

@endsection

@section('after-js')
    <script src="https://js.pusher.com/3.0/pusher.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/vue/0.12.16/vue.min.js"></script>

    <script>
        var csrfToken = $('meta[name=csrf_token]').attr('content');

        var pusher = new Pusher('{{ env('PUSHER_KEY') }}', {
            encrypted: true,
            authEndPoints: '/pusher/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            }
        });

        var entries = {!! $entries !!},
            channel = pusher.subscribe('private-jonzzzzz');

        var entries = new Vue({
            el: '#giveaway-entries',

            data: {
                entries: [],
                entriesCount: 0
            },

            methods: {
                clearEntries: function() {
                    this.entries = [];
                    this.entriesCount = 0;
                },

                removeEntry: function(handle) {
                    var i = -1;

                    $.each(this.entries, function(key, value) {
                        if (value.handle === handle) {
                            i = key;
                        }
                    }.bind(this));

                    if (i != -1) {
                        this.entries.splice(i, 1);
                    }

                    this.entriesCount -= 1;
                }
            },

            ready: function() {
                var self = this;

                $.each(entries, function(key, value) {
                    self.entriesCount += 1;
                    self.entries.unshift({ handle: value.handle, tickets: value.tickets });
                });

                channel.bind('App\\Events\\GiveAwayWasEntered', function(data) {
                    console.log(data);
                    self.entriesCount += 1;
                    self.entries.unshift({ handle: data.handle, tickets: data.tickets });
                });
            }
        });

        var panel = new Vue({
            el: '#giveaway-control-panel',

            data: {
                disableButtons: true,
                winner: '',
                status: '',
                statusClass: ''
            },

            computed: {
                isStatusRunning: function() {
                    if (this.status === 'Running') {
                        return true;
                    }
                },

                isStatusStopped: function() {
                    if (this.status === 'Stopped') {
                        return true;
                    }
                }
            },

            methods: {
                resetGiveAway: function() {
                    this.disableButtons = true;

                    var xhr = $.ajax({
                        url: '/giveaway/reset',
                        method: 'POST',
                        data: {
                            _token: csrfToken
                        }
                    });

                    xhr.done(function(data) {
                        entries.clearEntries();
                        this.winner = '';
                        this.status = 'Stopped';
                        this.statusClass = 'label-danger';
                        this.disableButtons = false;
                        console.log('Reset');
                    }.bind(this));
                },

                startGiveAway: function() {
                    if (this.status === 'Running') {
                        return;
                    }

                    this.disableButtons = true;

                    var xhr = $.ajax({
                        url: '/giveaway/start',
                        method: 'POST',
                        data: {
                            _token: csrfToken
                        }
                    });

                    xhr.done(function(data) {
                        this.status = 'Running';
                        this.statusClass = 'label-primary';
                        this.disableButtons = false;
                        console.log('Started');
                    }.bind(this));
                },

                stopGiveAway: function() {
                    if (this.status === 'Stopped') {
                        return;
                    }

                    this.disableButtons = true;

                    var xhr = $.ajax({
                        url: '/giveaway/stop',
                        method: 'POST',
                        data: {
                            _token: csrfToken
                        }
                    });

                    xhr.done(function(data) {
                        this.status = 'Stopped';
                        this.statusClass = 'label-danger';
                        this.disableButtons = false;
                        console.log('Stopped');
                    }.bind(this));
                },

                selectWinner: function() {
                    if (entries.entries.length === 0) {
                        return;
                    }

                    this.disableButtons = true;

                    var xhr = $.ajax({
                        url: '/giveaway/winner',
                        method: 'POST',
                        data: {
                            _token: csrfToken
                        }
                    });

                    xhr.done(function(data) {
                        if (data.error) {
                            alert(data.error);
                            this.disableButtons = false;
                            return;
                        }

                        entries.removeEntry(data.winner);

                        this.winner = data.winner;
                        this.disableButtons = false;
                    }.bind(this));
                }
            },

            filters: {

            },

            ready: function() {
                this.status = '{{ $status }}';
                this.disableButtons = false;
            }
        });

        settings = new Vue({
            el: '#settings',

            data: {
                messageText: '',
                showAlert: false,
                formSubmitting: false,
                ticketCost: 0,
                ticketMax: 0,
                giveawayStartedText: '',
                giveawayStoppedText: ''
            },

            methods: {
                saveSettings: function(e) {
                    this.formSubmitting = true;

                    $.post('/giveaway/save-settings', {
                        _token: csrfToken,
                        'ticket-max': this.ticketMax,
                        'ticket-cost': this.ticketCost,
                        'giveaway-started-text': this.giveawayStartedText,
                        'giveaway-stopped-text': this.giveawayStoppedText
                    }, function() {
                        this.messageText = 'Settings Saved.';
                        this.showAlert = true;
                        this.formSubmitting = false;

                        setTimeout(function(){
                            this.showAlert = false;
                        }.bind(this), 3000);
                    }.bind(this));

                    e.preventDefault();
                }
            },

            ready: function() {

            }
        });
    </script>
@endsection