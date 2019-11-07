<template>
    <div class="getStarted auth-forgot-password">
        <div class="g-left">

            <a @click="back()" class="mb-4">
                <img src="/frontend/images/arrow-up.svg" alt="Go back">
            </a>

            <div v-if="! showSuccess" class="animated slideInDown">
                <h3 class="text-left">Reset Account Password</h3>
                <div class="row mt-4">
                    <div class="col-sm-9">
                        <div class="md-form">
                            <input v-model="identity" type="text" id="auth-login-username" class="form-control">
                            <label for="auth-login-username">Phone Number/Email Address</label>
                        </div>

                        <form-errors :errors="formErrors" class="mt-2"></form-errors>

                        <button @click="submit()" class="btn-outline-md largeButton-2 mt-3 mb-4">Reset Password</button>

                        <div class="mb-2">First time user? <a href="/v2/loan/start">Create a free account</a></div>
                        <div>Want to login instead? <a href="/v2/auth/login">Login</a></div>
                    </div>

                    <loader v-if="isSending">Please, wait...</loader>
                </div>
            </div>
            <div v-else="" class="animated slideInDown">
                <h3 class="text-left">We've sent you reset instruction</h3>
                <div class="row mt-4">
                    <div class="col-sm-9">
                        Please check your email for password reset instruction.
                        You may want to check your junk/spam folder in case and remember to mark the email NOT Junk in that case.
                    </div>
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
                showSuccess: false,

                formErrors: {},

                identity: '',
            }
        },

        methods: {

            submit() {
                if (this.isSending) {
                    return;
                }

                this.isSending = true;

                API.Auth.forgotPassword({identity: this.identity})
                    .then(response => {
                        this.showSuccess = true;
                    })
                    .catch(error => {
                        this.showSuccess = false;
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