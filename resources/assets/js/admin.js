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

                    document.querySelector('#commands-table tbody').className = '';
                })
        },

        methods: {
            newCommandModal() {
                this.$broadcast('openNewCommandModal', null, 'New Command');
            },

            editCommandModal(index) {
                this.$broadcast('openEditCommandModal', this.customCommands[index]);
            },

            deleteCommandModal(index) {
                this.$broadcast('openDeleteCommandModal', this.customCommands[index]);
            },

            deleteFromTable(command) {
                var index = null;
                for (var i in this.customCommands) {
                    if (this.customCommands[i].id == command.id) {
                        index = i;
                    }
                }

                if (index) {
                    this.customCommands.splice(index, 1);
                }
            },

            updateOrAddToTable(command) {
                var index = null;
                for (var i in this.customCommands) {
                    if (this.customCommands[i].id == command.id) {
                        index = i;
                    }
                }

                if (index) {
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
