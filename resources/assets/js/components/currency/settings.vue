<script>
    import namedRankingsModal from './named-rankings-modal.vue';
    import Errors from '../../forms/Errors';

    export default {
        data: () => {
            return {
                streaming,
                currencyName,
                currencyStatus,
                saving: false,
                keyword: '',
                onlineAmount: 1,
                onlineTimeInterval: 1,
                offlineAmount: 0,
                offlineTimeInterval: 1,
                syncStatus: false,
                alert: {
                    visible: false,
                    class: {
                        'text-success': true
                    },
                    text: ''
                },

                errors: new Errors(),

                currencyRate: 9,
                currencyRates: {
                    0: { amount: 1, interval: 10 },
                	1: { amount: 1, interval: 9 },
                	2: { amount: 1, interval: 8 },
                	3: { amount: 1, interval: 7 },
                	4: { amount: 1, interval: 6 },
                	5: { amount: 1, interval: 5 },
                	6: { amount: 1, interval: 4 },
                	7: { amount: 1, interval: 3 },
                	8: { amount: 1, interval: 2 },
                	9: { amount: 1, interval: 1 },
                	10: { amount: 2, interval: 1 },
                	11: { amount: 3, interval: 1 },
                	12: { amount: 4, interval: 1 },
                	13: { amount: 5, interval: 1 },
                	14: { amount: 6, interval: 1 },
                	15: { amount: 7, interval: 1 },
                	16: { amount: 8, interval: 1 },
                	17: { amount: 9, interval: 1 },
                	18: { amount: 10, interval: 1 }
                },
                currencyRateString: ''
            }
        },

        ready() {
            const startValue = Object.keys(this.currencyRates).find((r) => {
                return this.currencyRates[r].amount == onlineAmount && this.currencyRates[r].interval == onlineTimeInterval;
            });

            this.onlineAmount = startValue.amount;
            this.onlineTimeInterval = startValue.interval;

            this.setupCurrencyRateSlider(startValue);

            this.currencyRateString = this.makeCurrencyRateString();
        },

        watch: {
            currencyRate() {
                this.currencyRateString = this.makeCurrencyRateString();
                this.updateCurrencyRates();
            }
        },

        components: {
            'named-rankings-modal': namedRankingsModal
        },

        methods: {
            setupCurrencyRateSlider(startValue) {
                const rangeSlider = document.getElementById('currencyRateSliderOnline');

                noUiSlider.create(rangeSlider, {
                    start: startValue,
                    step: 1,
                    range: {
                        min: 0,
                        max: 18
                    }
                });

                rangeSlider.noUiSlider.on('update', (values, handle) => {
                    this.currencyRate = ~~values[0];
                });
            },

            openRankingsModal() {
                this.$broadcast('openRankingsModal');
            },

            updateCurrencyRates() {
                const rate = this.currencyRates[this.currencyRate];

                this.onlineAmount = rate.amount;
                this.onlineTimeInterval = rate.interval;
            },

            makeCurrencyRateString() {
                const rate = this.currencyRates[this.currencyRate];
                let str;

                if (this.currencyRate >= 9) {
                    str = 'minute';
                } else {
                    str = `${rate.interval} minutes`;
                }


                return `${rate.amount} ${this.currencyName.toLowerCase()} every ${str}`;
            },

            submit() {
                let request = this.$http.put('settings', {
                    'currency__name': this.currencyName,
                    'currency__online-interval': this.onlineTimeInterval,
                    'currency__online-awarded': this.onlineAmount,
                    'currency__offline-interval': this.offlineTimeInterval,
                    'currency__offline-awarded': this.offlineAmount,
                    'currency__keyword': this.keyword
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
                    if (response.status === 401 || response.status === 403) {
                        alert('There was a problem authenticating with the api. Please refresh the page.');
                    }

                    this.errors.clear();
                    this.errors.record(response.data.message.validation_errors);
                    this.saving = false;
                });
            },

            disableCurrency() {
                let request = this.$http.post('currency/stop-system', {}, {
                    beforeSend: (request) => {
                        this.currencyStatus = false;
                    }
                });

                request.then((response) => {

                }, (response) => {
                    if (response.status === 401 || response.status === 403) {
                        alert('There was a problem authenticating with the api. Please refresh the page.');
                    }

                    this.currencyStatus = true;
                });
            },

            enableCurrency() {
                let request = this.$http.post('currency/start-system', {}, {
                    beforeSend: (request) => {
                        this.currencyStatus = true;
                    }
                });

                request.then((response) => {

                }, (response) => {
                    if (response.status === 401 || response.status === 403) {
                        alert('There was a problem authenticating with the api. Please refresh the page.');
                    }

                    this.currencyStatus = false;
                });
            }
        }
    }
</script>
