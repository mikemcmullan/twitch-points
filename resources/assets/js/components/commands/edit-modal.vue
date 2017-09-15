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
                        <div class="form-group" v-bind:class="{ 'has-error': errors.has('command') }">
                            <label for="command-input">Command:</label>
                            <input type="text" class="form-control" id="command-input" name="command" placeholder="!command" v-model="newCommand.command" v-bind:disabled="disabled.command">

                            <span class="help-block" v-if="errors.has('command')" v-text="errors.get('command')"></span>
                        </div><!-- .form-group -->

                        <div class="form-group">
                            <label for="level-input">Level:</label>
                            <select class="form-control" id="level-input" name="level" v-model="newCommand.level" v-bind:disabled="disabled.level">
                                <option value="owner">Owner</option>
                                <option value="admin">Admin</option>
                                <option value="mod">Mod</option>
                                <option value="sub">Subscriber</option>
                                <option value="everyone" selected="selected">Everyone</option>
                            </select>
                        </div><!-- .form-group -->

                        <div class="form-group" v-bind:class="{ 'has-error': errors.has('cool_down') }">
                            <label for="command-down">Cool Down:</label>

                            <div id="commandCooldownSlider"></div>
                            <span class="help-block">{{ newCommand.cool_down }} seconds</span>
                            <span class="help-block" v-if="errors.has('cool_down')" v-text="errors.get('cool_down')"></span>
                            <span class="help-block">The amount of time in seconds before the command can be used again.</span>
                        </div>
                        <!--
                        <div class="form-group">
                            <label for="command-count">Count</label>
                            <input type="number" class="form-control" id="command-count" name="count" placeholder="3" v-model="newCommand.count">
                        </div>
                        -->
                        <div class="form-group" v-bind:class="{ 'has-error': errors.has('response') }">
                            <label for="response-input">Response:</label>
                            <textarea class="form-control" id="response-input" name="command" rows="4" v-model="newCommand.response" v-bind:disabled="disabled.response" placeholder="This is a response output by the bot when the command is executed."></textarea>

                            <span class="help-block" v-if="errors.has('response')" v-text="errors.get('response')"></span>
                        </div><!-- .form-group -->
                    </div><!-- .modal-body -->

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" @click="close">Close</button>
                        <button type="submit" class="btn btn-primary" v-bind:disabled="saving">{{ saving ? 'Saving...' : 'Save' }}</button>
                    </div><!-- .modal-footer -->
                </form><!-- form -->
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
                title: 'Edit Command',
                originalCommand: false,
                newCommand: {
                    command: '',
                    level: 'everyone',
                    response: '',
                    cool_down: 3,
                    count: 0
                },
                modal: false,
                saving: false,
                disabled: {
                    command: false,
                    level: false,
                    response: false,
                    cool_down: false
                },

                errors: new Errors()
            }
        },

        ready() {
            this.$set('modal', $(this.$els.modal));

            this.modal.on('hide.bs.modal', () => {
                setTimeout(() => {
                    this.errors.clear();
                    this.newCommand.command = '';
                    this.newCommand.level = 'everyone';
                    this.newCommand.response = '';
                    this.newCommand.cool_down = 3;
                    this.newCommand.count = 0;
                    this.originalCommand = false;
                    this.saving = false;
                    this.disabled.command = false;
                    this.disabled.level = false;
                    this.disabled.response = false;

                    this.coolDownSlider.noUiSlider.set(this.newCommand.cool_down);
                }, 500);
            });

            this.setupCoolDownSlider();
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
            setupCoolDownSlider() {
                const coolDownSlider = document.getElementById('commandCooldownSlider');

                noUiSlider.create(coolDownSlider, {
                    start: this.newCommand.cool_down,
                    step: 1,
                    range: {
                        min: 3,
                        max: 300
                    }
                });

                coolDownSlider.noUiSlider.on('update', (values, handle) => {
                    this.newCommand.cool_down = ~~values[0];
                });

                this.coolDownSlider = coolDownSlider;
            },

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

                if (! this.disabled.count) {
                    data.count = this.newCommand.count;
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
                    this.errors.record(response.data.message.validation_errors);
                    this.saving = false;
                });
            },

            open(command) {
                if (command) {
                    this.originalCommand = command
                    this.newCommand.command = command.command;
                    this.newCommand.cool_down = command.cool_down;
                    this.newCommand.level = command.level;
                    this.newCommand.response = command.response;
                    this.newCommand.count = command.count;

                    this.coolDownSlider.noUiSlider.set(command.cool_down);
                }

                this.modal.modal('toggle');
            },

            close() {
                this.modal.modal('toggle');
            }
        }
    };
</script>
