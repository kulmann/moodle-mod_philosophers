<template lang="pug">
    b {{ remainingSeconds }}
</template>

<script>
    import {mapActions, mapState} from 'vuex';

    export default {
        props: {
            question: Object,
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
            ])
        },
        watch: {
            remainingSeconds (seconds) {
                if (this.question && this.cancelledQuestionId !== this.question.id && seconds <= 0) {
                    this.cancelledQuestionId = this.question.id;
                    this.cancelAnswer({
                        'gamesessionid': this.question.gamesession,
                        'questionid': this.question.id,
                    });
                    console.log("cancelled question " + this.question.id);
                }
            }
        }
    }
</script>
