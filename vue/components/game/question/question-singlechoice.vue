<template lang="pug">
    #philosophers-question_singlechoice
        .uk-card.uk-card-default
            .uk-card-header(style="padding-top: 5px; padding-bottom: 5px;")
                vk-grid(matched)
                    .uk-width-expand
                        i.uk-h5 {{ levelName }}
                    .uk-width-auto
                        questionTimer(:question="question")
            .uk-card-body
                p._question(v-html="mdl_question.questiontext")
        vk-grid.uk-margin-top(matched)
            div(v-for="answer in mdl_answers", :key="answer.id", class="uk-width-1-1@s uk-width-1-2@m")
                .uk-alert.uk-alert-default._answer(uk-alert, @click="selectAnswer(answer)", :class="getAnswerClasses(answer)")
                    vk-grid.uk-grid-small
                        span.uk-width-auto.uk-text-bold {{ answer.label }}
                        span.uk-width-expand.uk-text-center(v-html="answer.answer")
</template>

<script>
    import {mapActions, mapState} from 'vuex';
    import mixins from '../../../mixins';
    import VkGrid from "vuikit/src/library/grid/components/grid";
    import _ from 'lodash';
    import {GAME_PROGRESS} from "../../../constants";
    import questionTimer from "./question-timer";

    export default {
        mixins: [mixins],
        props: {
            levels: Array,
            gameSession: Object,
            question: Object,
            mdl_question: Object,
            mdl_answers: Array,
        },
        data() {
            return {
                mostRecentQuestionId: null,
                clickedAnswerId: null,
            }
        },
        computed: {
            ...mapState([
                'strings'
            ]),
            correctAnswerId() {
                let correct = _.find(this.mdl_answers, function (mdl_answer) {
                    return mdl_answer.fraction === 1;
                });
                return correct ? correct.id : null;
            },
            isFinished() {
                return this.clickedAnswerId !== null || this.question.finished;
            },
            isAnyAnswerGiven() {
                return this.clickedAnswerId !== null || this.question.finished;
            },
            level() {
                if (this.question) {
                    let levelId = this.question.level;
                    return _.find(this.levels, function (level) {
                        return level.id === levelId;
                    });
                } else {
                    return null;
                }
            },
            levelName() {
                if (this.level) {
                    return this.level.name;
                } else {
                    return '';
                }
            },
            isGameOver() {
                return this.gameSession.state !== GAME_PROGRESS;
            }
        },
        methods: {
            ...mapActions([
                'submitAnswer'
            ]),
            selectAnswer(answer) {
                if (this.isGameOver) {
                    // don't allow another submission if game is over
                    return;
                }
                if (this.isFinished) {
                    // don't allow another submission if already answered
                    return;
                }
                this.mostRecentQuestionId = this.question.id;
                this.clickedAnswerId = answer.id;
                this.submitAnswer({
                    'gamesessionid': this.question.gamesession,
                    'questionid': this.question.id,
                    'mdlanswerid': this.clickedAnswerId,
                });
            },
            getAnswerClasses(answer) {
                let result = [];
                if (this.isFinished) {
                    if (this.isCorrectAnswer(answer)) {
                        result.push('uk-alert-success');
                    }
                    if (this.isClickedAnswer(answer)) {
                        result.push('_thick-border');
                        if (this.isWrongAnswer(answer)) {
                            result.push('uk-alert-danger');
                        }
                    }
                } else {
                    result.push('_pointer');
                }
                return result.join(' ');
            },
            isClickedAnswer(answer) {
                return this.isFinished && this.clickedAnswerId === answer.id;
            },
            isWrongAnswer(answer) {
                return this.correctAnswerId !== answer.id;
            },
            isCorrectAnswer(answer) {
                return this.correctAnswerId === answer.id;
            },
            initQuestion() {
                this.mostRecentQuestionId = this.question.id;
                this.clickedAnswerId = (this.question.mdl_answer_given > 0) ? this.question.mdl_answer_given : null;
            },
        },
        mounted() {
            if (this.question) {
                this.initQuestion();
            }
        },
        watch: {
            question(question) {
                if (this.mostRecentQuestionId !== question.id) {
                    this.initQuestion();
                }
            }
        },
        components: {
            VkGrid,
            questionTimer
        }
    }
</script>

<style lang="scss" scoped="scoped">
    ._thick-border {
        border-width: 1px;
        border-style: solid;
    }

    ._answer {
        border-radius: 5px;
        font-size: 1.2em;
    }

    ._question {
        font-size: 1.4em;
    }
</style>
