import _ from 'lodash';
export default {
    filters: {
        formatCurrency: formatCurrencyInternal,
        stringParams(translation, params) {
            let tmp = translation;
            if (translation.includes('{$a}')) {
                return _.replace(tmp, '{$a}', params);
            } else if (translation.includes('{$a->')) {
                _.forEach(params, function(value, key) {
                    tmp = _.replace(tmp, '{$a->' + key + '}', value);
                });
                return tmp;
            }
        }
    },
    methods: {
        findUnfinishedLevel(levels) {
            let unfinishedLevels = _.filter(levels, function (level) {
                return level.seen && !level.finished;
            });
            if (unfinishedLevels.length === 0) {
                return null;
            } else {
                return _.first(unfinishedLevels);
            }
        },
        formatCurrency: formatCurrencyInternal,
    }
}
function formatCurrencyInternal(amount, currencySign) {
    return amount.toLocaleString() + " " + currencySign;
}
