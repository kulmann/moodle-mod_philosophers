<template lang="pug">
    .uk-grid.uk-grid-collapse.footer-bar(uk-grid).uk-flex-middle
        .uk-width-expand
            span(v-if="gameSession.score === 1") {{ strings.game_progress_point }}
            span(v-else) {{ strings.game_progress_points | stringParams(gameSession.score) }}
        .uk-width-auto
            span(v-if="completedLevels === totalLevels") {{ strings.game_progress_answered_levels_all | stringParams(totalLevels) }}
            span(v-else-if="completedLevels === 1") {{ strings.game_progress_answered_level | stringParams(totalLevels) }}
            span(v-else) {{ strings.game_progress_answered_levels | stringParams({completed: completedLevels, total: totalLevels}) }}
</template>

<script>
    import mixins from '../../mixins';
    import {mapState} from 'vuex';
    import _ from 'lodash';

    export default {
        mixins: [mixins],
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
            }
        }
    }
</script>

<style scoped>
    .footer-bar {
        padding-left: 10px;
        padding-right: 10px;
        width: 100%;
        background-color: #f8f8f8;
        border-bottom-left-radius: 5px;
        border-bottom-right-radius: 5px;
        border: 1px solid #ccc;
        height: 50px;
    }
</style>
