import Vue from 'vue';

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
            isSearching: false
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

                    this.$els.loop2.className = '';
                })
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
