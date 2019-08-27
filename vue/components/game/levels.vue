<template lang="pug">
    vk-grid(matched)
        div(v-for="level in levels", :key="level.id", class="uk-width-1-1@s uk-width-1-2@m")
            .uk-text-center.level(@click="selectLevel(level)", :style="getLevelStyles(level)", :class="{'_pointer': !isDone(level)}")
                vk-grid.uk-grid-small.uk-flex-middle
                    .uk-width-auto.level-content
                        v-icon.uk-margin-small-left(v-if="getLevelIcon(level)", :name="getLevelIcon(level)", scale="1.5")
                    .uk-width-expand.level-content
                        template(v-if="level.seen")
                            b {{ level.name }}
                            br
                            span.done(v-if="level.score === 1") {{ strings.game_progress_point | stringParams(1) }}
                            span.done(v-else) {{ strings.game_progress_points | stringParams(level.score) }}
                        b.open(v-else) {{ level.name }}

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
            getLevelIcon(level) {
                if (level.finished) {
                    if (level.correct) {
                        return "regular/check-circle";
                    } else {
                        return "regular/times-circle"
                    }
                }
                return null;
            },
            getLevelStyles(level) {
                let styles = [];
                // fg color
                if (level.fgcolor) {
                    styles.push('color: ' + level.fgcolor + ';');
                } else {
                    styles.push('color: #666;');
                }
                // bg color
                if (level.bgcolor) {
                    styles.push('background-color: ' + level.bgcolor + ';');
                }
                // bg image
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
            selectLevel(level) {
                if (!this.isDone(level)) {
                    this.showQuestionForLevel(level.id);
                }
            },
        }
    }
</script>

<style lang="scss" scoped>
    .level {
        height: 100%;
        border-radius: 10px;
    }

    .level-content {
        margin-top: 10px;
        margin-bottom: 10px;
        text-align: center;
    }

    .open {
        font-size: 1.1em;
    }
</style>
