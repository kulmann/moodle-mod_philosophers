<template lang="pug">
    .level(@click="selectLevel(level)", :style="getLevelStyles(level)", :class="{'_pointer': !isDone(level)}")
        .uk-flex.uk-flex-middle.level(:style="overlayStyles").uk-box-shadow-hover-large
            .uk-flex.uk-width-expand
                .uk-width-auto.level-content
                    v-icon.uk-margin-small-left(v-if="getLevelIcon(level)", :name="getLevelIcon(level)", :scale="2.5")
                .uk-width-expand.level-content.uk-text-center
                    template(v-if="level.seen")
                        b {{ level.name }}
                        br
                        span.done(v-if="level.score === 1") {{ strings.game_progress_point | stringParams(1) }}
                        span.done(v-else) {{ strings.game_progress_points | stringParams(level.score) }}
                    b.open(v-else) {{ level.name }}
</template>

<script>
    import mixins from '../../mixins';

    export default {
        mixins: [mixins],
        props: {
            strings: Object,
            level: Object,
            game: Object,
        },
        computed: {
            overlayStyles() {
                let styles = ['color: #fff;'];
                let alpha = ((100 - this.game.level_tile_alpha) / 100.0);
                if (this.level.finished && this.level.imageurl) {
                    if (this.level.correct) {
                        styles.push('background-color: rgba(1,50,32,0.9);');
                    } else {
                        styles.push('background-color: rgba(139,0,0,0.9);');
                    }
                } else {
                    styles.push(('background-color: rgba(0,0,0,' + alpha + ');'));
                }
                return styles.join(' ');
            },
        },
        methods: {
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
                let styles = [
                    'min-height: ' + level.tile_height_px + 'px;',
                    'max-height: ' + level.tile_height_px + 'px;',
                ];
                // bg color
                if (level.bgcolor) {
                    styles.push('background-color: ' + level.bgcolor + ';');
                }
                // bg image
                if (level.imageurl) {
                    styles.push('background-image: url(' + level.imageurl + ');');
                    styles.push('background-size: cover;');
                    styles.push('background-position: center;');
                }
                return styles.join(' ');
            },
            isDone(level) {
                return level.finished;
            },
            selectLevel(level) {
                if (!this.isDone(level)) {
                    this.$emit('onSelectLevel', level.id);
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
