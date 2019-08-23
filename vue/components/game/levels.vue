<template lang="pug">
    vk-grid.uk-margin-top(matched)
        div(v-for="level in levels", :key="level.id", class="uk-width-1-1@s uk-width-1-2@m", :class="getLevelClasses(level)")
            .uk-alert.uk-alert-default.uk-text-center.level(uk-alert, @click="selectLevel(level)", :style="getLevelStyles(level)", :class="{'_pointer': !isDone(level)}")
                b {{ level.name }}
</template>

<script>
    import {mapActions, mapState} from 'vuex';
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
        },
        methods: {
            ...mapActions([
                'showQuestionForLevel',
            ]),
            getLevelClasses(level) {
                let classes = [];
                // bg color for lost/won levels
                if (this.isLost(level)) {
                    classes.push('level-lost');
                } else if (this.isWon(level)) {
                    classes.push('level-won');
                }
                return classes.join(' ');
            },
            getLevelStyles(level) {
                let styles = [];
                // bg color
                if (level.color) {
                    styles.push('background-color: ' + level.color + ';');
                }
                if (level.image) {
                    styles.push('background-image: url(' + level.image + ');');
                    styles.push('background-size: cover;');
                }
                return styles.join(' ');
            },
            isGameOver() {
                return this.gameSession.state !== GAME_PROGRESS;
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
                if (!this.isDone(level)) {
                    this.showQuestionForLevel(level.id);
                }
            },
        }
    }
</script>

<style lang="scss" scoped>
    .level-wrapper {
        padding: 5px;
    }

    .level-won {
        background-color: #edfbf6;
    }

    .level-lost {
        background-color: #fef4f6;
    }

    .level {
        border-bottom-left-radius: 15px;
        border-top-right-radius: 15px;
    }
</style>
