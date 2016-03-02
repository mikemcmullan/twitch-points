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
                            <div class="form-group" v-bind:class="{ 'has-error': !$editValidation.command.valid && $editValidation.command.modified }">
                                <label for="command-input">Command:</label>
                                <input type="text" class="form-control" id="command-input" name="command" placeholder="!command" v-model="newCommand.command" v-bind:disabled="disabled.command" v-validate:command="{ minlength: 1, maxlength: 80, required: true }">

                                <span class="help-block" v-if="!$editValidation.command.valid && $editValidation.command.modified">Command requires a minimum of 1 characters and has a maximum 80 characters.</span>
                            </div><!-- .form-group -->

                            <div class="form-group">
                                <label for="level-input">Level:</label>
                                <select class="form-control" id="level-input" name="level" v-model="newCommand.level" v-bind:disabled="disabled.level">
                                    <option value="owner">Owner</option>
                                    <option value="admin">Admin</option>
                                    <option value="mod">Mod</option>
                                    <option value="everyone" selected="selected">Everyone</option>
                                </select>
                            </div><!-- .form-group -->

                            <div class="form-group" v-bind:class="{ 'has-error': !$editValidation.cool_down.valid && $editValidation.cool_down.modified }">
                                <label for="command-cool">Cool Down:</label>
                                <input type="number" class="form-control" id="command-cool" name="cool_down" placeholder="3" v-model="newCommand.cool_down" v-validate:cool_down="{ numeric_betwen: true, required: true }">

                                <span class="help-block" v-if="!$editValidation.cool_down.valid && $editValidation.cool_down.modified">Cool down must be a number between 0 and 86400.</span>
                                <span class="help-block">The amount of time in seconds before the command can be used again.</span>
                            </div>

                            <div class="form-group" v-bind:class="{ 'has-error': !$editValidation.response.valid && $editValidation.response.modified }">
                                <label for="response-input">Response:</label>
                                <textarea class="form-control" id="response-input" name="command" rows="4" v-model="newCommand.response" v-bind:disabled="disabled.response" placeholder="This is a response output by the bot when the command is executed." v-validate:response="{ minlength: 2, maxlength: 400, required: true }"></textarea>

                                <span class="help-block" v-if="!$editValidation.response.valid && $editValidation.response.modified">Response requires a minimum of 2 characters and has a maximum 400 characters.</span>
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
                title: 'Edit Command',
                originalCommand: false,
                newCommand: {
                    command: '',
                    level: 'everyone',
                    response: '',
                    cool_down: 3
                },
                modal: false,
                saving: false,
                disabled: {
                    command: false,
                    level: false,
                    response: false,
                    cool_down: false
                }
            }
        },

        validators: {
            numeric_betwen: {
                message: '',
                check: (value) => {
                    return (! isNaN(value)) && value >= 0 && value <= 86400;
                }
            },

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
                    this.newCommand.command = '';
                    this.newCommand.level = 'everyone';
                    this.newCommand.response = '';
                    this.newCommand.cool_down = 3;
                    this.originalCommand = false;
                    this.saving = false;
                    this.disabled.command = false;
                    this.disabled.level = false;
                    this.disabled.response = false;
                }, 500);
            });
        },

        events: {
            openEditCustomCommandModal(command, title) {
                this.title = 'Edit Command';
                this.open(command);
            },

            openNewCustomCommandModal() {
                this.title = 'New Command';
                this.open();
            },

            openEditSystemCommandModal(command) {
                this.title = 'Edit Command';

                this.disabled.command = true;

                this.open(command);
            },

            closeEditCustomCommandModal() {
                this.close();
            }
        },

        methods: {
            save() {
                let request,
                    data = {};

                if (! this.disabled.command) {
                    data.command = this.newCommand.command;
                }

                if (! this.disabled.level) {
                    data.level = this.newCommand.level;
                }

                if (! this.disabled.response) {
                    data.response = this.newCommand.response;
                }

                if (! this.disabled.cool_down) {
                    data.cool_down = this.newCommand.cool_down;
                }

                if (this.originalCommand === false) {
                    request = this.$http.post('commands', data, {
                        beforeSend: () => {
                            this.saving = true;
                        }
                    });
                } else {
                    request = this.$http.put(`commands/${this.originalCommand.id}`, data, {
                        beforeSend: () => {
                            this.saving = true;
                        }
                    });
                }

                request.then((response) => {
                    this.$parent.updateOrAddToCommandsTable(response.data);

                    this.close();
                }, (response) => {
                    this.saving = false;
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
            }
        }
    };
</script>
