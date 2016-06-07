<script>
    export default {
        data: () => {
            return {
                entries: [],
                disableButtons: false
            }
        },

        events: {
            newEntry(entry) {
                this.entries.unshift(entry);
            },

            clearEntries() {
                this.entries = []
            },

            removeEntry(handle) {
                let i = -1;

                $.each(this.entries, function(key, value) {
                    if (value.handle === handle) {
                        i = key;
                    }
                }.bind(this));

                if (i != -1) {
                    this.entries.splice(i, 1);
                }
            }
        },

        methods: {
            clearEntries() {
                this.entries = [];

                this.$http.post('giveaway/clear', {}, {
                    beforeSend: (request) => {
                        this.disableButtons = true;
                    }
                }).then((response) => {
                    this.disableButtons = false;
                    // this.$dispatch('clearEntries');
                },(response) => {
                    if (response.status === 401 || response.status === 403) {
                        alert('There was a problem authenticating with the api. Please refresh the page.');
                    }
                });
            }
        },

        ready() {
            $("#giveaway-entries > .box-body").slimscroll({
                height: 400,
                alwaysVisible: false
            });

            // this.entries.push({ handle: 'mcsmike', tickets: 5 });

            this.$http.get('giveaway/entries').then((response) => {
                let entry;

                for (entry in response.data) {
                    this.entries.push(response.data[entry]);
                }
            }, (response) => {

            });
        }
    }
</script>
