import axios from 'axios';

export default {
    getAll: () => {
        return axios.get(`/mainframe/investments`);
    },

    get: id => {
        return axios.get(`/mainframe/investments/${id}`);
    },

    edit: (id, data) => {
        return axios.post(`/mainframe/investments/${id}/edit`, data);
    },

    startDateEdit: (id, data) => {
        return axios.post(`/mainframe/investments/${id}/start/edit`, data);
    },

    editWithholdingTax: (id, data) => {
        return axios.post(`/mainframe/investments/${id}/withholding-tax/edit`, data)
    },

    getAddConfig: () => {
        return axios.get(`/mainframe/investments/add`);
    },

    add: data => {
        return axios.post(`/mainframe/investments/add`, data);
    },

    payoutPartialLiquidation: id => {
        return axios.get(`/mainframe/investments/partial-liquidations/${id}/payout`);
    },

    payoutProfitPayment: id => {
        return axios.get(`/mainframe/investments/profit-payments/${id}/payout`);
    },

    editProfitPaymentAutoPayout: (id, data) => {
        return axios.post(`/mainframe/investments/profit-payments/${id}/auto-payout/edit`, data)
    },

};
