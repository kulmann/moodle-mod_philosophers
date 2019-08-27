<template lang="pug">
    #philosophers-question_actions
        .uk-heading-divider.uk-margin-small-bottom
        .uk-align-right
            button.btn.btn-default(@click="showLevelOverview")
                v-icon(name="arrow-circle-right").uk-margin-small-right
                span {{ buttonContinueText }}
</template>

<script>
    import {mapMutations, mapState} from 'vuex';
    import {MODE_LEVELS} from "../../../constants";

    export default {
        data() {
            return {
                lastFinishedQuestionId: 0,
                countdownValue: 0,
                timer: null,
            }
        },
        computed: {
            ...mapState([
                'strings',
                'question',
                'game',
            ]),
            buttonContinueText() {
                if (this.game.review_duration > 0) {
                    return this.strings.game_btn_continue + ' (' + this.countdownValue + ')';
                } else {
                    return this.strings.game_btn_continue;
                }
            },
        },
        methods: {
            ...mapMutations([
                'setGameMode'
            ]),
            showLevelOverview() {
                if (this.timer) {
                    clearInterval(this.timer);
                }
                this.setGameMode(MODE_LEVELS)
            }
        },
        mounted() {
            if (this.game.review_duration <= 0) {
                // only becomes relevant if auto-continue is enabled
                return;
            }
            if (this.question && this.question.finished && this.question.id !== this.lastFinishedQuestionId) {
                this.lastFinishedQuestionId = this.question.id;
                this.countdownValue = this.game.review_duration;
                this.timer = setInterval(() => {
                    this.countdownValue--;
                    if (this.countdownValue <= 0) {
                        this.showLevelOverview();
                    }
                }, 1000);
            }
        },
    }
</script>

<style scoped>
</style>
