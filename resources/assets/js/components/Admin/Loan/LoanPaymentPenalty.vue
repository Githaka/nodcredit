<template>
    <div class="loan-payment-penalty" v-if="isLoaded">

        <h4 class="u-mt-medium u-mb-small">Daily Penalty (1%)</h4>
        <div class="o-media u-mb-small">
            <div class="o-media__body u-flex u-justify-between">
                <p>Status:</p>
                <span v-if="payment.is_penalty_paused" class="u-text-small">Paused until {{ payment.penalty_paused_until_formatted }}</span>
                <span v-else="" class="u-text-small text-warning">Active</span>
            </div>
        </div>
        <div class="u-mb-medium" style="text-align: right;">
            <button @click="showModal()" class="c-btn c-btn--small">Manage</button>
        </div>

        <modal
                v-if="showPauseModal"
                @close="closeModal()"
        >
            <h5 slot="header">Pause Daily Penalty (1%)</h5>

            <div slot="body">

                <div class="c-field u-mb-small">
                    <label class="c-field__label">Pause until</label>
                    <input
                            v-model="pauseUntil"
                            type="date"
                            class="c-input"
                    />
                </div>
                <p><small>Leave empty if you need to remove pause</small></p>

                <form-errors :errors="formErrors"></form-errors>

                <loader v-if="isSending">Please wait...</loader>
            </div>

            <div slot="footer">
                <button @click="submit()" class="c-btn c-btn--info">Submit</button>
                <button @click="closeModal()" class="c-btn c-btn--primary">Close</button>
            </div>
        </modal>

    </div>
</template>

<script>
    import axios  from 'axios';

    export default {

        props: {
            id: {
                type: String,
                required: true,
            }
        },

        data() {
            return {
                isLoading: false,
                isLoaded: false,
                isSending: false,

                showPauseModal: false,

                formErrors: {},

                payment: {},
                pauseUntil: ''
            }
        },

        mounted() {
            this.load();
        },

        methods: {
            load() {

                if (this.isLoading) {
                    return false;
                }

                this.isLoading = true;

                axios
                    .get(`/mainframe/payments/${this.id}`)
                    .then(response => {
                        this.payment = response.data.payment_json;
                        this.pauseUntil = response.data.payment_json.penalty_paused_until_formatted;
                        this.isLoaded = true;
                    })
                    .catch(error => {
                        this.isLoaded = false;
                    })
                    .then(() => {
                        this.isLoading = false;
                    })
                ;
            },

            submit() {
                if (this.isSending) {
                    return false;
                }

                this.isSending = true;

                let data = {
                    date: this.pauseUntil
                };

                axios
                    .post(`/mainframe/payments/${this.id}/pause-penalty`, data)
                    .then(response => {
                        this.payment = response.data.payment_json;
                        this.pauseUntil = response.data.payment_json.penalty_paused_until_formatted;
                        window.location.reload();
                    })
                    .catch(error => {
                        this.formErrors = error.response.data.errors;
                    })
                    .then(() => {
                        this.isSending = false;
                    })
                ;
            },

            closeModal() {
                this.showPauseModal = false;
                this.formErrors = {};
            },

            showModal() {
                this.showPauseModal = true;
            }
        },
    }
</script>