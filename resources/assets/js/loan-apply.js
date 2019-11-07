require('./bootstrap');

window.Vue = require('vue');

import ApplyLoan from './components/ApplyLoan.vue';

import VueCurrencyFilter from 'vue-currency-filter'


Vue.use(VueCurrencyFilter,
    {
        symbol : 'NGN',
        thousandsSeparator: ',',
        fractionCount: 2,
        fractionSeparator: '.',
        symbolPosition: 'front',
        symbolSpacing: true
});

const app = new Vue({
    el: '#root',
    components: { ApplyLoan },
    template: '<apply-loan></apply-loan>'
});
