<template lang="pug">
    #philosophers-intro
        template(v-if="game")
            h1.uk-text-center {{ game.name }}
        p.uk-text-center
            button.btn.btn-primary(@click="startGame")
                span {{ strings.game_btn_start }}
</template>

<script>
    import {mapMutations, mapState} from 'vuex';
    import mixins from '../../mixins';
    import {MODE_LEVELS} from "../../constants";

    export default {
        mixins: [mixins],
        computed: {
            ...mapState([
                'strings',
                'game',
            ]),
        },
        methods: {
            ...mapMutations([
                'setGameMode'
            ]),
            startGame() {
                this.setGameMode(MODE_LEVELS);
            }
        }
    }
</script>
