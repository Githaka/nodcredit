require('./bootstrap');

window.Vue = require('vue');

import WorkHistory from './components/WorkHistory.vue';


const app = new Vue({
    el: '#work-hisotry',
    components: { WorkHistory },
    template: '<work-history></work-history>'
});
