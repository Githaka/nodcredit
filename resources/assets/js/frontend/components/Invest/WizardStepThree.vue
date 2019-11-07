<template>
    <div class="getStarted">
        <div class="g-left">
            <a @click="$emit('back')" class="mb-4">
                <img src="/frontend/images/arrow-up.svg" alt="Go back">
            </a>

            <div class="animated slideInDown">
                <h6>Okay, Let's get started</h6>
                <h3 class="text-left">How do you want to invest</h3>
                <div class="row mt-4">
                    <div class="col-sm-10">
                        <div class="md-form">
                            <input v-model="amount"
                                   :min="minAmount"
                                   :max="maxAmount"
                                   step="1000"
                                   type="number"
                                   id="invest-wizard-amount"
                                   class="form-control" />

                            <label for="invest-wizard-amount">How much do you want to invest?</label>
                        </div>

                        <div class="outofplace mb-3">How long do you want to invest for?</div><br>

                        <div class="mt-3">
                            <div v-for="plan in plans" class="form-check form-check-inline mb-3 nod-radio">
                                <label class="rdiobox">
                                    <input v-model="selectedPlan" :value="plan" class="form-check-input" type="radio">
                                    <span>{{ plan.name }}</span>
                                </label>
                            </div>
                        </div>

                        <div v-if="showResult" class="investcalculator animated fadeIn">
                            <h6>Investment Calculator</h6>
                            <hr>
                            <div>
                                <div class="info">You are investing</div>
                                <div class="info-2">{{ amount | currency }}</div>
                            </div>
                            <hr>
                            <div>
                                <div class="info">Investment Duration</div>
                                <div class="info-2">{{ selectedPlan.days }} days</div>
                            </div>
                            <hr>
                            <div>
                                <div class="info">Investment Interest</div>
                                <div class="info-2">{{ selectedPlan.percentage }}% ({{ totalNetProfit | currency }})</div>
                            </div>
                            <hr>
                            <div class="total">
                                <div class="info">Total Returns</div>
                                <div class="info-2">{{ totalReturns | currency }}</div>
                            </div>
                        </div>

                        <button @click="validate()" class="btn-outline-md largeButton-2 mt-3">Continue</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="g-Right">
            <div class="gcontent animated slideInRight">
                <h6> <span><img src="/frontend/images/help.svg" alt=""></span> Why do you need this?</h6>
                <p>These are basic information about investing, we need to know how much you are investing. This is to help us calculate your ROI.</p>
            </div>
        </div>
    </div>
</template>

<script>

    export default {
        props: {
            plans: {
                type: Array
            },

            minAmount: {
                type: Number,
                default: 10000
            },

            maxAmount: {
                type: Number,
                default: 1000000
            },
        },

        data() {
            return {
                amount: '',
                selectedPlan: {},
            }
        },

        computed: {
            showResult() {
                if (! this.amount || this.amount < this.minAmount) {
                    return false;
                }

                if (! Object.keys(this.selectedPlan).length) {
                    return false;
                }

                return true;
            },

            totalNetProfit() {

                if (! Object.keys(this.selectedPlan).length) {
                    return 0;
                }

                const perDay = this.amount * this.selectedPlan.percentage / 100 / 365;

                return perDay * this.selectedPlan.days;
            },

            totalReturns() {
                return parseInt(this.amount) + this.totalNetProfit;
            },

        },

        methods: {
            validate() {

                if (! this.amount) {
                    alert('Input amount');
                    return;
                }

                if (! this.selectedPlan) {
                    alert('Select tenor');
                    return;
                }

                this.$emit('next');
            }
        }
    }

</script>