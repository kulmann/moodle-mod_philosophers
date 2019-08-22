<template lang="pug">
    vk-grid.uk-margin-top(matched)
        div(v-for="level in levels", :key="level.id", class="uk-width-1-1@s uk-width-1-2@m", :class="getLevelClasses(level)")
            .uk-alert.uk-alert-default(uk-alert, @click="selectLevel(level)")
</template>

<script>
    import {mapState, mapActions} from 'vuex';
    import _ from 'lodash';
    import mixins from '../../mixins';
    import {GAME_PROGRESS} from "../../constants";

    export default {
        mixins: [mixins],
        computed: {
            ...mapState([
                'strings',
                'levels',
                'gameSession',
                'question',
            ]),
            sortedLevels() {
                return _.reverse(_.sortBy(this.levels, ['position']));
            }
        },
        methods: {
            ...mapActions([
                'showQuestionForLevel',
            ]),
            isGameOver() {
                return this.gameSession.state !== GAME_PROGRESS;
            },
            isSeen(level) {
                return level.seen;
            },
            isDone(level) {
                return level.finished;
            },
            isWon(level) {
                return this.isDone(level) && level.correct;
            },
            isLost(level) {
                return this.isDone(level) && !level.correct;
            },
            selectLevel(level) {
                if (this.isDone(level)) {
                    this.showQuestionForLevel(level.position);
                }
            },
        }
    }
</script>

<style lang="scss" scoped>
    .won-level {
        & > td {
            background-color: #edfbf6;
        }
    }
    .lost-level {
        & > td {
            background-color: #fef4f6;
        }
    }
</style>
