<template lang="pug">
    .uk-grid.uk-grid-collapse.footer-bar(uk-grid).uk-flex-middle
        .uk-width-expand.uk-margin-small-left
            span(v-if="completedLevels === totalLevels") {{ strings.game_progress_answered_levels_all | stringParams(totalLevels) }}
            span(v-else-if="completedLevels === 1") {{ strings.game_progress_answered_level | stringParams(totalLevels) }}
            span(v-else) {{ strings.game_progress_answered_levels | stringParams({completed: completedLevels, total: totalLevels}) }}
        .uk-width-auto.uk-margin-small-right(:class="{'score-update': activeAnimation}")
            span {{ strings.game_progress_current_score }}&nbsp;
            span(v-if="visibleScore === 1") {{ strings.game_progress_point }}
            span(v-else) {{ strings.game_progress_points | stringParams(visibleScore) }}
            template(v-if="activeAnimation")
                span.score-up(v-if="scoreDelta > 0") {{ ' (+' + scoreDelta + ')' }}
                span.score-down(v-else) {{ ' (' + scoreDelta + ')' }}
</template>

<script>
    import mixins from '../../mixins';
    import {mapState} from 'vuex';
    import _ from 'lodash';

    export default {
        mixins: [mixins],
        data() {
            return {
                visibleScore: 0,
                scoreDelta: 0,
                timer: null,
                activeAnimation: false,
            }
        },
        computed: {
            ...mapState([
                'strings',
                'gameSession',
                'levels',
            ]),
            completedLevels() {
                return _.filter(this.levels, function (level) {
                    return level.finished;
                }).length;
            },
            totalLevels() {
                return this.levels.length;
            },
            score() {
                return this.gameSession.score;
            }
        },
        mounted() {
            if (this.gameSession) {
                this.visibleScore = this.gameSession.score;
            }
        },
        watch: {
            score(score) {
                // kill the old timer if it lures around
                if (this.timer) {
                    clearInterval(this.timer);
                }
                // make sure there's an update incoming
                if (score === this.visibleScore) {
                    return;
                }
                // start the timer
                let self = this;
                this.scoreDelta = (score - this.visibleScore);
                self.activeAnimation = true;
                this.timer = setInterval(() => {
                    if (self.visibleScore !== score) {
                        let delta = (score - self.visibleScore) / 10;
                        delta = (delta >= 0) ? Math.ceil(delta) : Math.floor(delta);
                        self.visibleScore += delta;
                    } else {
                        self.activeAnimation = false;
                    }
                }, 50);
            }
        }
    }
</script>

<style scoped>
    .footer-bar {
        width: 100%;
        background-color: #f8f8f8;
        border-bottom-left-radius: 5px;
        border-bottom-right-radius: 5px;
        border: 1px solid #ccc;
        height: 50px;
        font-weight: bold;
    }
    .score-update {
        font-size: 1.1em;
    }
    .score-up {
        color: green;
    }
    .score-down {
        color: red;
    }
</style>
