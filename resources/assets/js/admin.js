import Vue from 'vue';

if (! String.prototype.capitalize) {
    String.prototype.capitalize = function() {
        return this.charAt(0).toUpperCase() + this.slice(1);
    }
}

Vue.use(require('vue-resource'));
Vue.use(require('vue-validator'));

Vue.http.options.root = options.api.root;
Vue.http.headers.common['Authorization'] = `Bearer ${options.api.token}`;

if (!Array.prototype.findIndex) {
    Array.prototype.findIndex = function(predicate) {
        if (this === null) {
            throw new TypeError('Array.prototype.findIndex called on null or undefined');
        }

        if (typeof predicate !== 'function') {
            throw new TypeError('predicate must be a function');
        }

        var list = Object(this);
        var length = list.length >>> 0;
        var thisArg = arguments[1];
        var value;

        for (var i = 0; i < length; i++) {
            value = list[i];
            if (predicate.call(thisArg, value, i, list)) {
                return i;
            }
        }

        return -1;
    };
}

Vue.transition('fade', {
    enterClass: 'fadeIn',
    leaveClass: 'fadeOut'
});

import editCommandModal from './components/commands/edit-modal.vue'
import deleteCommandModal from './components/commands/delete-modal.vue'

if (document.querySelector('#commands')) {
    new Vue({
        el: '#commands',

        components: {
            'edit-command-modal': editCommandModal,
            'delete-command-modal': deleteCommandModal
        },

        data: {
            customCommands: [],
            systemCommands: []
        },

        ready() {
            this.$http.get('commands')
                .then((response) => {
                    let command;

                    for (command in response.data) {
                        switch (response.data[command].type) {
                            case 'system':
                                this.systemCommands.push(response.data[command]);
                                break;
                            case 'custom':
                                this.customCommands.push(response.data[command]);
                                break;
                        }
                    }

                    document.querySelector('#custom-commands-table tbody').className = '';
                    document.querySelector('#system-commands-table tbody').className = '';
                })
        },

        methods: {
            newCustomCommandModal() {
                this.$broadcast('openNewCustomCommandModal', null, 'New Command');
            },

            editCustomCommandModal(index) {
                this.$broadcast('openEditCustomCommandModal', this.customCommands[index]);
            },

            deleteCustomCommandModal(index) {
                this.$broadcast('openDeleteCustomCommandModal', this.customCommands[index]);
            },

            disableCustomCommand(index) {
                let command = this.customCommands[index];

                this.$http.put(`commands/${command.id}`, { disabled: !command.disabled })
                    .then((response) => {
                        command.disabled = response.data.disabled;
                    });
            },

            disableSystemCommand(index) {
                let command = this.systemCommands[index];

                this.$http.put(`commands/${command.id}`, { disabled: !command.disabled })
                    .then((response) => {
                        command.disabled = response.data.disabled;
                    });
            },

            editSystemCommandModal(index) {
                this.$broadcast('openEditSystemCommandModal', this.systemCommands[index]);
            },

            deleteFromCustomCommandsTable(command) {
                let index = this.customCommands.findIndex((row) => {
                    return row.id === command.id
                });

                if (index !== -1) {
                    this.customCommands.splice(index, 1);
                }
            },

            updateOrAddToSystemCommandTable(command) {
                let index = this.systemCommands.findIndex((row) => {
                    return row.id === command.id
                });

                if (index !== -1) {
                    this.systemCommands.splice(index, 1, command);
                } else {
                    this.systemCommands.unshift(command);
                }
            },

            updateOrAddToCustomCommandTable(command) {
                let index = this.customCommands.findIndex((row) => {
                    return row.id === command.id
                });

                if (index !== -1) {
                    this.customCommands.splice(index, 1, command);
                } else {
                    this.customCommands.unshift(command);
                }
            }
        }
    });
}

import giveawayEntries from './components/giveaway/entries.vue';
import giveawaySettings from './components/giveaway/settings.vue';
import giveawayControlPanel from './components/giveaway/control-panel.vue';

if (document.querySelector('#giveaway')) {
    const pusher = new Pusher(options.pusher.key, {
        encrypted: true,
        authEndPoints: '/pusher/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': options.csrf_token
            }
        }
    });

    new Vue({
        el: '#giveaway',

        components: {
            'giveaway-entries': giveawayEntries,
            'giveaway-settings': giveawaySettings,
            'giveaway-control-panel': giveawayControlPanel
        },

        events: {
            clearEntries() {
                this.$broadcast('clearEntries');
            },

            removeEntry(handle) {
                this.$broadcast('removeEntry', handle);
            }
        },

        ready() {
            let channel = pusher.subscribe(`private-${options.channel}`);

            channel.bind('giveaway.was-entered', (data) => {
                this.$broadcast('newEntry', { handle: data.handle, tickets: data.tickets });
            });
        }
    });
}

import currencySettings from './components/currency/settings.vue';

if (document.querySelector('#currency')) {
    new Vue({
        el: '#currency',

        components: {
            'currency-settings': currencySettings
        }
    });
}
