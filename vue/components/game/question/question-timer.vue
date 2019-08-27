<template lang="pug">
    b.time(:class="timeLabelClass") {{ remainingSeconds }}
</template>

<script>
    import {mapActions, mapState} from 'vuex';

    export default {
        props: {
            question: Object,
            game: Object,
        },
        data() {
            return {
                cancelledQuestionId: 0,
            }
        },
        computed: {
            ...mapState([
                'strings',
                'now',
            ]),
            timeLabelClass() {
                if (this.remainingSeconds > this.question.score_max) {
                    return 'time-reading';
                }
                if (this.remainingSeconds > (this.question.score_max / 3 * 2)) {
                    return 'time-plenty';
                }
                if (this.remainingSeconds > (this.question.score_max / 3)) {
                    return 'time-okish';
                }
                return 'time-tight';
            },
            remainingSeconds() {
                if (this.question.timeremaining >= 0) {
                    return this.question.timeremaining;
                } else {
                    let start = this.question.timecreated * 1000;
                    let end = start + (this.question.time_max * 1000);
                    return Math.max(0, Math.round((end - this.now) / 1000));
                }
            }
        },
        methods: {
            ...mapActions([
                'cancelAnswer',
            ]),
        },
        watch: {
            remainingSeconds(seconds) {
                if (this.question && this.cancelledQuestionId !== this.question.id && seconds <= 0) {
                    this.cancelledQuestionId = this.question.id;
                    this.cancelAnswer({
                        'gamesessionid': this.question.gamesession,
                        'questionid': this.question.id,
                    });
                }
            }
        }
    }
</script>

<style scoped>
    .time {
        font-weight: bold;
    }

    .time-reading {
        color: black;
    }

    .time-plenty {
        color: green;
    }

    .time-okish {
        color: orange;
    }

    .time-tight {
        color: red;
    }
</style>
