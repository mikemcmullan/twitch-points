new Vue({

    el: '#bot-log',

    data: {
        buttons: {
            start: false,
            stop: false,
            join: false,
            leave: false
        },
        refreshing_log: false,
        starting_bot: false,
        stopping_bot: false,
        joining_channel: false,
        leaving_channel: false,
        bot_status: 'Unknown',
        offset: 0,
        entries: [],
        alerts: []
    },

    computed: {

    },

    ready: function() {
        var self = this;

        self.fetchEntries();

        var refreshLogTimer = function() {
            setTimeout(function() {
                self.fetchEntries();
                refreshLogTimer();
            }, 2000);
        }

        refreshLogTimer();
    },

    methods: {
        toggleButton: function(button, delay) {
            var self = this,
                doToggle = function() {
                    self[buttons][property] = ! self[buttons][property];
                };

            if (delay) {
                setTimeout(doToggle, delay);
                return;
            }

            doToggle();
        },

        makeBotControlRequest: function(apiEndPoints, callback) {
            var self = this,
                request = $.ajax({
                    url: apiEndPoints,
                    method: 'GET',
                    dataType: 'json'
                });

            request.done(callback);
        },

        fetchEntries: function() {
            var self = this,
                request = $.ajax({
                    url: '/api/bot/log',
                    method: 'GET',
                    data: { offset: this.offset },
                    dataType: 'json'
                });

            request.done(function(response) {
                if (response.error) {
                    self.entries = [response.error];
                    self.bot_status = 'Error';
                    return;
                }


                self.offset = response.new_offset;
                self.bot_status = response.status;

                if (self.bot_status !== 'RUNNING') {
                    self.buttons.join = false;
                    self.buttons.leave = false;
                    self.buttons.stop = false;
                    self.buttons.start = true;
                } else if(self.bot_status === "RUNNING") {
                    self.buttons.start = false;
                    self.buttons.join = true;
                    self.buttons.leave = true;
                    self.buttons.stop = true;
                } else {
                    self.buttons.join = true;
                    self.buttons.leave = true;
                    self.buttons.stop = true;
                }

                for (var entry in response.entries) {
                    self.entries.unshift(response.entries[entry]);
                }
            });
        },

        startBot: function() {
            var self = this;

            self.buttons.join = false;
            self.buttons.leave = false;
            self.buttons.stop = false;
            self.buttons.start = false;

            this.makeBotControlRequest('/api/bot/start', function(response) {
                if (response.error) {
                    self.alerts.push({ level: 'danger', msg: response.error });

                    setTimeout(function() {
                        self.alerts.pop();
                    }, 2000);
                }
            });
        },

        stopBot: function() {
            var self = this;

            self.buttons.join = false;
            self.buttons.leave = false;
            self.buttons.stop = false;
            self.buttons.start = false;

            this.makeBotControlRequest('/api/bot/stop', function(response) {
                if (response.error) {
                    self.alerts.push({ level: 'danger', msg: response.error });

                    setTimeout(function() {
                        self.alerts.pop();
                    }, 2000);
                }
            });
        },

        joinChannel: function() {
            var self = this;

            this.makeBotControlRequest('/api/bot/join', function(response) {
                if (response.error) {
                    self.alerts.push({ level: 'danger', msg: response.error });

                    setTimeout(function() {
                        self.alerts.pop();
                    }, 2000);
                }
            });
        },

        leaveChannel: function() {
            var self = this;

            this.makeBotControlRequest('/api/bot/leave', function(response) {
                if (response.error) {
                    self.alerts.push({ level: 'danger', msg: response.error });

                    setTimeout(function() {
                        self.alerts.pop();
                    }, 2000);
                }
            });
        }
    }

});