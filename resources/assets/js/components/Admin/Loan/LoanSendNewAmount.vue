<template>
    <div class="loan-send-new-amount">

        <button @click="showModal()" class="c-btn">Send New Amount</button>

        <modal
                v-if="showNewAmountModal"
                @close="closeModal()"
        >
            <h5 slot="header">Send New Amount Confirmation mail</h5>

            <div slot="body" v-if="isLoaded">

                <div class="o-media u-mb-small">
                    <div class="o-media__body u-flex u-justify-between">
                        <p>Amount requested:</p>
                        <span class="u-text-small">NGN {{ loan.amount_requested }}</span>
                    </div>
                </div>

                <h5>New Amount</h5>

                <div class="u-mb-small">
                    <label class="c-field__label">Use presets</label>
                    <button v-for="amountButton in amounts"
                            @click="amount = amountButton.value"
                            class="c-btn c-btn--small" style="margin: 3px;"
                            :class="{
                               'c-btn--info': amountButton.value === amount,
                               'c-btn--outline': amountButton.value !== amount,
                           }"
                    >{{ amountButton.name }}</button>
                </div>

                <div class="c-field u-mb-small">
                    <label class="c-field__label">Or input value</label>
                    <input v-model="amount" type="number" class="c-input" min="100" step="100">
                </div>


                <form-errors :errors="formErrors"></form-errors>

                <loader v-if="isSending">Please wait...</loader>
                <loader v-if="isLoading"></loader>
            </div>

            <div slot="footer">
                <button @click="submit()" class="c-btn c-btn--info">Send</button>
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

                showNewAmountModal: false,

                formErrors: {},
                loan: {},

                amount: 10000,

                amounts: [
                    {value: 10000, name: '10,000'},
                    {value: 15000, name: '15,000'},
                    {value: 20000, name: '20,000'},
                    {value: 25000, name: '25,000'},
                    {value: 30000, name: '30,000'},
                    {value: 35000, name: '35,000'},
                    {value: 40000, name: '40,000'},
                    {value: 45000, name: '45,000'},
                    {value: 50000, name: '50,000'},
                ],
            }
        },

        methods: {
            load() {

                if (this.isLoading) {
                    return false;
                }

                this.isLoading = true;

                axios
                    .get(`/mainframe/loans/${this.id}/json`)
                    .then(response => {
                        this.loan = response.data.loan;
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
                this.formErrors = {};

                axios
                    .post(`/mainframe/loans/${this.id}/send-new-amount`, {amount: this.amount})
                    .then(response => {
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
                this.showNewAmountModal = false;
                this.formErrors = {};
            },

            showModal() {
                this.load();
                this.showNewAmountModal = true;
            }
        },
    }
</script>
<style lang="sass">
    .loan-send-new-amount
        display: inline-block
        vertical-align: top
</style>