<template>
    <div class="getStarted">
        <div class="g-left">
            <a @click="$emit('back')" class="mb-4">
                <img src="/frontend/images/arrow-up.svg" alt="Go back">
            </a>
            <div class="animated slideInDown">
                <h6>Almost Done</h6>
                <h3 class="text-left">We need more information to secure your account</h3>
                <div class="row mt-4">
                    <div class="col-sm-9">
                        <div class="md-form">
                            <input v-model="form.bvn" type="text" id="loan-wizard-bvn" class="form-control">
                            <label for="loan-wizard-bvn" class="">BVN Number</label>
                        </div>
                        <div class="md-form">
                            <input v-model="form.email" type="text" id="loan-wizard-email" class="form-control">
                            <label for="loan-wizard-email">Email Address</label>
                        </div>
                        <div class="md-form">
                            <input v-model="form.phone" type="text" id="loan-wizard-phone" class="form-control">
                            <label for="loan-wizard-phone">Phone Number</label>
                            <div>Verification is required. You must use a valid mobile line.</div>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input v-model="form.agree" type="checkbox" class="custom-control-input" id="loan-wizard-agree">
                            <label class="custom-control-label" for="loan-wizard-agree">
                                I agree to the <a target="_blank" href="/v2/term-conditions">Terms of Use</a>
                            </label>
                        </div>

                        <form-errors :errors="formErrors" class="mt-2"></form-errors>

                        <button @click="submit()" class="btn-outline-md largeButton-2 mt-3">Continue</button>
                    </div>

                    <loader v-if="isSending">Sending...</loader>

                </div>
            </div>
        </div>
        <div class="g-Right">
            <div class="gcontent animated  slideInRight">
                <h6> <span><img src="/frontend/images/help.svg" alt=""></span> Why do you need this?</h6>
                <p>We need this information to be able to successfully secure your account, and also to reduces your registration process.</p>
            </div>
        </div>
    </div>
</template>

<script>

    import API from '@/frontend/api'

    export default {
        data() {
            return {
                isSending: false,

                form: {
                    agree: false,
                    bvn: '',
                    email: '',
                    phone: '',
                },

                formErrors: {},
            }
        },

        methods: {
            submit() {

                if (this.isSending) {
                    return false;
                }

                this.isSending = true;

                this.formErrors = {};

                API.Auth.registerCustomer(this.form)
                    .then(response => {
                        window.location.href = response.data.redirect_to;
                    })
                    .catch(error => {
                        this.formErrors = error.response.data.errors;
                    })
                    .then(() => {
                        this.isSending = false;
                    });
            }
        }
    }

</script>