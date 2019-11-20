<template lang="pug">
    vk-grid(matched)
        div(v-for="level in levels", :key="level.id", class="uk-width-1-1@s uk-width-1-2@m uk-width-1-3@l uk-width-1-4@xl")
            level(:level="level", :strings="strings", :game="game", @onSelectLevel="selectLevel")
</template>

<script>
    import {mapActions, mapState} from 'vuex';
    import mixins from '../../mixins';
    import Level from "../helper/level";

    export default {
        mixins: [mixins],
        computed: {
            ...mapState([
                'strings',
                'levels',
                'game',
            ]),
        },
        methods: {
            ...mapActions([
                'showQuestionForLevel',
            ]),
            selectLevel(levelId) {
                this.showQuestionForLevel(levelId);
            },
        },
        components: {Level}
    }
</script>
