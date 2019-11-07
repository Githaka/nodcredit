import axios from 'axios';

export default {

    getAccount: id => {
        return axios.get(`/mainframe/accounts/${id}`, data);
    },

    banAccount: (id, data) => {
        return axios.post(`/mainframe/accounts/${id}/ban`, data);
    },

};
