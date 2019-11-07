<template>
    <div class="getStarted">
        <div class="g-left animated  slideInDown">
            <h3 class="text-left">We've sent you a four-digit number</h3>
            <div class="row mt-4">
                <div class="col-sm-10">
                    <label>Enter Four-Digit Code</label>

                    <div id="nod-user-otp">
                        <input type="number"
                               v-model="digit1"
                               maxlength="1"
                               min="0"
                               max="9"
                               ref="digit1"
                               autofocus
                        />
                        <input type="number"
                               v-model="digit2"
                               ref="digit2"
                               maxlength="1"
                               min="0"
                               max="9"
                        />
                        <input type="number"
                               v-model="digit3"
                               ref="digit3"
                               maxlength="1"
                               min="0"
                               max="9"
                        />
                        <input type="number"
                               v-model="digit4"
                               ref="digit4"
                               maxlength="1"
                               min="0"
                               max="9"
                        />
                    </div>
                    <div class="my-3 pt-4">
                        We've sent code to <b>{{ phone }}</b> at <b>{{ sentAt }}</b>
                    </div>
                    <div class="my-3">Didn't receive code?

                        <span v-if="resendTimer > 0">You can resend code after <b>{{ resendTimer }}s</b></span>
                        <a v-else="" @click="resend()" href="#">Resend Code</a>
                    </div>

                    <form-errors :errors="formErrors" class="mt-2"></form-errors>

                    <button @click="submit()"
                            class="btn-outline-md largeButton-2 mt-4"
                            :class="{disabled: code.length < 4}"
                            :disabled="code.length < 4"

                    >Verify and Submit</button>
                </div>

                <loader v-if="isSending">Please, wait...</loader>

            </div>
        </div>
        <div class="g-Right animated slideInRight">
            <div class="gcontent">
                <h6> <span><img src="/frontend/images/help.svg" alt=""></span> You need to check your phone</h6>
                <p>
                    We promise this will be the only time we'll ask you to use this code during sign up.
                    We will email your subsequently if we have a need to talk to you.
                    So it is important that you verify your account.
                </p>
            </div>
        </div>
    </div>
</template>

<script>

    import API from '@/frontend/api'

    import {DateTime} from 'luxon'

    export default {

        props: {
            phone: {
                required: true
            },
            sentAt: {
                type: String,
                required: true
            }
        },

        data() {
            return {
                isSending: false,

                formErrors: {},

                digit1: '',
                digit2: '',
                digit3: '',
                digit4: '',

                resendTimer: 60,
                resendDate: null
            }
        },

        computed: {
            code() {
                return this.digit1 + this.digit2 + this.digit3 + this.digit4;
            }
        },

        watch: {
            digit1(value) {

                if (! value) {
                    return this.focusOn(1);
                }

                this.digit1 = value[0];
                this.focusOn(2);
            },
            digit2(value) {

                if (! value) {
                    return this.focusOn(2);
                }

                this.digit2 = value[0];
                this.focusOn(3);
            },
            digit3(value) {

                if (! value) {
                    return this.focusOn(3);
                }

                this.digit3 = value[0];
                this.focusOn(4);
            },
            digit4(value) {

                if (! value) {
                    return this.focusOn(4);
                }

                this.digit4 = value[0];

                if (this.code.length > 3) {
                    this.submit();
                }
            },
        },

        mounted() {
            const resendDate = DateTime.fromSQL(this.sentAt).plus({seconds: 60});

            this.resendTimer = parseInt(resendDate.diffNow('seconds').seconds);

            const interval = setInterval(() => {
                this.resendTimer--;
            }, 1000);

            setTimeout(function () {
                clearInterval(interval);
                this.resendTimer = 0;
            }, this.resendTimer * 1000);
        },

        methods: {

            submit() {
                if (this.isSending) {
                    return;
                }

                this.isSending = true;

                API.Auth.phoneVerify({code: this.code})
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

            resend() {
                window.location.reload();
            },

            focusOn(digitNumber) {
                this.$refs[`digit${digitNumber}`].focus();
            }
        }
    }

</script>