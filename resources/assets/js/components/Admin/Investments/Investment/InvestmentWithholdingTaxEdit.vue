<template>
    <div class="investment-withholding-tax-edit-wrapper">

        <button @click="showModal()" class="c-btn c-btn--small">Edit</button>

        <modal
                v-if="showEditModal"
                @close="closeModal()"
                class="modal-investment-withholding-tax-edit"
        >
            <h5 slot="header">Investment Withholding Tax</h5>

            <div slot="body" v-if="isLoaded">

                <div class="u-mb-small">
                    <p>WHT applies to scheduled interest payouts.</p>
                </div>

                <div class="o-media u-mb-small">
                    <div class="o-media__body u-flex u-justify-between">
                        <p>Percentage, %:</p>
                        <span class="u-text-small">
                            <input v-model="withholdingTaxPercent" type="number" class="c-input u-width-100" step="1" min="1" max="100">
                        </span>
                    </div>
                </div>

                <form-errors :errors="formErrors"></form-errors>

                <loader v-if="isSending">Please wait...</loader>
                <loader v-if="isLoading"></loader>
            </div>

            <div slot="footer">
                <button v-if="isLoaded" @click="submit()" class="c-btn c-btn--info">Submit</button>
                <button @click="closeModal()" class="c-btn c-btn--primary">Close</button>
            </div>
        </modal>

    </div>
</template>

<script>

    import API from '@/api';

    export default {

        props: {
            id: {
                type: String,
                required: true
            }
        },

        data() {
            return {
                isLoading: false,
                isLoaded: false,
                isSending: false,

                showEditModal: false,

                formErrors: {},
                withholdingTaxPercent: 0,
            }
        },

        methods: {
            load() {

                if (this.isLoading) {
                    return false;
                }

                this.isLoading = true;

                API.Admin.Investments.get(this.id)
                    .then(response => {
                        this.withholdingTaxPercent = response.data.investment.withholding_tax_percent;
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

                let data = {
                    withholding_tax_percent: this.withholdingTaxPercent,
                };

                API.Admin.Investments.editWithholdingTax(this.id, data)
                    .then(response => {
                        this.withholdingTaxPercent = response.data.investment.withholding_tax_percent;
                        this.$emit('changed');
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
                this.showEditModal = false;
                this.formErrors = {};
            },

            showModal() {
                this.load();
                this.showEditModal = true;
            }
        }
    }
</script>
