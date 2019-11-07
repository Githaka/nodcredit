import Vue from 'vue';

Vue.component('admin-loan-payment-parts', require('./LoanPaymentParts.vue').default);
Vue.component('admin-loan-payment-amount', require('./LoanPaymentAmount.vue').default);
Vue.component('admin-loan-payment-penalty', require('./LoanPaymentPenalty.vue').default);
Vue.component('admin-loan-send-new-amount', require('./LoanSendNewAmount.vue').default);