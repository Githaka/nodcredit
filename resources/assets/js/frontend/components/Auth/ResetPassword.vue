<template>
    <div class="getStarted auth-reset-password">
        <div class="g-left">

            <a @click="back()" class="mb-4">
                <img src="/frontend/images/arrow-up.svg" alt="Go back">
            </a>

            <div v-if="! showSuccess" class="animated slideInDown">
                <h3 class="text-left">Set A New Password</h3>
                <div class="row mt-4">
                    <div class="col-sm-9">
                        <div class="md-form">
                            <input v-model="password" type="password" id="auth-reset-password" class="form-control">
                            <span data-toggle="#auth-reset-password" class="fa fa-fw fa-eye field-icon js-password-toggle"></span>
                            <label for="auth-reset-password" class="">Password</label>
                        </div>
                        <div class="md-form">
                            <input v-model="passwordConfirmation" type="password" id="auth-reset-password-confirmation" class="form-control">
                            <span data-toggle="#auth-reset-password-confirmation" class="fa fa-fw fa-eye field-icon js-password-toggle"></span>
                            <label for="auth-reset-password-confirmation" class="">Password Confirmation</label>
                        </div>

                        <form-errors :errors="formErrors" class="mt-2"></form-errors>

                        <button @click="submit()" class="btn-outline-md largeButton-2 mt-3 mb-4">Update Password</button>

                        <div class="mb-2">First time user? <a href="/v2/loan/start">Create a free account</a></div>
                        <div>Want to login instead? <a href="/v2/auth/login">Login</a></div>
                    </div>

                    <loader v-if="isSending">Please, wait...</loader>
                </div>
            </div>
            <div v-else="" class="animated slideInDown">
                <h3 class="text-left">Password Updated Successfully</h3>
                <div class="row mt-4">
                    <div class="col-sm-9">
                        <div><a href="/v2/auth/login" class="btn-fill-md largeButton">Login to your Dashboard</a></div>
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

        props: {
            token: {
                required: true,
                type: String
            }
        },

        data() {
            return {
                isSending: false,
                showSuccess: false,

                formErrors: {},

                password: '',
                passwordConfirmation: '',
            }
        },

        methods: {

            submit() {
                if (this.isSending) {
                    return;
                }

                this.isSending = true;

                const data = {
                    token: this.token,
                    password: this.password,
                    password_confirmation: this.passwordConfirmation,
                };

                API.Auth.resetPassword(data)
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