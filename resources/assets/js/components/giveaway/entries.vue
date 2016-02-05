<script>
    export default {
        data: () => {
            return {
                entries: []
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

        },

        ready() {
            $("#giveaway-entries > .box-body").slimscroll({
                height: 400,
                alwaysVisible: false
            });
            
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
