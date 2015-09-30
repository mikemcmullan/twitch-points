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

                    $.post('/giveaway/reset', {
                        _token: csrfToken
                    });
                },

                startGiveAway: function() {
                    if (this.status === 'Running') {
                        return;
                    }

                    this.disableButtons = true;

                    $.post('/giveaway/start', {
                        _token: csrfToken
                    });
                },

                stopGiveAway: function() {
                    if (this.status === 'Stopped') {
                        return;
                    }

                    this.disableButtons = true;

                    $.post('/giveaway/stop', {
                        _token: csrfToken
                    });
                },

                selectWinner: function() {
                    if (entries.entries.length === 0) {
                        return;
                    }

                    this.disableButtons = true;

                    $.post('/giveaway/winner', {
                        _token: csrfToken
                    }, function(response) {
                        if (response.error) {
                            alert(response.error);
                            this.disableButtons = false;
                            return;
                        }

                        var i = -1;

                        $.each(entries.entries, function(key, value) {
                            if (value.handle === response.winner) {
                                i = key;
                            }
                        });

                        if (i != -1) {
                            entries.entries.splice(i, 1);
                        }

                        entries.entriesCount -= 1;

                        this.winner = response.winner;

                        this.disableButtons = false;
                    }.bind(this));
                }
            },

            filters: {

            },

            ready: function() {
                var self = this;

                self.status = '{{ $status }}';
                self.disableButtons = false;

                channel.bind('App\\Events\\GiveAwayWasStarted', function(data) {
                    self.status = 'Running';
                    self.statusClass = 'label-primary';
                    self.disableButtons = false;
                    console.log('Started');
                });

                channel.bind('App\\Events\\GiveAwayWasReset', function(data) {
                    entries.entries = [];
                    entries.entriesCount = 0;
                    self.winner = '';
                    self.disableButtons = false;
                    console.log('Reset');
                });

                channel.bind('App\\Events\\GiveAwayWasStopped', function(data) {
                    self.status = 'Stopped';
                    self.statusClass = 'label-danger';
                    self.disableButtons = false;
                    console.log('Stopped');
                });
            }
        });
    </script>
@endsection