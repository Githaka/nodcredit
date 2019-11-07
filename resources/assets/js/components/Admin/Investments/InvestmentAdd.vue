<template>

    <div class="investment-add" style="display: inline-block;">

        <button @click="showModal()" class="c-btn c-btn--info c-btn--small">+ Add Investment</button>

        <modal
                v-if="showAddModal"
                class="modal-investment-add"
                @close="closeModal()"
        >
            <h4 slot="header">Add Investment</h4>

            <div slot="body" v-if="isLoaded">

                    <div class="row u-mb-medium">
                        <div class="col-md-12">
                            <p>Select Investor</p>
                            <div class="c-select">
                                <select v-model="userId" class="c-select__input">
                                    <option v-for="investor in investors" :value="investor.id">
                                        {{ investor.name }} ({{ investor.email }})
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row u-mb-small">
                        <div class="col-md-6">
                            <p>Investment Amount</p>
                            <input
                                    v-model="amount"
                                    type="number"
                                    :min="minAmount"
                                    :max="maxAmount"
                                    step="500"
                                    class="c-input"
                            >
                            <p class="small-text">{{ minAmount | currency }} - {{ maxAmount | currency }}</p>
                        </div>
                        <div class="col-md-6">
                            <p>Tenor of investment?</p>
                            <div class="c-select">
                                <select v-model="selectedPlan" class="c-select__input">
                                    <option v-for="plan in plans" :value="plan">{{ plan.name }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                <div class="row u-mb-medium">
                    <div class="col-md-6">
                        <p>Profit payout:</p>
                        <div class="c-select">
                            <select v-model="profitPayoutType" class="c-select__input">
                                <option v-for="payoutType in profitPayoutTypes" :value="payoutType.value">{{ payoutType.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                    <div class="row u-mb-medium investment-add-totals">
                        <div class="col-md-6">
                            <div class="u-pv-small result-card">
                                <p>Total Returns at <span v-if="Object.keys(selectedPlan).length">{{ selectedPlan.percentage }}</span>% Interest</p>
                                <h3>{{ totalReturns | currency }}</h3>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="u-pv-small result-card">
                                <p>Total Net Profit</p>
                                <h3>{{ totalNetProfit | currency }}</h3>
                            </div>
                        </div>
                    </div>

                <form-errors :errors="formErrors"></form-errors>

                <loader v-if="isSending">Please wait...</loader>
                <loader v-if="isLoading"></loader>
            </div>

            <div slot="footer">
                <button @click="submit()" class="c-btn btn-primary c-btn--large c-btn--fullwidth">Add Investment</button>
            </div>
        </modal>

    </div>

</template>

<script>
    import API from './../../../api';

    export default {

        data() {
            return {
                isLoading: false,
                isLoaded: false,
                isSending: false,

                showAddModal: false,

                userId: '',
                amount: 0,
                plans: [],
                selectedPlan: {},
                investors: [],
                profitPayoutType: '',
                profitPayoutTypes: [],
                minAmount: 0,
                maxAmount: 0,

                formErrors: {}
            }
        },

        computed: {
            totalNetProfit() {
                let profit = 0;

                if (! Object.keys(this.selectedPlan).length) {
                    return 0;
                }

                const perDay = this.amount * this.selectedPlan.percentage / 100 / 365;
                profit = perDay * this.selectedPlan.days;

                return profit;
            },

            totalReturns() {
                return parseInt(this.amount) + this.totalNetProfit;
            },

        },

        methods: {

            closeModal() {
                this.showAddModal = false;
                this.formErrors = {};
            },

            showModal() {
                this.load();
                this.showAddModal = true;
            },


            load() {

                if (this.isLoading) {
                    return false;
                }

                this.isLoading = true;

                API.Admin.Investments.getAddConfig()
                    .then(response => {
                        this.minAmount = response.data.min_amount;
                        this.amount = response.data.min_amount;
                        this.maxAmount = response.data.max_amount;
                        this.plans = response.data.plans;
                        this.selectedPlan = response.data.plans[0];
                        this.investors = response.data.investors;
                        this.profitPayoutTypes = response.data.profit_payout_types;

                        this.isLoaded = true;
                    })
                    .catch(error => {
                        if (this.loadCount < 3) {
                            this.load();
                        }

                        this.isLoaded = false;
                    })
                    .then(() => {
                        this.isLoading = false;
                    })
                ;
            },

            resetForm() {
                this.amount = this.minAmount;
                this.userId = '';
                this.selectedPlan = this.plans[0];
                this.profitPayoutType = 'single';
            },

            submit() {

                if (this.isSending) {
                    return false;
                }

                this.isSending = true;
                this.formErrors = {};

                let data = {
                    amount: this.amount,
                    user: this.userId,
                    tenor: this.selectedPlan.value,
                    profit_payout_type: this.profitPayoutType,
                };

                API.Admin.Investments.add(data)
                    .then(response => {
                        this.resetForm();
                        this.closeModal();
                        this.$emit('added');
                    })
                    .catch(error => {
                        this.formErrors = error.response.data.errors;
                    })
                    .then(() => {
                        this.isSending = false;
                    })
                ;
            },

        },
    }
</script>
