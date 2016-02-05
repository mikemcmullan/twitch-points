<template>
    <div class="modal fade" v-el:modal>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" @click="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ title }}</h4>
                </div><!-- .modal-header -->

                <form @submit.prevent @submit="save">
                    <div class="modal-body">
                        <div v-if="alert.visible" v-bind:class="alert.class" role="alert">{{ alert.text }}</div>

                        <div class="form-group" v-bind:class="{ 'has-error': validationErrors.command }">
                            <label for="command-input">Command:</label>
                            <input type="text" class="form-control" id="command-input" name="command" placeholder="!command" v-model="newCommand.command">
                            <span class="help-block" v-if="validationErrors.command">{{ validationErrors.command }}</span>
                        </div><!-- .form-group -->

                        <div class="form-group" v-bind:class="{ 'has-error': validationErrors.level }">
                            <label for="level-input">Level:</label>
                            <select class="form-control" id="level-input" name="level" v-model="newCommand.level">
                                <option value="owner">Owner</option>
                                <option value="admin">Admin</option>
                                <option value="mod">Mod</option>
                                <option value="everyone" selected="selected">Everyone</option>
                            </select>
                            <span class="help-block" v-if="validationErrors.level">{{ validationErrors.level }}</span>
                        </div><!-- .form-group -->

                        <div class="form-group" v-bind:class="{ 'has-error': validationErrors.response }">
                            <label for="response-input">Response:</label>
                            <textarea class="form-control" id="response-input" name="command" v-model="newCommand.response" placeholder="This is a response output by the bot when the command is executed."></textarea>
                            <span class="help-block" v-if="validationErrors.response">{{ validationErrors.response }}</span>
                        </div><!-- .form-group -->
                    </div><!-- .modal-body -->

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" @click="close">Close</button>
                        <button type="submit" class="btn btn-primary" v-if="!saving">Save</button>
                        <button type="submit" class="btn btn-primary" disabled="disabled" v-if="saving">Saving...</button>
                    </div><!-- .modal-footer -->
                </form><!-- form -->
            </div><!-- .modal-content -->
        </div><!-- .modal-dialog -->
    </div><!-- .modal -->
</template>

<script>
    export default {
        props: {
            title: {
                default: 'Edit Command',
            },
            apiToken: {
                type: String,
                twoWay: false,
                default: () => {
                    const tag = document.querySelector('meta[name=api-token]');

                    return tag ? tag.content : '';
                }
            },
        },

        data: () => {
            return {
                originalCommand: false,
                newCommand: {
                    command: '',
                    level: 'everyone',
                    response: ''
                },
                modal: false,
                validationErrors: {
                    command: false,
                    level: false,
                    response: false
                },
                alert: {
                    visible: false,
                    class: {
                        alert: true,
                        'alert-danger': true
                    },
                    text: ''
                },
                saving: false
            }
        },

        ready() {
            this.$set('modal', $(this.$els.modal));

            this.modal.on('hide.bs.modal', () => {
                setTimeout(() => {
                    this.newCommand.command = '';
                    this.newCommand.level = 'everyone';
                    this.newCommand.response = '';
                    this.originalCommand = false;
                    this.saving = false;

                    this.alert.visible = false;

                    for (var error in this.validationErrors) {
                        this.validationErrors[error] = false;
                    }
                }, 500);
            });
        },

        events: {
            openEditCommandModal(command) {
                this.open(command);
            },

            openNewCommandModal() {
                this.open();
            },

            closeEditCommandModal() {
                this.close();
            }
        },

        methods: {
            save() {
                let url,
                    obj = {
                        contentType: 'application/json',
                        dataType: 'json',
                        beforeSend: (xhr) => {
                            this.saving = true;
                        },
                        headers: {
                            'Authorization': `Bearer ${this.apiToken}`
                        },
                        data: JSON.stringify({
                            command: this.newCommand.command,
                            level: this.newCommand.level,
                            response: this.newCommand.response
                        })
                    };

                if (this.originalCommand === false) {
                    obj.url = 'http://api.twitch.dev/mcsmike/commands';
                    obj.method = 'post';
                } else {
                    obj.url = `http://api.twitch.dev/mcsmike/commands/${this.originalCommand.id}`;
                    obj.method = 'put';
                }

                let xhr = $.ajax(obj);

                xhr.error(this._handleErrors);

                xhr.done((data) => {
                    this.$parent.updateOrAddToTable(data);
                    this.close();
                });
            },

            open(command) {
                if (command) {
                    this.originalCommand = command
                    this.newCommand.command = command.command;
                    this.newCommand.level = command.level;
                    this.newCommand.response = command.response;
                }

                this.modal.modal('toggle');
            },

            close() {
                this.modal.modal('toggle');
            },

            _handleErrors(jqXHR, textStatus, errorThrown) {
                const responseError = JSON.parse(jqXHR.responseText);
                let error;

                this.saving = false;

                if (responseError.status === 403) {
                    this.alert.visible = true;
                    this.alert.text = 'There was an error authenticating with the api, please refresh the page.';
                    return;
                }

                // Reset the validation errors.
                for (error in this.validationErrors) {
                    this.validationErrors[error] = false;
                }

                // If there are error set them on the validation errors object.
                if (responseError.message.validation_errors) {
                    this.alert.visible = true;
                    this.alert.text = 'Please fix the validation errors below.';

                    for (error in responseError.message.validation_errors) {
                        this.validationErrors[error] = responseError.message.validation_errors[error][0];
                    }
                }
            },
        }
    };
</script>
