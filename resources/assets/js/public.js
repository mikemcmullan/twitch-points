import Vue from 'vue';
import vuePagination from './components/paginator2.vue';

Vue.use(require('vue-resource'));

Vue.http.options.root = options.api.root;

//------------------------------------------------------------------------------
// Commands
//------------------------------------------------------------------------------
import paginator from './components/paginator.vue';

if (document.querySelector('#commands')) {
    new Vue({
        el: '#commands',

        components: {
            'paginator': paginator
        },

        data: {
            commands: [],
            loading: true,
            loading2: true,
            itemsPerPage: 10,
            itemsIndex: 0,
            searchKeyword: '',
            searchCount: 0,
            isSearching: false,

            commandGroups: {},
            commandGroupNames: []
        },

        filters: {
            searchCommands(commands) {
                if (this.searchKeyword === '') {
                    this.searchCount = 0;

                    return commands;
                }

                const result = commands.filter((command) => {
                    return command.command.toLowerCase().indexOf(this.searchKeyword) !== -1
                        || command.response.toLowerCase().indexOf(this.searchKeyword) !== -1;
                });

                if (this.searchCount !== result.length) {
                    this.$broadcast('goToPage', 1);
                }

                this.searchCount = result.length;

                return result;
            }
        },

        computed: {
            customCommands() {
                return this.commands.filter((command) => {
                    return command.type === 'custom';
                });
            },

            noSearchResults() {
                return this.searchCount === 0 && this.searchKeyword !== '';
            },

            systemCommands() {
                return this.commands.filter((command) => {
                    return command.type === 'system';
                });
            }
        },

        ready() {
            this.$http.get('commands?type=custom&disabled=0')
                .then((response) => {
                    this.commands = this.commands.concat(response.data);
                    this.loading = false;

                    this.$els.loop.className = '';
                })

            this.$http.get('commands?type=system&orderBy=order&orderDirection=ASC&disabled=0')
                .then((response) => {
                    this.commands = this.commands.concat(response.data);
                    this.loading2 = false;

                    response.data.forEach((com) => {
                        const group = com.id.split('.')[0];

                        if (this.commandGroups[group] === undefined) {
                            this.commandGroups[group] = [com];
                        } else {
                            this.commandGroups[group].push(com);
                        }
                    });

                    this.commandGroupNames = Object.keys(this.commandGroups);

                    Vue.nextTick(() => {
                        Array.apply(null, this.$el.querySelectorAll('.system-commands-box.hide')).forEach((elem) => {
                            elem.classList.remove('hide');
                        });
                    });
                });
        }
    });
}

//------------------------------------------------------------------------------
// Currency
//------------------------------------------------------------------------------
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
            username: '',
            viewer: {}
        },

        components: {
            pagination: vuePagination
        },

        ready() {
            const data = scoreboard;
            this.viewer = viewer;

            Array.apply(null, this.$el.querySelectorAll('.hide')).forEach((elem) => {
                elem.classList.remove('hide');
            });

            if (viewer.username) {
                this.username = viewer.username;
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
// Quotes
//------------------------------------------------------------------------------
if (document.querySelector('#quotes')) {
    new Vue({
        el: '#quotes',

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
        }
    });
}
