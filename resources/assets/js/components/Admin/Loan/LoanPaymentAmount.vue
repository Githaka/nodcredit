<template>
    <div class="loan-payment-amount">

        <button @click="showModal()" class="c-btn">Increase Amount</button>

        <modal
                v-if="showIncreaseModal"
                @close="closeModal()"
        >
            <h5 slot="header">Increase Payment Amount</h5>

            <div slot="body" v-if="isLoaded">
                <div class="c-field u-mb-small">
                    <label class="c-field__label">Increase by</label>
                    <div class="row">
                        <div class="col-8">
                            <input v-model="increaseValue" type="number" class="c-input" min="1">
                        </div>
                        <div class="col-4">
                            <select v-model="increaseType" class="c-input">
                                <option value="percent">%</option>
                                <option value="fixed">NGN</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="o-media u-mb-small">
                    <div class="o-media__body u-flex u-justify-between">
                        <p>Current Amount:</p>
                        <span class="u-text-small">NGN {{ payment.amount }}</span>
                    </div>
                </div>

                <div class="o-media u-mb-small">
                    <div class="o-media__body u-flex u-justify-between">
                        <p class="u-text-bold">New Amount:</p>
                        <span class="u-text-small u-text-bold">NGN {{ newAmount }}</span>
                    </div>
                </div>

                <form-errors :errors="formErrors"></form-errors>

                <loader v-if="isSending">Please wait...</loader>
            </div>

            <div slot="footer">
                <button @click="submit()" class="c-btn c-btn--info">Increase</button>
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

                showIncreaseModal: false,

                formErrors: {},

                increaseType: 'fixed',
                increaseValue: 1,

                payment: {}
            }
        },

        computed: {
            newAmount() {
                let amount = 0;
                let currentAmount = parseFloat(this.payment.amount);
                let increaseValue = parseFloat(this.increaseValue);

                if (this.increaseType === 'fixed') {
                    amount = currentAmount + increaseValue;
                }
                else if (this.increaseType === 'percent') {
                    amount = currentAmount * (100 + increaseValue) / 100;
                }

                return amount.toFixed(2);
            }
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
                        this.payment = response.data.payment;
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
                    value: this.increaseValue,
                    type: this.increaseType,
                };

                axios
                    .post(`/mainframe/payments/${this.id}/increase-amount`, data)
                    .then(response => {
                        this.payment = response.data.payment;
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
                this.showIncreaseModal = false;
                this.formErrors = {};
            },

            showModal() {
                this.load();
                this.showIncreaseModal = true;
            }
        },
    }
</script>
<style lang="sass">
    .loan-payment-amount
        display: inline-block
        vertical-align: top
</style>