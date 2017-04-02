<script>
    import Errors from '../../forms/Errors';

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
                },

                errors: new Errors()
            }
        },

        methods: {
            submit() {
                let request = this.$http.put('settings', {
                    'giveaway__use-tickets': this.useTickets,
                    'giveaway__started-text': this.startedText,
                    'giveaway__stopped-text': this.stoppedText,
                    'giveaway__ticket-max': this.ticketMax,
                    'giveaway__ticket-cost': this.ticketCost,
                    'giveaway__keyword': this.keyword
                }, {
                    beforeSend: (request) => {
                        this.saving = true;
                    }
                });

                request.then((response) => {
                    this.saving = false;
                    this.alert.visible = true;
                    this.alert.text = 'Settings saved.';
                    this.errors.clear();

                    setTimeout(() => {
                        this.alert.visible = false;
                    }, 2000);
                }, (response) => {
                    this.saving = false;
                    this.errors.clear();
                    this.errors.record(response.data.message.validation_errors);
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
