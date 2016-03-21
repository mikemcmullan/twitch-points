import Vue from 'vue';

Vue.use(require('vue-resource'));
Vue.use(require('vue-validator'));

Vue.http.options.root = options.api.root;
Vue.http.headers.common['Authorization'] = `Bearer ${options.api.token}`;

Vue.transition('fade', {
    enterClass: 'fadeIn',
    leaveClass: 'fadeOut'
});

Vue.validator('keywordFormat', {
    message: 'invalid email address', // error message with plain string
    check: (val) => {
        return /^!?\w{2,20}$/.test(val);
    }
})

// Vue.config.debug = true;

//------------------------------------------------------------------------------
// Commands
//------------------------------------------------------------------------------
import editCommandModal from './components/commands/edit-modal.vue';
import deleteCommandModal from './components/commands/delete-modal.vue';
import paginator from './components/paginator.vue';

if (document.querySelector('#commands')) {
    new Vue({
        el: '#commands',

        components: {
            'edit-command-modal': editCommandModal,
            'delete-command-modal': deleteCommandModal,
            'paginator': paginator
        },

        data: {
            commands: [],
            loading: true,
            loading2: true,
            disableDisableBtn: false,
            itemsPerPage: 10,
            itemsIndex: 0
        },

        computed: {
            customCommands() {
                return this.commands.filter((command) => {
                    return command.type === 'custom';
                });
            },

            systemCommands() {
                return this.commands.filter((command) => {
                    return command.type === 'system';
                });
            }
        },

        ready() {
            this.$http.get('commands?type=custom')
                .then((response) => {
                    this.commands = this.commands.concat(response.data);
                    this.loading = false;

                    this.$els.loop.className = '';
                })

                this.$http.get('commands?type=system&orderBy=order&orderDirection=ASC')
                    .then((response) => {
                        this.commands = this.commands.concat(response.data);
                        this.loading2 = false;

                        this.$els.loop2.className = '';
                    })
        },

        methods: {
            _getCommand(value, key = 'id') {
                return this.commands.find((command) => {
                    return command[key] == value;
                });
            },

            newCustomCommandModal() {
                this.$broadcast('openNewCustomCommandModal', null, 'New Command');
            },

            editCommandModal(id) {
                this.$broadcast('openEditCustomCommandModal', this._getCommand(id));
            },

            deleteCommandModal(id) {
                this.$broadcast('openDeleteCustomCommandModal', this._getCommand(id));
            },

            disableCommand(id) {
                if (this.disableDisableBtn) {
                    return;
                }

                this.disableDisableBtn = true;
                let command = this._getCommand(id);

                this.$http.put(`commands/${command.id}`, { disabled: !command.disabled })
                    .then((response) => {
                        this.updateOrAddToCommandsTable(response.data);
                        this.disableDisableBtn = false;
                    });
            },

            deleteFromCommandsTable(command) {
                let index = this.commands.findIndex((row) => {
                    return row.id === command.id
                });

                if (index !== -1) {
                    this.commands.splice(index, 1);
                }
            },

            updateOrAddToCommandsTable(command) {
                let index = this.commands.findIndex((row) => {
                    return row.id === command.id
                });

                if (index !== -1) {
                    this.commands.splice(index, 1, command);
                } else {
                    this.commands.unshift(command);
                }
            }
        }
    });
}

//------------------------------------------------------------------------------
// Giveaways
//------------------------------------------------------------------------------
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

//------------------------------------------------------------------------------
// Currency
//------------------------------------------------------------------------------
import currencySettings from './components/currency/settings.vue';

if (document.querySelector('#currency')) {
    new Vue({
        el: '#currency',

        components: {
            'currency-settings': currencySettings
        }
    });
}

//------------------------------------------------------------------------------
// Timers
//------------------------------------------------------------------------------
import editTimerModal from './components/timers/edit-modal.vue'
import deleteTimerModal from './components/timers/delete-modal.vue'

if (document.querySelector('#timers')) {
    new Vue({
        el: '#timers',

        components: {
            'edit-timer-modal': editTimerModal,
            'delete-timer-modal': deleteTimerModal
        },

        data: {
            timers: [],
            loading: true,
            disableDisableBtn: false
        },

        ready() {
            this.$http.get('timers')
                .then((response) => {
                    this.timers = response.data;
                    this.loading = false;

                    this.$els.loop.className = '';
                })
        },

        methods: {
            _getTimer(value, key = 'id') {
                return this.timers.find((timer) => {
                    return timer[key] == value;
                });
            },

            editModal(id) {
                this.$broadcast('openEditModal', this._getTimer(id));
            },

            newModal() {
                this.$broadcast('openNewModal');
            },

            disable(id) {
                if (this.disableDisableBtn) {
                    return;
                }

                this.disableDisableBtn = true;
                let timer = this._getTimer(id);

                this.$http.put(`timers/${timer.id}`, { disabled: !timer.disabled })
                    .then((response) => {
                        this.updateOrAddToTable(response.data);
                        this.disableDisableBtn = false;
                    });
            },

            deleteModal(id) {
                this.$broadcast('openDeleteModal', this._getTimer(id));
            },

            updateOrAddToTable(timer) {
                let index = this.timers.findIndex((row) => {
                    return row.id === timer.id
                });

                if (index !== -1) {
                    this.timers.splice(index, 1, timer);
                } else {
                    this.timers.unshift(timer);
                }
            },

            deleteFromTable(timer) {
                let index = this.timers.findIndex((row) => {
                    return row.id === timer.id
                });

                if (index !== -1) {
                    this.timers.splice(index, 1);
                }
            }
        }
    });
}

//------------------------------------------------------------------------------
// Quotes
//------------------------------------------------------------------------------
import editQuoteModal from './components/quotes/edit-modal.vue'
import deleteQuoteModal from './components/quotes/delete-modal.vue'

if (document.querySelector('#quotes')) {
    new Vue({
        el: '#quotes',

        components: {
            'edit-quote-modal': editQuoteModal,
            'delete-quote-modal': deleteQuoteModal
        },

        data: {
            quotes: [],
            loading: true
        },

        ready() {
            this.$http.get('quotes')
                .then((response) => {
                    this.quotes = response.data;
                    this.loading = false;

                    this.$els.loop.className = '';
                })
        },

        methods: {
            _getQuote(value, key = 'id') {
                return this.quotes.find((quote) => {
                    return quote[key] == value;
                });
            },

            editModal(id) {
                this.$broadcast('openEditModal', this._getQuote(id));
            },

            newModal() {
                this.$broadcast('openNewModal');
            },

            // disable(id) {
            //     if (this.disableDisableBtn) {
            //         return;
            //     }
            //
            //     this.disableDisableBtn = true;
            //     let timer = this._getQuote(id);
            //
            //     this.$http.put(`timers/${timer.id}`, { disabled: !timer.disabled })
            //         .then((response) => {
            //             this.updateOrAddToTable(response.data);
            //             this.disableDisableBtn = false;
            //         });
            // },

            deleteModal(id) {
                this.$broadcast('openDeleteModal', this._getQuote(id));
            },

            updateOrAddToTable(quote) {
                let index = this.quotes.findIndex((row) => {
                    return row.id === quote.id
                });

                if (index !== -1) {
                    this.quotes.splice(index, 1, quote);
                } else {
                    this.quotes.unshift(quote);
                }
            },

            deleteFromTable(quote) {
                let index = this.quotes.findIndex((row) => {
                    return row.id === quote.id
                });

                if (index !== -1) {
                    this.quotes.splice(index, 1);
                }
            }
        }
    });
}
