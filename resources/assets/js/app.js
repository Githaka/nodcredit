require('./bootstrap');
require('./custom');
require('./store');

require('./modules/datetimepicker');
require('./modules/currency-filter');
require('./modules/highcharts');

window.Vue = require('vue');

require('./components/Parts');
require('./components/FormElements');

Vue.component('loader', require('./components/Loader.vue').default);
Vue.component('modal', require('./components/Modal.vue').default);
Vue.component('admin-disbursed-and-repayment-chart', require('./components/Admin/DisbursedAndRepaymentChart.vue').default);
Vue.component('admin-customers-charts', require('./components/Admin/CustomersCharts.vue').default);
Vue.component('account-investments', require('./components/Account/Investments/Investments.vue').default);

require('./components/Admin/Accounts');
require('./components/Admin/Investments');
require('./components/Admin/Loan');

const app = new Vue({
    el: '#app'
});
