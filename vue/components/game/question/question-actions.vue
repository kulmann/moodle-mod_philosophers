<template lang="pug">
    #philosophers-question_actions
        .uk-heading-divider.uk-margin-small-bottom
        .uk-align-right
            button.btn.btn-default(@click="showNextLevel", :disabled="isNextLevelDisabled")
                v-icon(name="arrow-circle-right").uk-margin-small-right
                span {{ strings.game_btn_continue }}
</template>

<script>
    import {mapActions, mapState} from 'vuex';

    export default {
        computed: {
            ...mapState([
                'strings',
                'gameSession',
                'question',
                'gameMode',
                'levels'
            ]),
            isNextLevelDisabled() {
                return this.question.mdl_answer_given === 0;
            },
        },
        methods: {
            ...mapActions([
                'closeGameSession',
                'showQuestionForLevel',
            ]),
            quitGame() {
                this.closeGameSession();
            },
            showNextLevel() {
                let nextIndex = this.question.index + 1;
                if(this.levels.length > nextIndex) {
                    this.showQuestionForLevel(nextIndex);
                }
            }
        },
    }
</script>

<style scoped>
</style>
