<script>
    export default {
        data: () => {
            return {
                keyword: '',
                startedText: '',
                stoppedText: '',
                ticketCost: 0,
                ticketMax: 0,
                saving: false,
                type: 'keyword',
                useTickets: false,
                alert: {
                    visible: false,
                    class: {
                        'text-success': true,
                        'text-danger': false
                    },
                    text: ''
                }
            }
        },

        methods: {
            submit() {
                let request = this.$http.put('settings', {
                    'giveaway.use-tickets': this.useTickets,
                    'giveaway.started-text': this.startedText,
                    'giveaway.stopped-text': this.stoppedText,
                    'giveaway.ticket-max': this.ticketMax,
                    'giveaway.ticket-cost': this.ticketCost,
                    'giveaway.keyword': this.keyword
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
            }
        },

        ready() {

        }
    }
</script>
