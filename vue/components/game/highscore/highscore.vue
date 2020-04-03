<template lang="pug">
    div
        table.uk-table.uk-table-small.uk-table-striped(v-if="filteredScores.length > 0")
            thead
                tr
                    th.uk-table-shrink {{ strings.game_stats_rank }}
                    th.uk-table-auto {{ strings.game_stats_user }}
                    th.uk-table-shrink {{ strings.game_stats_score }}
                    th.uk-table-shrink.uk-text-nowrap {{ strings.game_stats_maxscore }}
                    th.uk-table-shrink {{ strings.game_stats_sessions }}
            tbody
                tr(v-for="score in mainScores", :key="score.mdl_user", :class="getScoreClasses(score)")
                    td {{ score.rank }}
                    td
                        a(:href="getProfileUrl(score.mdl_user)", target="_blank") {{ score.mdl_user_name }}
                    td.uk-text-right(v-html="formatScore(score.score)")
                    td.uk-text-right(v-html="formatScore(score.maxscore)")
                    td.uk-text-right {{ score.sessions }}
                tr(v-if="loserScores.length > 0")
                    td.uk-text-center(colspan="5")
                        v-icon(name="ellipsis-v")
                tr(v-for="score in loserScores", :key="score.mdl_user", :class="getScoreClasses(score)")
                    td {{ score.rank }}
                    td
                        a(:href="getProfileUrl(score.mdl_user)", target="_blank") {{ score.mdl_user_name }}
                    td.uk-text-right(v-html="formatScore(score.score)")
                    td.uk-text-right(v-html="formatScore(score.maxscore)")
                    td.uk-text-right {{ score.sessions }}
        infoAlert(v-else, :message="strings.game_stats_empty")
</template>

<script>
    import {mapState} from 'vuex';
    import infoAlert from "../../helper/info-alert";

    export default {
        props: {
            maxRows: Number,
            scores: Array,
        },
        computed: {
            ...mapState([
                'strings',
                'game',
            ]),
            filteredScores() {
                let ownUserId = this.game.mdl_user;
                let ownScore = _.find(this.scores, score => {
                    return parseInt(score.mdl_user) === ownUserId;
                });
                return _.filter(this.scores, score => {
                    // show first x rows in any case
                    if (score.rank <= this.maxRows) {
                        return true;
                    }
                    // if own score not within first x: show one before and one after own score as well
                    if (ownScore) {
                        if (_.inRange(score.rank, ownScore.rank - 1, ownScore.rank + 2)) {
                            return true;
                        }
                    }
                    return false;
                });
            },
            firstLoser() {
                return _.find(this.filteredScores, (score, index) => {
                    let prev = (index === 0) ? null : this.filteredScores[index - 1];
                    return prev !== null && prev.rank < score.rank - 1;
                });
            },
            mainScores() {
                if (this.firstLoser) {
                    return _.filter(this.filteredScores, score => {
                        return score.rank < this.firstLoser.rank;
                    });
                } else {
                    return this.filteredScores;
                }
            },
            loserScores() {
                if (this.firstLoser) {
                    return _.filter(this.filteredScores, score => {
                        return score.rank >= this.firstLoser.rank;
                    });
                } else {
                    return [];
                }
            },
        },
        methods: {
            formatScore(score) {
                let str = score.toFixed(0);
                if (this.game.currency_for_levels) {
                    str += '&nbsp;' + this.game.currency_for_levels;
                }
                return str;
            },
            getScoreClasses(score) {
                let result = [];
                if (score.mdl_user === this.game.mdl_user) {
                    result.push('uk-text-bold');
                }
                return result.join(' ');
            },
            getProfileUrl(userId) {
                const baseUrl = window.location.origin;
                return `${baseUrl}/user/profile.php?id=${userId}`;
            }
        },
        components: {infoAlert}
    }
</script>
