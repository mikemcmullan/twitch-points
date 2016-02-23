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
                            <div class="form-group" v-bind:class="{ 'has-error': !$editValidation.name.valid && $editValidation.name.modified }">
                                <label for="name-input">Name:</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="name-input"
                                    name="name"
                                    v-model="name"
                                    v-bind:disabled=""
                                    v-validate:name="{ alpha_dash_space: true, maxlength: 30, required: true }"
                                >

                                <span class="help-block" v-if="$editValidation.name.required && $editValidation.name.modified">Name is required.</span>
                                <span class="help-block" v-if="$editValidation.name.maxlength">Name cannot be longer than 30 characters.</span>
                                <span class="help-block" v-if="$editValidation.name.alpha_dash_space">Name may only contain apha numeric characters, dashes, underscores and spaces.</span>
                            </div><!-- .form-group -->

                            <div class="form-group">
                                <label for="">Lines</label>

                                <input type="range" min="0" max="100" v-model="lines">

                                <p class="help-block">
                                    The timer will only execute if {{ lines }} messages have been sent in chat in the past five minutes.
                                </p>
                            </div><!-- .form-group -->

                            <div class="form-group">
                                <label for="">Interval</label>

                                <input type="range" min="10" max="100" step="5" v-model="interval">

                                <p class="help-block">
                                    The timer will execute every {{ interval }} minutes.
                                </p>
                            </div><!-- .form-group -->

                            <div class="form-group" v-bind:class="{ 'has-error': !$editValidation.message.valid && $editValidation.message.modified }">
                                <label for="message-input">Message:</label>
                                <textarea
                                    class="form-control"
                                    id="message-input"
                                    name="command"
                                    v-model="message"
                                    v-bind:disabled=""
                                    placeholder="This is a message output by the bot when the timer is executed."
                                    v-validate:message="{ maxlength: 400, required: true }"
                                ></textarea>

                                <span class="help-block" v-if="$editValidation.message.required && $editValidation.message.modified">Message is required.</span>
                                <span class="help-block" v-if="$editValidation.message.maxlength">Message cannot be longer than 400 characters.</span>
                            </div><!-- .form-group -->
                        </div><!-- .modal-body -->

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" @click="close">Close</button>
                            <button type="submit" class="btn btn-primary" v-bind:disabled="saving || !$editValidation.valid">Save</button>
                        </div><!-- .modal-footer -->
                    </form><!-- form -->
                </validator>
            </div><!-- .modal-content -->
        </div><!-- .modal-dialog -->
    </div><!-- .modal -->
</template>

<script>
    export default {
        props: {},

        data: () => {
            return {
                title: false,
                modal: false,
                saving: false,
                id: null,
                name: false,
                message: false,
                lines: 30,
                interval: 10
            }
        },

        validators: {
            alpha_dash_space: {
                message: '',
                check: (value) => {
                    if (value.length !== 0) {
                        return /^[a-z-_\s0-9]+$/i.test(value);
                    }

                    return true;
                }
            }
        },

        ready() {
            this.$set('modal', $(this.$els.modal));

            this.modal.on('hide.bs.modal', () => {
                setTimeout(() => {
                    this.id = null;
                    this.name = null;
                    this.interval = 30;
                    this.lines = 10;
                    this.saving = false;
                }, 500);
            });
        },

        events: {
            openEditModal(timer) {
                this.title = 'Edit Timer';
                this.open(timer);
            },

            openNewModal() {
                this.title = 'New Timer';
                this.open();
                // this.$validatorReset();
            },
        },

        methods: {
            getErrorMessage($validator, field, property) {
                return $validator.messages[field] && $validator.messages[field][property];
            },

            save() {
                let request,
                    data = {
                        name: this.name,
                        lines: this.lines,
                        interval: this.interval,
                        message: this.message
                    };

                if (this.id === null) {
                    request = this.$http.post(`timers`, data, {
                        beforeSend: () => {
                            this.saving = true;
                        }
                    });
                } else {
                    request = this.$http.put(`timers/${this.id}`, data, {
                        beforeSend: () => {
                            this.saving = true;
                        }
                    });

                    console.log('sdfsd');
                }

                request.then((response) => {
                    this.$parent.updateOrAddToTable(response.data);

                    this.close();
                }, (response) => {
                    this.saving = false;
                });
            },

            open(timer) {
                if (timer) {
                    this.id = timer.id;
                    this.name = timer.name;
                    this.message = timer.message;
                    this.interval = timer.interval;
                    this.lines = timer.lines;
                }

                this.modal.modal('toggle');
            },

            close() {
                this.modal.modal('toggle');
            }
        }
    };
</script>
