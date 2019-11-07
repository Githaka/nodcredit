require('./bootstrap');

window.Vue = require('vue');

import LoanForm from './components/LoanForm.vue';

import VueCurrencyFilter from 'vue-currency-filter'


Vue.use(VueCurrencyFilter,
    {
        symbol : 'NGN',
        thousandsSeparator: ',',
        fractionCount: 0,
        fractionSeparator: '.',
        symbolPosition: 'front',
        symbolSpacing: false
    });


const app = new Vue({
    el: '#loan-form',
    components: { LoanForm },
    template: '<loan-form></loan-form>'
});
