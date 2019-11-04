<template lang="pug">
    .uk-grid.uk-grid-collapse.top-bar(uk-grid).uk-flex-middle
        .uk-width-expand
            button.btn.uk-margin-small-left(@click="restartGame", :class="{'btn-primary': isGameOver, 'btn-default': !isGameOver}")
                v-icon(name="redo").uk-margin-small-right
                span {{ strings.game_btn_restart }}
            button.btn.btn-default.uk-margin-small-left(@click="showHighscore", v-if="highscoreButtonVisible")
                v-icon(name="chart-line").uk-margin-small-right
                span {{ strings.game_btn_highscore }}
            button.btn.btn-default.uk-margin-small-left(@click="showGame", v-if="gameButtonVisible")
                v-icon(name="gamepad").uk-margin-small-right
                span {{ strings.game_btn_game }}
        .uk-width-auto
            button.btn.btn-default.uk-margin-small-right(@click="showAdmin", v-if="adminButtonVisible")
                v-icon(name="cogs")
            button.btn.btn-default.uk-margin-small-right(@click="showHelp")
                v-icon(name="regular/question-circle")
</template>

<script>
    import {mapActions, mapMutations, mapState} from 'vuex';
    import {GAME_PROGRESS, MODE_HELP, MODE_LEVELS, MODE_QUESTION, MODE_HIGHSCORE} from "../constants";
    import _ from 'lodash';
    import mixins from '../mixins';

    export default {
        mixins: [mixins],
        computed: {
            ...mapState([
                'strings',
                'game',
                'gameMode',
                'gameSession',
                'question'
            ]),
            isAdminUser() {
                return this.game.mdl_user_teacher;
            },
            isAdminScreen() {
                return this.$route.name === 'admin-screen';
            },
            isGameScreen() {
                return this.$route.name === 'game-screen';
            },
            isGameOver() {
                return this.gameSession === null || this.gameSession.state !== GAME_PROGRESS;
            },
            highscoreButtonVisible() {
                return this.gameMode !== MODE_HIGHSCORE || !this.isGameScreen;
            },
            gameButtonVisible() {
                if (this.gameSession === null || this.question === null) {
                    return false;
                }
                let modes = [MODE_HIGHSCORE, MODE_HELP];
                return _.includes(modes, this.gameMode) || !this.isGameScreen;
            },
            adminButtonVisible() {
                return this.isAdminUser && !this.isAdminScreen;
            }
        },
        methods: {
            ...mapActions([
                'createGameSession'
            ]),
            ...mapMutations([
                'setGameMode'
            ]),
            restartGame() {
                this.createGameSession();
                this.goToGameRoute();
            },
            showHighscore() {
                this.setGameMode(MODE_HIGHSCORE);
                this.goToGameRoute();
            },
            showGame() {
                if (!this.question || this.question.finished) {
                    this.setGameMode(MODE_LEVELS);
                } else {
                    this.setGameMode(MODE_QUESTION)
                }
                this.goToGameRoute();
            },
            showAdmin() {
                this.goToAdminRoute();
            },
            showHelp() {
                this.setGameMode(MODE_HELP);
                this.goToGameRoute();
            },
            goToAdminRoute() {
                if (!this.isAdminScreen) {
                    this.$router.push({name: 'admin-screen'});
                }
            },
            goToGameRoute() {
                if (!this.isGameScreen) {
                    this.$router.push({name: 'game-screen'});
                }
            }
        },
    }
</script>

<style scoped>
    .top-bar {
        background-color: #f8f8f8;
        border-top-left-radius: 5px;
        border-top-right-radius: 5px;
        border: 1px solid #ccc;
        height: 50px;
    }
</style>
