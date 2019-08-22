<template lang="pug">
    #philosophers-question
        template(v-if="question")
            loadingAlert(v-if="mdl_question === null", :message="strings.game_loading_question")
            template(v-else)
                finished(v-if="isGameFinished")
                div(:is="componentByType", :levels="levels", :gameSession="gameSession", :question="question", :mdl_question="mdl_question", :mdl_answers="mdl_answers")
                actions(v-if="areActionsAllowed").uk-margin-small-top
        .uk-alert.uk-alert-primary(uk-alert, v-else)
            p Show info about level selection if not dead. If dead, show stats?!
</template>

<script>
    import {mapState} from 'vuex';
    import finished from './finished';
    import mixins from '../../../mixins';
    import questionActions from './question-actions';
    import questionError from './question-error';
    import questionSingleChoice from './question-singlechoice';
    import {GAME_FINISHED, GAME_PROGRESS} from "../../../constants";
    import loadingAlert from '../../helper/loading-alert';

    export default {
        mixins: [mixins],
        computed: {
            ...mapState([
                'strings',
                'gameSession',
                'gameMode',
                'levels',
                'question',
                'mdl_question',
                'mdl_answers'
            ]),
            componentByType() {
                switch (this.question.mdl_question_type) {
                    case 'qtype_multichoice_single_question':
                        return 'singlechoice';
                    default:
                        return 'error';
                }
            },
            highestSeenLevel() {
                return this.findHighestSeenLevel(this.levels);
            },
            isCurrentQuestion() {
                return this.highestSeenLevel !== null && this.highestSeenLevel.position === this.question.index;
            },
            isGameOver() {
                return this.gameSession.state !== GAME_PROGRESS;
            },
            isGameFinished() {
                return this.gameSession.state === GAME_FINISHED;
            },
            areActionsAllowed() {
                return !(!this.isCurrentQuestion || this.isGameOver);

            }
        },
        components: {
            loadingAlert,
            finished,
            'actions': questionActions,
            'error': questionError,
            'singlechoice': questionSingleChoice,
        }
    }
</script>
