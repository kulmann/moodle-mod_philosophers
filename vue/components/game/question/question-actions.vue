<template lang="pug">
    #philosophers-question_actions
        .uk-heading-divider.uk-margin-small-bottom
        .uk-align-right
            button.btn.btn-default(@click="showLevelOverview")
                v-icon(name="arrow-circle-right").uk-margin-small-right
                span {{ strings.game_btn_continue }}
</template>

<script>
    import {mapMutations, mapState} from 'vuex';
    import {MODE_LEVELS} from "../../../constants";

    export default {
        computed: {
            ...mapState([
                'strings'
            ])
        },
        methods: {
            ...mapMutations([
                'setGameMode'
            ]),
            showLevelOverview() {
                this.setGameMode(MODE_LEVELS)
            }
        },
    }
</script>

<style scoped>
</style>
