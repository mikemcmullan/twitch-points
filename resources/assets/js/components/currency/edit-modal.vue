<template>
    <div class="modal fade" v-el:modal>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" @click="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{title}}</h4>
                </div><!-- .modal-header -->

                    <div class="modal-body text-center">
						<p>Modifying {{ currencyName.toLowerCase() }} for <strong>{{ user.displayName }}</strong></p>

						<form @submit.prevent @submit="save" class="">
	                        <div class="form-group" v-bind:class="{ 'has-error': errors.has('amount') }">
	                            <label class="sr-only" for="amount">Amount</label>

								<div class="input-group" style="width: 150px; margin: 0 auto;">
	                            	<input type="number" class="form-control" id="amount-input" name="amount" min="0" max="10000" v-el:input v-model="amount">
									<div class="input-group-addon">{{ currencyName.toLowerCase() }}</div>
								</div>

	                            <span class="help-block" v-if="errors.has('amount')" v-text="errors.get('amount')"></span>
	                        </div><!-- .form-group -->

							<button type="button" class="btn btn-primary" @click="save('add')" v-bind:disabled="saving">Add</button>
							<button type="button" class="btn btn-danger" @click="save('remove')" v-bind:disabled="saving">Remove</button>
						</form><!-- form -->

                    </div><!-- .modal-body -->

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" @click="close">Close</button>
                    </div><!-- .modal-footer -->
            </div><!-- .modal-content -->
        </div><!-- .modal-dialog -->
    </div><!-- .modal -->
</template>

<script>
    import Errors from '../../forms/Errors';

    export default {
        props: {},

        data: () => {
            return {
                title: 'Edit Currency',
				state: 'add',
				amount: 0,
				currencyName: '',
				user: {
					username: '',
					displayName: ''
				},

				saving: false,
				modal: null,
                errors: new Errors()
            }
        },

        ready() {
            this.$set('modal', $(this.$els.modal));

			this.currencyName = window.currencyName;

			this.modal.on('show.bs.modal', () => {
				setTimeout(() => {
					this.$els.input.focus();
					this.$els.input.select();
				}, 500);
			});

            this.modal.on('hide.bs.modal', () => {
                setTimeout(() => {
					this.user.username = '';
					this.user.displayName = '';
					this.amount = 0;
                    this.errors.clear();
                }, 500);
            });
        },

        events: {
            openEditCurrencyModal(username, state) {
                this.open(username, state);
            },

            closeEditCurrencyModal() {
                this.close();
            }
        },

        methods: {
            save(state) {
				let request,
					beforeSend = () => {
						this.saving = true;
					};

				if (state === 'add') {
					request = this.$http.post('currency', { username: this.user.username, amount: this.amount }, { beforeSend });
				} else {
					request = this.$http.delete('currency', { username: this.user.username, amount: this.amount }, { beforeSend });
				}

				request.then(
					(response) => {
						this.saving = false;
						this.updateData(response.data.username, response.data.points);
						this.close();
					},
					(error)	=> {
						this.saving = false;
						this.errors.record({
							amount: [error.data.message]
						});
					}
				);
            },

            open(username, state) {
				this.$http.get(`viewer?username=${username}`)
					.then((response) => {
						this.user.username = response.data.username;
						this.user.displayName = response.data.display_name;
					});

				this.state = state;
                this.modal.modal('toggle');
            },

            close() {
                this.modal.modal('toggle');
            },

			updateData(username, amount) {
				if (window.viewer.username === username) {
					window.viewer.points = amount;
				}

				window.scoreboard.data.forEach((user, index) => {
					if (user.username === username) {
						window.scoreboard.data[index].points = amount;
					}
				});
			}
        }
    };
</script>
