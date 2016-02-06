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
                                <input type="text" class="form-control" id="command-input" name="command" placeholder="!command" v-model="newCommand.command" v-validate:command="{ minlength: 1, maxlength: 80, required: true }">

                                <span class="help-block" v-if="!$editValidation.command.valid && $editValidation.command.modified">Command requires a minimum of 1 characters and has a maximum 80 characters.</span>
                            </div><!-- .form-group -->

                            <div class="form-group">
                                <label for="level-input">Level:</label>
                                <select class="form-control" id="level-input" name="level" v-model="newCommand.level">
                                    <option value="owner">Owner</option>
                                    <option value="admin">Admin</option>
                                    <option value="mod">Mod</option>
                                    <option value="everyone" selected="selected">Everyone</option>
                                </select>
                            </div><!-- .form-group -->

                            <div class="form-group" v-bind:class="{ 'has-error': !$editValidation.response.valid && $editValidation.response.modified }">
                                <label for="response-input">Response:</label>
                                <textarea class="form-control" id="response-input" name="command" v-model="newCommand.response" placeholder="This is a response output by the bot when the command is executed." v-validate:response="{ minlength: 2, maxlength: 400, required: true }"></textarea>

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
                    response: ''
                },
                modal: false,
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
                }, 500);
            });
        },

        events: {
            openEditCommandModal(command, title) {
                this.title = 'Edit Command';
                this.open(command);
            },

            openNewCommandModal() {
                this.title = 'New Command';
                this.open();
            },

            closeEditCommandModal() {
                this.close();
            }
        },

        methods: {
            save() {
                let request,
                    data = {
                        command: this.newCommand.command,
                        level: this.newCommand.level,
                        response: this.newCommand.response
                    };

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
                    this.$parent.updateOrAddToTable(response.data);
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
