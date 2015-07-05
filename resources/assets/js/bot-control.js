new Vue({

    el: '#bot-log',

    data: {
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
        toggleProperty: function(property, delay) {
            var self = this,
                doToggle = function() {
                    self[property] = ! self[property];
                };

            if (delay) {
                setTimeout(doToggle, delay);
                return;
            }

            doToggle();
        },

        makeBotControlRequest: function(apiEndPoints, property) {
            var self = this,
                request = $.ajax({
                    url: apiEndPoints,
                    method: 'GET',
                    dataType: 'json',
                    beforeSend: function() {
                        self.toggleProperty(property);
                    }
                });

            request.done(function(response) {
                if (response.error) {
                    self.alerts.push({ level: 'danger', msg: response.error });

                    setTimeout(function() {
                        self.alerts.pop();
                    }, 2000);
                }

                self.toggleProperty(property, 4000);
            });
        },

        fetchEntries: function() {
            var self = this,
                request = $.ajax({
                    url: '/api/bot/log',
                    method: 'GET',
                    data: { offset: this.offset },
                    dataType: 'json',
                    beforeSend: function() {
                        self.toggleProperty('refreshing_log')
                    }
                });

            request.done(function(response) {
                if (response.error) {
                    self.entries = [response.error];
                    self.bot_status = 'Error';
                    return;
                }


                self.offset = response.new_offset;
                self.bot_status = response.status;

                for (var entry in response.entries) {
                    self.entries.unshift(response.entries[entry]);
                }

                self.toggleProperty('refreshing_log')
            });
        },

        startBot: function() {
            this.makeBotControlRequest('/api/bot/start', 'starting_bot')
        },

        stopBot: function() {
            this.makeBotControlRequest('/api/bot/stop', 'stopping_bot')
        },

        joinChannel: function() {
            this.makeBotControlRequest('/api/bot/join', 'joining_channel')
        },

        leaveChannel: function() {
            this.makeBotControlRequest('/api/bot/leave', 'leaving_channel')
        }
    }

});