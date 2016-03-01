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
