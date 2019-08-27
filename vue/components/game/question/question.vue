<template lang="pug">
    #philosophers-question
        template(v-if="question")
            loadingAlert(v-if="mdl_question === null", :message="strings.game_loading_question")
            template(v-else)
                div(:is="componentByType", :levels="levels", :game="game", :gameSession="gameSession", :question="question", :mdl_question="mdl_question", :mdl_answers="mdl_answers")
                actions(v-if="areActionsAllowed").uk-margin-small-top
</template>

<script>
    import {mapState} from 'vuex';
    import finished from './finished';
    import mixins from '../../../mixins';
    import questionActions from './question-actions';
    import questionError from './question-error';
    import questionSingleChoice from './question-singlechoice';
    import loadingAlert from '../../helper/loading-alert';

    export default {
        mixins: [mixins],
        computed: {
            ...mapState([
                'strings',
                'game',
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
            areActionsAllowed() {
                return this.question.finished;
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
