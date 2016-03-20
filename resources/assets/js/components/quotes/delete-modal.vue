<template>
    <div class="modal fade" v-el:modal>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" @click="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ title }}</h4>
                </div><!-- .modal-header -->

                <form @submit.prevent @submit="delete">
                    <div class="modal-body">
                        Are you sure you want to delete the quote <code>#{{ quote.id }}</code> ?
                    </div><!-- .modal-body -->

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" @click="close">Cancel</button>
                        <button type="submit" class="btn btn-primary" v-bind:disabled="deleting">{{ deleting ? 'Deleting...' : 'Yes, Delete' }}</button>
                    </div><!-- .modal-footer -->
                </form><!-- form -->
            </div><!-- .modal-content -->
        </div><!-- .modal-dialog -->
    </div><!-- .modal -->
</template>

<script>
    export default {

        props: {},

        data: () => {
            return {
                title: 'Delete Quote',
                modal: false,
                quote: false,
                deleting: false
            }
        },

        ready() {
            this.$set('modal', $(this.$els.modal));

            this.modal.on('hide.bs.modal', () => {
                setTimeout(() => {
                    this.quote = false;
                    this.deleting = false;
                }, 500);
            });
        },

        events: {
            openDeleteModal(quote) {
                this.open(quote);
            }
        },

        methods: {
            delete() {
                this.$http.delete(`quotes/${this.quote.id}`, {}, {
                    beforeSend: (request) => {
                        this.deleting = true;
                    }
                })
                    .then((response) => {
                        this.$parent.deleteFromTable(this.quote);
                        this.close();
                    }, (response) => {
                        this.deleting = false;
                    });
            },

            open(quote) {
                this.quote = quote;
                this.modal.modal('toggle');
            },

            close() {
                this.modal.modal('toggle');
            }
        },
    }
</script>
