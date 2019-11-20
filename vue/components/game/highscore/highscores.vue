<template lang="pug">
    #philosophers-highscore
        .uk-card.uk-card-default
            .uk-card-header
                h3 {{ strings.game_btn_highscore }}
            .uk-card-body
                loadingAlert(v-if="loading", :message="strings.game_loading_highscore")
                failureAlert(v-else-if="failed", :message="strings.game_loading_highscore_failed")
                template(v-else)
                    ul.uk-tab
                        li(:class="{'uk-active': activeTab === 'day'}")
                            a(@click="showTab('day')") {{ strings.game_stats_day }}
                        li(:class="{'uk-active': activeTab === 'week'}")
                            a(@click="showTab('week')") {{ strings.game_stats_week }}
                        li(:class="{'uk-active': activeTab === 'month'}")
                            a(@click="showTab('month')") {{ strings.game_stats_month }}
                        li(:class="{'uk-active': activeTab === 'all'}")
                            a(@click="showTab('all')") {{ strings.game_stats_all }}

                    highscore(v-if="activeTab === 'day'", :maxRows="maxRows", :scores="scores.day")
                    highscore(v-else-if="activeTab === 'week'", :maxRows="maxRows", :scores="scores.week")
                    highscore(v-else-if="activeTab === 'month'", :maxRows="maxRows", :scores="scores.month")
                    highscore(v-else-if="activeTab === 'all'", :maxRows="maxRows", :scores="scores.all")
</template>

<script>
    import {mapActions, mapState} from 'vuex';
    import mixins from '../../../mixins';
    import loadingAlert from '../../helper/loading-alert';
    import failureAlert from '../../helper/failure-alert';
    import highscore from "./highscore";

    export default {
        mixins: [mixins],
        data() {
            return {
                loading: true,
                failed: false,
                activeTab: 'day',
            }
        },
        computed: {
            ...mapState([
                'strings',
                'game',
                'scores',
            ]),
            maxRows() {
                return this.game.highscore_count;
            },
        },
        methods: {
            ...mapActions([
                'fetchScores'
            ]),
            showTab(tab) {
                this.activeTab = tab;
            },
        },
        mounted() {
            this.fetchScores({span: 'day'}).then(() => {
                this.loading = false;
            }).catch(() => {
                this.loading = false;
                this.failed = true;
            });
            this.fetchScores({span: 'week'});
            this.fetchScores({span: 'month'});
            this.fetchScores({span: 'all'});
        },
        components: {
            loadingAlert,
            failureAlert,
            highscore,
        }
    }
</script>
