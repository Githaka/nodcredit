<template>
    <div class="getStarted auth-login">
        <div class="g-left">

            <a @click="back()" class="mb-4">
                <img src="/frontend/images/arrow-up.svg" alt="Go back">
            </a>

            <div class="animated slideInDown">
                <h3 class="text-left">Sure thing. Enter your login details.</h3>
                <div class="row mt-4">
                    <div class="col-sm-9">
                        <div class="md-form">
                            <input v-model="identity" type="text" id="auth-login-username" class="form-control">
                            <label for="auth-login-username">Phone Number/Email Address</label>
                        </div>
                        <div class="md-form">
                            <input v-model="password" type="password" id="auth-login-password" class="form-control">
                            <span data-toggle="#auth-login-password" class="fa fa-fw fa-eye field-icon js-password-toggle"></span>
                            <label for="auth-login-password" class="">Password</label>
                        </div>

                        <div><a href="/v2/auth/forgot-password">Forgot password?</a></div>

                        <form-errors :errors="formErrors" class="mt-2"></form-errors>

                        <button @click="submit()" class="btn-outline-md largeButton-2 mt-3 mb-4">Login</button>

                        <div>First time user? <a href="/v2/loan/start">Create a free account</a></div>

                        <p class="small">
                            By loging in, you express consent to receiving customer service & marketing calls and text from NodCredit.
                            Consent is not a condition of purchase and you may opt-out at any time.
                        </p>
                    </div>

                    <loader v-if="isSending">Please, wait...</loader>

                </div>
            </div>
        </div>
        <div class="g-Right"></div>
    </div>
</template>

<script>

    import API from '@/frontend/api'

    export default {

        data() {
            return {
                isSending: false,

                formErrors: {},

                identity: '',
                password: '',
            }
        },

        methods: {

            submit() {
                if (this.isSending) {
                    return;
                }

                this.isSending = true;

                const data = {
                    identity: this.identity,
                    password: this.password
                };

                API.Auth.login(data)
                    .then(response => {
                        window.location.href = response.data.redirect_to;
                    })
                    .catch(error => {
                        this.formErrors = error.response.data.errors;
                    })
                    .then(() => {
                        this.isSending = false;
                    });
            },

            back() {
                window.history.back();
            }
        }
    }

</script>