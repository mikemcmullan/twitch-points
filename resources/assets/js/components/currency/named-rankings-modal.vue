<template>
    <div class="modal fade" v-el:modal>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" @click="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ title }}</h4>
                </div><!-- .modal-header -->

                <validator name="validation">
                    <form @submit.prevent @submit="save">
                        <div class="modal-body">
                            <p>If you would like to assign names to different currency groups fill in the form below. Once a viewer has reached the starting amount
                                of points they will be given that rank.</p>

                            <div class="alert alert-danger" v-if="errorText.length !== 0">
                                {{ errorText }}
                            </div>

                            <table class="table">
                                <thead>
                                    <tr>
                                        <th style="width: 45%">Name</th>
                                        <th style="width: 45%">Starting Amount</th>
                                        <th>--</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="rank in rankings">
                                        <td><input class="form-control" type="text" v-model="rank.name" required maxlength="30"></td>
                                        <td><input class="form-control" type="number" min="0" max="999999" required v-model="rank.min"></td>
                                        <td><a style="margin-top: 5px" @click="rankings.splice($index, 1)" class="btn btn-danger btn-xs" title="Delete Ranking"><i class="fa fa-trash-o"></i></a></td>
                                    </tr>

                                    <tr v-if="rankings.length === 0">
                                        <td col-span="3">No rankings have been added.</td>
                                    </tr>
                                </tbody>
                            </table>

                            <button type="button" class="btn btn-info" @click="addBlankRanking()">Add Ranking</button>
                        </div><!-- .modal-body -->

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" @click="close">Close</button>
                            <button type="submit" class="btn btn-primary" v-bind:disabled="saving || !$validation.valid">{{ saving ? 'Saving...' : 'Save' }}</button>
                        </div><!-- .modal-footer -->
                    </form><!-- form -->
                </validator>
            </div><!-- .modal-content -->
        </div><!-- .modal-dialog -->
    </div><!-- .modal -->
</template>

<script>
    export default {
        props: ['ranks'],

        data: () => {
            return {
                title: 'Named Rankings',
                modal: false,
                saving: false,
                rankings: [],
                errorText: ''
            }
        },

        ready() {
            this.$set('modal', $(this.$els.modal));

            this.rankings = this.ranks;

            this.modal.on('hide.bs.modal', () => {
                setTimeout(() => {
                    this.saving = false;
                    this.errorText = '';
                }, 500);
            });
        },

        events: {
            openRankingsModal() {
                this.open();
            }
        },

        methods: {
            addBlankRanking() {
                let min = 0;

                if (this.rankings[this.rankings.length-1]) {
                    min = ~~this.rankings[this.rankings.length-1].min + 100;
                }

                this.rankings.push({ name: '', min, max: 0 })
            },

            save() {
                this.rankings = this.rankings.sort((a, b) => {
                    return ~~a.min - ~~b.min;
                });

                this.rankings = this.rankings.map((ranking, index, rankings) => {
                    const nextRank = rankings[index+1];
                    let max = 999999;

                    if (nextRank) {
                        max = ~~nextRank.min -1;
                    }

                    ranking.max = max;

                    return ranking;
                });

                let request = this.$http.put('settings/named-rankings', {
                    'named-rankings': this.rankings
                }, {
                    beforeSend: (request) => {
                        this.saving = true;
                    }
                });

                request.then(response => {
                    this.close();
                }, error => {
                    this.errorText = error.data.message;
                    this.saving = false;
                });
            },

            open() {
                this.modal.modal('toggle');
            },

            close() {
                this.modal.modal('toggle');
            }
        }
    }
</script>
