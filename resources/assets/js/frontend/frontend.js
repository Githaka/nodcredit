require('./bootstrap');
require('@/modules/currency-filter');

window.Vue = require('vue');

Vue.component('loader', require('@/components/Loader.vue').default);
Vue.component('form-errors', require('@/components/FormElements/FormErrors.vue').default);
Vue.component('loan-wizard', require('./components/Loan/Wizard.vue').default);
Vue.component('invest-wizard', require('./components/Invest/Wizard.vue').default);
Vue.component('auth-login', require('./components/Auth/Login.vue').default);
Vue.component('auth-forgot-password', require('./components/Auth/ForgotPassword.vue').default);
Vue.component('auth-reset-password', require('./components/Auth/ResetPassword.vue').default);
Vue.component('auth-phone-verify', require('./components/Auth/PhoneVerify.vue').default);

const app = new Vue({
    el: '#frontend'
});
