<template>
    <div class="modal fade" v-el:modal>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" @click="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ title }}</h4>
                </div><!-- .modal-header -->

                <validator name="editValidation">
                    <form @submit.prevent @submit="save">
                        <div class="modal-body">
                            <div class="form-group" v-bind:class="{ 'has-error': errors.has('text') }">
                                <label for="message-input">Quote Text:</label>
                                <textarea
                                    class="form-control"
                                    id="text-input"
                                    name="text"
                                    rows="4"
                                    maxlength="500"                                    
                                    v-model="text"
                                ></textarea>

                                <span class="help-block" v-if="errors.has('text')" v-text="errors.get('text')"></span>
                            </div><!-- .form-group -->
                        </div><!-- .modal-body -->

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" @click="close">Close</button>
                            <button type="submit" class="btn btn-primary" v-bind:disabled="saving">{{ saving ? 'Saving...' : 'Save' }}</button>
                        </div><!-- .modal-footer -->
                    </form><!-- form -->
                </validator>
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
                title: false,
                modal: false,
                saving: false,
                id: null,
                text: null,
                errors: new Errors()
            }
        },

        ready() {
            this.$set('modal', $(this.$els.modal));

            this.modal.on('hide.bs.modal', () => {
                setTimeout(() => {
                    this.id = null;
                    this.text = null;
                    this.saving = false;
                    this.errors.clear();
                }, 500);
            });
        },

        events: {
            openEditModal(timer) {
                this.title = 'Edit Quote';
                this.open(timer);
            },

            openNewModal() {
                this.title = 'New Quote';
                this.open();
            },
        },

        methods: {
            save() {
                let request,
                    data = {
                        text: this.text
                    };

                if (this.id === null) {
                    request = this.$http.post(`quotes`, data, {
                        beforeSend: () => {
                            this.saving = true;
                        }
                    });
                } else {
                    request = this.$http.put(`quotes/${this.id}`, data, {
                        beforeSend: () => {
                            this.saving = true;
                        }
                    });
                }

                request.then((response) => {
                    this.$parent.updateOrAddToTable(response.data);
                    this.errors.clear();
                    this.close();
                }, (response) => {
                    this.errors.clear();
                    this.saving = false;
                    this.errors.record(response.data.message.validation_errors);
                });
            },

            open(timer) {
                if (timer) {
                    this.id = timer.id;
                    this.text = timer.text;
                }

                this.modal.modal('toggle');
            },

            close() {
                this.modal.modal('toggle');
            }
        }
    };
</script>
