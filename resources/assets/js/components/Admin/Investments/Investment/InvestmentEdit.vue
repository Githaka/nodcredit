<template>

    <div class="investment-edit-wrapper">

        <button @click="showModal()" class="c-btn c-btn--small">Edit</button>

        <modal
                v-if="showEditModal"
                @close="closeModal()"
                class="modal-investment-edit"
        >
            <h5 slot="header">Investment: Edit</h5>

            <div slot="body" v-if="isLoaded">

                <p v-if="! investment.is_changeable" class="u-mb-small">You can`t change Investment if it is ended/liquidated or has any paid profit.</p>
                <div v-else>
                    <div class="o-media u-mb-small">
                        <div class="o-media__body u-flex u-justify-between">
                            <p>Name:</p>
                            <span class="u-text-small">
                                <input v-model="investment.plan_name" type="text" class="c-input u-width-100">
                            </span>
                        </div>
                    </div>
                    <div class="o-media u-mb-small">
                        <div class="o-media__body u-flex u-justify-between">
                            <p>Tenor, days:</p>
                            <span class="u-text-small">
                                <input v-model="investment.plan_days" type="number" class="c-input u-width-100" step="1" min="1" max="1000">
                            </span>
                        </div>
                    </div>
                    <div class="o-media u-mb-small">
                        <div class="o-media__body u-flex u-justify-between">
                            <p>Percentage, %:</p>
                            <span class="u-text-small">
                                <input v-model="investment.plan_percentage" type="number" class="c-input u-width-100" step="1" min="1" max="100">
                            </span>
                        </div>
                    </div>
                    <div class="o-media u-mb-small">
                        <div class="o-media__body u-flex u-justify-between">
                            <p>Profit payout:</p>
                            <span class="u-text-small">
                                <select v-model="investment.profit_payout_type" class="c-input u-width-100">
                                    <option value="monthly">Monthly</option>
                                    <option value="single">In the end</option>
                                </select>
                            </span>
                        </div>
                    </div>

                    <p><b><i>Submit will re-build profit payments plan.</i></b></p>

                    <form-errors :errors="formErrors"></form-errors>
                </div>

                <loader v-if="isSending">Please wait...</loader>
                <loader v-if="isLoading"></loader>
            </div>

            <div slot="footer">
                <button v-if="isLoaded && investment.is_changeable" @click="submit()" class="c-btn c-btn--info">Submit</button>
                <button @click="closeModal()" class="c-btn c-btn--primary">Close</button>
            </div>
        </modal>

    </div>


</template>

<script>

    import API from '../../../../api';

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
                investment: {},
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
                        this.investment = response.data.investment;
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
                    plan_name: this.investment.plan_name,
                    plan_days: this.investment.plan_days,
                    plan_percentage: this.investment.plan_percentage,
                    profit_payout_type: this.investment.profit_payout_type,
                };

                API.Admin.Investments.edit(this.id, data)
                    .then(response => {
                        this.investment = response.data.investment;
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
