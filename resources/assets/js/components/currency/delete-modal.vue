<template>
    <div class="modal fade" v-el:modal>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" @click="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{title}}</h4>
                </div><!-- .modal-header -->

                    <div class="modal-body text-center">
						<p>Are you sure you want to delete the chatter <strong>{{ user.displayName }}</strong>?</p>
                    </div><!-- .modal-body -->

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" @click="delete" v-bind:disabled="deleting">Delete</button>
                        <button type="button" class="btn btn-default" @click="close">Close</button>
                    </div><!-- .modal-footer -->
            </div><!-- .modal-content -->
        </div><!-- .modal-dialog -->
    </div><!-- .modal -->
</template>
<script>
	export default {
		props: {},

        data: () => {
			return {
				modal: null,
				title: 'Delete Chatter',
				user: {
					id: 0,
					username: '',
					displayName: ''
				},
				deleting: false
			}
		},

		ready() {
			this.$set('modal', $(this.$els.modal));

			this.modal.on('hide.bs.modal', () => {
                setTimeout(() => {
                    this.user = { id: 0, username: '', displayName: '' };
                }, 500);
            });
		},

		events: {
			openDeleteChatterModal(username, reloadAfter) {
				this.open(username, reloadAfter);
			}
		},

		methods: {
			delete() {
				this.$http.delete(`viewer/${this.user.username}`, {}, {
	                    beforeSend: (request) => {
	                        this.deleting = true;
	                    }
	                })
                    .then((response) => {
						this.updateData(this.user.username);
                        this.deleting = false;
						this.close();
                    }, (response) => {
                        this.deleting = false;
                    });
			},

			open(username) {
				this.$http.get(`viewer?username=${username}`)
					.then((response) => {
						this.user.id = response.data.id;
						this.user.username = response.data.username;
						this.user.displayName = response.data.display_name;
					});

                this.modal.modal('toggle');
            },

            close() {
                this.modal.modal('toggle');
            },

			updateData(username) {
				if (window.viewer.username === username) {
					this.$parent.viewer = {};
				}

				const userIndex = window.scoreboard.data.findIndex((user, index) => {
					return user.username === username;
				});

				window.scoreboard.data.splice(userIndex, 1);
			}
		},
	}
</script>
