<template lang="pug">
    #philosophers-game_screen
        .uk-clearfix
        loadingAlert(v-if="!initialized", message="Loading Game").uk-text-center
        template(v-else)
            topbar
            .uk-margin-small-top.uk-margin-small-bottom
                intro(v-if="introVisible")
                levels(v-if="levelsVisible")
                question(v-if="questionVisible")
                stats(v-if="statsVisible")
                help(v-if="helpVisible")
            session(v-if="sessionVisible")
</template>

<script>
    import mixins from '../../mixins';
    import {mapState} from 'vuex';
    import help from './help';
    import intro from './intro';
    import levels from './levels';
    import loadingAlert from '../helper/loading-alert';
    import session from './session';
    import question from './question/question';
    import stats from './stats';
    import topbar from '../topbar';
    import VkGrid from "vuikit/src/library/grid/components/grid";
    import {MODE_HELP, MODE_INTRO, MODE_LEVELS, MODE_QUESTION, MODE_STATS} from "../../constants";

    export default {
        mixins: [mixins],
        computed: {
            ...mapState([
                'initialized',
                'strings',
                'gameMode',
                'gameSession',
                'levels',
                'question',
            ]),
            introVisible() {
                return this.gameMode === MODE_INTRO;
            },
            levelsVisible() {
                return this.gameMode === MODE_LEVELS;
            },
            questionVisible() {
                return this.gameMode === MODE_QUESTION;
            },
            sessionVisible() {
                return this.levelsVisible || this.questionVisible;
            },
            statsVisible() {
                return this.gameMode === MODE_STATS;
            },
            helpVisible() {
                return this.gameMode === MODE_HELP;
            }
        },
        components: {
            help,
            intro,
            levels,
            loadingAlert,
            question,
            session,
            stats,
            topbar,
            VkGrid
        }
    }
</script>

<style scoped>
</style>
