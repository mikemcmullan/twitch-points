<script>
    export default {
        data: () => {
            return {
                status: '',
                winner: '',
                disableButtons: false
            }
        },

        computed: {
            isStatusRunning() {
                if (this.status === 'Running') {
                    return true;
                }
            },

            isStatusStopped() {
                if (this.status === 'Stopped') {
                    return true;
                }
            }
        },

        methods: {
            startGiveaway() {
                if (this.status === 'Running') {
                    return;
                }

                this.$http.post('giveaway/start', {}, {
                    beforeSend: (request) => {
                        this.disableButtons = true;
                    }
                }).then((response) => {
                    this.status = 'Running';
                    this.disableButtons = false;
                },(response) => {
                    if (response.status === 401 || response.status === 403) {
                        alert('There was a problem authenticating with the api. Please refresh the page.');
                    }
                });
            },

            stopGiveaway() {
                if (this.status === 'Stopped') {
                    return;
                }

                this.$http.post('giveaway/stop', {}, {
                    beforeSend: (request) => {
                        this.disableButtons = true;
                    }
                }).then((response) => {
                    this.status = 'Stopped';
                    this.disableButtons = false;
                },(response) => {
                    if (response.status === 401 || response.status === 403) {
                        alert('There was a problem authenticating with the api. Please refresh the page.');
                    }
                });
            },

            resetGiveaway() {
                this.$http.post('giveaway/reset', {}, {
                    beforeSend: (request) => {
                        this.disableButtons = true;
                    }
                }).then((response) => {
                    this.status = 'Stopped';
                    this.winner = '';
                    this.disableButtons = false;
                    this.$dispatch('clearEntries');
                },(response) => {
                    if (response.status === 401 || response.status === 403) {
                        alert('There was a problem authenticating with the api. Please refresh the page.');
                    }
                });
            },

            selectWinner() {
                // if (this.status === 'Stopped') {
                //     return;
                // }

                this.$http.get('giveaway/winner', {}, {
                    beforeSend: (request) => {
                        this.disableButtons = true;
                    }
                }).then((response) => {
                    this.disableButtons = false;
                    this.winner = response.data.winner;
                    this.$dispatch('removeEntry', response.data.winner);
                },(response) => {
                    if (response.status === 409) {
                        this.disableButtons = false;
                        alert(response.data.message);
                    }

                    if (response.status === 401 || response.status === 403) {
                        alert('There was a problem authenticating with the api. Please refresh the page.');
                    }
                });
            }
        },

        ready() {
            this.status = options.giveaway.status;
        }
    }
</script>
