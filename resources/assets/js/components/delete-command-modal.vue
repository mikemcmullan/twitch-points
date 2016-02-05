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
                        <div v-if="alert.visible" v-bind:class="alert.class" role="alert">{{ alert.text }}</div>
                        Are you sure you want to delete the command <code>{{ command.command }}</code> ?
                    </div><!-- .modal-body -->

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" @click="close">Cancel</button>
                        <button type="submit" class="btn btn-primary" v-if="!deleting">Yes, Delete</button>
                        <button type="submit" class="btn btn-primary" disabled="disabled" v-if="deleting">Deleting...</button>
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
                default: 'Delete Command'
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
                modal: false,
                command: false,
                alert: {
                    visible: false,
                    class: {
                        alert: true,
                        'alert-danger': true
                    },
                    text: ''
                },
                deleting: false
            }
        },

        ready() {
            this.$set('modal', $(this.$els.modal));

            this.modal.on('hide.bs.modal', () => {
                setTimeout(() => {
                    this.command = false;
                    this.deleting = false;
                }, 500);
            });
        },

        events: {
            openDeleteCommandModal(command) {
                this.open(command);
            }
        },

        methods: {
            delete() {
                var xhr = $.ajax({
                    url: `http://api.twitch.dev/mcsmike/commands/${this.command.id}`,
                    contentType: 'application/json',
                    method: 'delete',
                    dataType: 'json',
                    beforeSend: (xhr) => {
                        this.deleting = true;
                    },
                    headers: {
                        'Authorization': `Bearer ${this.apiToken}`
                    }
                });

                xhr.error((jqXHR, textStatus, errorThrown) => {
                    const responseError = JSON.parse(jqXHR.responseText);

                    this.deleting = true;

                    if (responseError.status === 403) {
                        this.alert.visible = true;
                        this.alert.text = 'There was an error authenticating with the api, please refresh the page.';
                        return;
                    }
                });

                xhr.done((data) => {
                    this.$parent.deleteFromTable(this.command);
                    this.close();
                });
            },

            open(command) {
                this.command = command;
                this.modal.modal('toggle');
            },

            close() {
                this.modal.modal('toggle');
            }
        },
    }
</script>
