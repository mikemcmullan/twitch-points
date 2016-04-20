<script>
    import namedRankingsModal from './named-rankings-modal.vue';

    export default {
        data: () => {
            return {
                saving: false,
                amount: '',
                keyword: '',
                timeInterval: '',
                currentStatus: '',
                syncStatus: '',
                alert: {
                    visible: false,
                    class: {
                        'text-success': true
                    },
                    text: ''
                }
            }
        },

        ready() {
            // this.openRankingsModal();
        },

        components: {
            'named-rankings-modal': namedRankingsModal
        },

        methods: {
            openRankingsModal() {
                this.$broadcast('openRankingsModal');
            },

            submit() {
                let request = this.$http.put('settings', {
                    'currency.interval': this.timeInterval,
                    'currency.awarded': this.amount,
                    'currency.sync-status': this.syncStatus,
                    'currency.keyword': this.keyword
                }, {
                    beforeSend: (request) => {
                        this.saving = true;
                    }
                });

                request.then((response) => {
                    this.saving = false;
                    this.alert.visible = true;
                    this.alert.text = 'Settings saved.';

                    setTimeout(() => {
                        this.alert.visible = false;
                    }, 2000);
                }, (response) => {
                    if (response.status === 401 || response.status === 403) {
                        alert('There was a problem authenticating with the api. Please refresh the page.');
                    }
                });

                let url;

                if (this.currentStatus === 'on') {
                    url = 'currency/start-system';
                } else if (this.currentStatus === 'off') {
                    url = 'currency/stop-system';
                }

                this.$http.post(url).then(() => {}, () => {});
            }
        }
    }
</script>
