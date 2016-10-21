import Vue from 'vue';
import vuePagination from './components/paginator2.vue';

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
});

const FormatTwitchEmotes = require('./TwitchEmotes').default;
const FormatBTTVEmotes = require('./BTTVEmotes').default;

// Vue.config.debug = true;

// if (document.querySelector('#header-nav')) {
//     new Vue({
//         el: '#header-nav',
//
//         data: {
//             status: '',
//             loading: true
//         },
//
//         ready() {
//             this.$http.get('bot/status')
//                 .then((response) => {
//                     this.status = response.data.status;
//                     this.$els.join.className = '';
//                     this.$els.leave.className = '';
//                     this.$els.unavailable.className = '';
//
//                     this.loading = false;
//                 });
//         },
//
//         methods: {
//             joinChannel() {
//                 this.$http.post('bot/join')
//                     .then((response) => {
//                         if (response.data.error) {
//                             this.status = 'not_in_channel';
//                             return alert(response.data.message);
//                         }
//
//                         this.status = 'in_channel';
//                     });
//             },
//
//             leaveChannel() {
//                 this.$http.post('bot/leave')
//                     .then((response) => {
//                         if (response.data.error) {
//                             this.status = 'not_in_channel';
//                             return alert(response.data.message);
//                         }
//
//                         this.status = 'not_in_channel';
//                     });
//             }
//         }
//     });
// }

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

        filters: {
            searchCommands(commands) {
                if (this.searchKeyword === '') {
                    this.searchCount = 0;

                    return commands;
                }

                const result = commands.filter((command) => {
                    return command.command.toLowerCase().indexOf(this.searchKeyword.toLowerCase()) !== -1
                        || command.response.toLowerCase().indexOf(this.searchKeyword.toLowerCase()) !== -1;
                });

                if (this.searchCount !== result.length) {
                    this.$broadcast('goToPage', 1);
                }

                this.searchCount = result.length;

                return result;
            }
        },

        data: {
            commands: [],
            loading: true,
            loading2: true,
            disableDisableBtn: false,
            itemsPerPage: 10,
            itemsIndex: 0,
            searchKeyword: '',
            searchCount: 0,
            isSearching: false
        },

        computed: {
            noSearchResults() {
                return this.searchCount === 0 && this.searchKeyword !== '';
            },

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

            channel.bind('giveaway.was-cleared', (data) => {
                this.$broadcast('clearEntries');
            });

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

        data: {
            items: [],
            pagination: {
                total: 0,
                per_page: 12,
                current_page: 1,
                last_page: 0,
                from: 1,
                to: 12
            },
            loading: false,
            handle: '',
            viewer: {}
        },

        components: {
            'currency-settings': currencySettings,
            pagination: vuePagination
        },

        ready() {
            const data = scoreboard;
            this.viewer = viewer;

            Array.apply(null, this.$el.querySelectorAll('.hide')).forEach((elem) => {
                elem.classList.remove('hide');
            });

            if (viewer.handle) {
                this.handle = viewer.handle;
            }

            this.items = data.data;
            this.pagination.total = data.total;
            this.pagination.per_page = data.per_page;
            this.pagination.current_page = data.current_page;
            this.pagination.last_page = data.last_page;
            this.pagination.from = data.from;
            this.pagination.to = data.to;
        },

        methods: {
            loadData() {
                const page = this.pagination.current_page;

                this.items = [];
                this.loading = true;

                this.$http.get(`currency?page=${page}`)
                    .then((response) => {
                        const data = response.data;

                        this.items = data.data;
                        this.loading = false;
                    });
            }
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

//------------------------------------------------------------------------------
// Chat Logs
//------------------------------------------------------------------------------
function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

if (document.querySelector('#chat-logs')) {
    new Vue({
        el: '#chat-logs',

        data: {
            state: 'default',
            page: 0,
            logs: [],
            loadingBottom: true,
            loadingTop: false,
            searchKeyword: '',
            oldSearchKeyword: '',

            moreResultsOlder: true,
            moreResultsNewer: true,
            conversationDate: '',
            loadedTime: 0,
            highlight: '',
            dateOptions: {
                month: 'short',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                timeZoneName: 'short'
            }
        },

        computed: {
            formatedLoadTime() {
                const now = new Date();

                return now.toLocaleDateString('en-CA', this.dateOptions);
            },

            isSearch() {
                return this.state === 'search';
            },

            isConversation() {
                return this.state === 'conversation';
            },

            showLoadNewer() {
                if (this.loadingBottom) {
                    return false;
                }

                if (this.moreResultsNewer === false) {
                    return false;
                }

                return true;
            },

            showLoadOlder() {
                if (this.loadingBottom) {
                    return false;
                }

                if (this.moreResultsOlder === false) {
                    return false;
                }

                return true;
            }
        },

        ready() {
            this.loadedTime = new Date().toISOString();

            this.twitchEmotes = new FormatTwitchEmotes();
            this.bttvEmotes = new FormatBTTVEmotes();

            Array.apply(null, this.$el.querySelectorAll('.hide')).forEach((elem) => {
                elem.classList.remove('hide');
            });

            this.bttvEmotes.load(options.channel)
                .then(() => {
                    this.reset();
                });
        },

        methods: {
            loadOlder() {
                if (this.state === 'default') {
                    this.default();
                } else if (this.state === 'search') {
                    this.search(true);
                } else if (this.state === 'conversation') {
                    this.loadingBottom = true;
                    const date = this.logs[this.logs.length-1].created_at;
                    const perPage = 100;

                    this.$http.get(`chat-logs?page=1&starting-from=${date}&limit=${perPage}`)
                        .then((response) => {
                            this.loadingBottom = false;

                            if (response.data.data.length < perPage) {
                                this.moreResultsOlder = false;
                            }

                            response.data.data.forEach(this._proccessMessage);
                        });
                }
            },

            loadNewer() {
                if (this.state === 'conversation') {
                    this.loadingTop = true;
                    const date = this.logs[0].created_at;
                    const perPage = 100;

                    this.$http.get(`chat-logs?page=1&starting-from=${date}&limit=${perPage}&direction=newer`)
                        .then((response) => {
                            this.loadingTop = false;

                            if (response.data.data.length < perPage) {
                                this.moreResultsNewer = false;
                            }

                            const data = response.data.data;

                            data.forEach((message) => {
                                if (message.emotes !== null) {
                                    message.message = this.twitchEmotes.formatMessage(message.message, message.emotes);
                                }

                                message.message = this.bttvEmotes.formatMessage(message.message);
                                message.message = htmlEntities(message.message);
                                message.message = this.bttvEmotes.replacePlaceholders(message.message);
                                message.message = this.twitchEmotes.replacePlaceholders(message.message);
                                message.highlight = message.id === this.highlight.id ? true : false;
                                message.message = message.message.linkify();

                                this.logs.unshift(message);
                            });

                        });
                }
            },
            reset(loadDefault = true) {
                this.state = 'default';
                this.page = 0;
                this.searchKeyword = '';
                this.oldSearchKeyword = '';
                this.conversationDate = '';
                this.highlight = '';
                this.logs = [];

                this.moreResultsNewer = false;
                this.moreResultsOlder = false;

                if (loadDefault) {
                    this.default();
                }
            },

            default() {
                this.loadingBottom = true;
                this.state = 'default';
                this.moreResultsOlder = true;

                const perPage = 500;

                this.$http.get(`chat-logs?page=${++this.page}&starting-from=${this.loadedTime}&limit=${perPage}&direction=older`)
                    .then((response) => {
                        this.loadingBottom = false;

                        if (response.data.data.length < perPage) {
                            this.moreResultsOlder = false;
                        }

                        response.data.data.forEach(this._proccessMessage);
                    });
            },

            search(loadingMore = false) {
                // Is this the first or a new search.
                if (this.state !== 'search' || this.oldSearchKeyword !== this.searchKeyword) {
                    this.page = 0;
                    this.logs = [];
                }

                // The search button was pushed without the query changing.
                if (this.oldSearchKeyword === this.searchKeyword && loadingMore === false) {
                    return;
                }

                this.loadingBottom = true;
                this.state = 'search';
                this.highlight = '';
                this.oldSearchKeyword = this.searchKeyword;

                const perPage = 100;

                this.$http.get(`chat-logs/search?page=${++this.page}&term=${this.searchKeyword}&limit=${perPage}`)
                    .then((response) => {
                        this.loadingBottom = false;

                        if (response.data.data.length < perPage) {
                            this.moreResultsOlder = false;
                        }

                        response.data.data.forEach(this._proccessMessage);
                    });
            },

            conversation(message) {
                this.reset(false);

                this.state = 'conversation';
                this.highlight = message;
                this.loadingBottom = true;
                this.moreResultsOlder = true;
                this.moreResultsNewer = true;

                this.$http.get(`chat-logs/conversation?date=${message.created_at}`)
                    .then((response) => {
                        this.loadingBottom = false;

                        response.data.data.forEach(this._proccessMessage);
                    });
            },

            _proccessMessage(message) {
                if (message.emotes !== null) {
                    message.message = this.twitchEmotes.formatMessage(message.message, message.emotes);
                }

                message.message = this.bttvEmotes.formatMessage(message.message);
                message.message = htmlEntities(message.message);
                message.message = this.bttvEmotes.replacePlaceholders(message.message);
                message.message = this.twitchEmotes.replacePlaceholders(message.message);
                message.highlight = message.id === this.highlight.id ? true : false;
                message.message = message.message.linkify();

                this.logs.push(message);
            },

            formatDisplayDate(date) {
                const createdAt = new Date(date);
                const local = new Date(createdAt.getTime() - createdAt.getTimezoneOffset() * 60000);

                return local.toLocaleDateString('en-CA', this.dateOptions);
            }
        }
    });
}
