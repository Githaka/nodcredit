import axios from 'axios';

export default {
    registerCustomer: (data) => {
        return axios.post('/v2/auth/register/customer', data);
    },

    registerInvestor: (data) => {
        return axios.post('/v2/auth/register/investor', data);
    },

    phoneVerify: (data) => {
        return axios.post('/v2/auth/phone/verify', data);
    },

    phoneEdit: (data) => {
        return axios.post('/v2/auth/phone/edit', data);
    },

    login: (data) => {
        return axios.post('/v2/auth/login', data)
    },

    forgotPassword: (data) => {
        return axios.post('/v2/auth/forgot-password', data)
    },

    resetPassword: (data) => {
        return axios.post('/v2/auth/reset-password', data)
    },

}
