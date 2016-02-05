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

// import editCommandModal from './components/edit-command-modal.vue'
// import deleteCommandModal from './components/delete-command-modal.vue'

// var commandsTable = new Vue({
//     el: '#commands-table',
//
//     components: {
//         'edit-command-modal': editCommandModal,
//         'delete-command-modal': deleteCommandModal
//     },
//
//     data: {
//         commands: data
//     },
//
//     ready() {
//         document.querySelector('#commands-table tbody').className = '';
//     },
//
//     methods: {
//         newCommandModal() {
//             this.$broadcast('openNewCommandModal');
//         },
//
//         editCommandModal(index) {
//             this.$broadcast('openEditCommandModal', this.commands[index]);
//         },
//
//         deleteCommandModal(index) {
//             this.$broadcast('openDeleteCommandModal', this.commands[index]);
//         },
//
//         deleteFromTable(command) {
//             var index = null;
//             for (var i in this.commands) {
//                 if (this.commands[i].id == command.id) {
//                     index = i;
//                 }
//             }
//
//             if (index) {
//                 this.commands.splice(index, 1);
//             }
//         },
//
//         updateOrAddToTable(command) {
//             var index = null;
//             for (var i in this.commands) {
//                 if (this.commands[i].id == command.id) {
//                     index = i;
//                 }
//             }
//
//             if (index) {
//                 this.commands.splice(index, 1, command);
//             } else {
//                 this.commands.unshift(command);
//             }
//
//         }
//     }
// });

// Vue.config.debug = true;

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
