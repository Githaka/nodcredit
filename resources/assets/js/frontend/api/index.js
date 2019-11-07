import axios from 'axios';

import Auth from './Auth';

export default {

    Auth,

    loanInfo: () => {
        return axios.get('/v2/loan/info');
    },

    investInfo: () => {
        return axios.get('/v2/invest/info');
    },

};
