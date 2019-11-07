<template>

    <div class="investment-liquidation" style="display: inline-block;">

        <button @click="showModal()" class="c-btn c-btn--info c-btn--small">Liquidate</button>

        <modal
                v-if="showLiquidationModal"
                class="modal-investment-liquidation"
                @close="closeModal()"
        >
            <h4 slot="header">Investment Liquidation</h4>

            <div slot="body" v-if="isLoaded">
                <h5 class="u-mb-small">Investment Details</h5>
                <div class="row">
                    <div class="col-5">
                        <div class="o-media u-mb-small">
                            <div class="o-media__body u-flex u-justify-between">
                                <p>Amount:</p>
                                <span class="u-text-small">{{ investment.amount.formatted }}</span>
                            </div>
                        </div>
                        <div class="o-media u-mb-small">
                            <div class="o-media__body u-flex u-justify-between">
                                <p>Tenor:</p>
                                <span class="u-text-small">{{ investment.plan_name }}</span>
                            </div>
                        </div>
                        <div class="o-media u-mb-small">
                            <div class="o-media__body u-flex u-justify-between">
                                <p>Percentage:</p>
                                <span class="u-text-small">{{ investment.plan_percentage }}%</span>
                            </div>
                        </div>

                    </div>
                    <div class="col-2"></div>
                    <div class="col-5">
                        <div class="o-media u-mb-small">
                            <div class="o-media__body u-flex u-justify-between">
                                <p>Started:</p>
                                <span v-if="investment.is_started" class="u-text-small">{{ investment.started_at }}</span>
                                <span v-else="" class="u-text-small">No</span>
                            </div>
                        </div>
                        <div class="o-media u-mb-small">
                            <div class="o-media__body u-flex u-justify-between">
                                <p>Ended:</p>
                                <span v-if="investment.is_ended" class="u-text-small">{{ investment.ended_at }}</span>
                                <span v-else="" class="u-text-small">No</span>
                            </div>
                        </div>
<!--
                        <div class="o-media u-mb-small">
                            <div class="o-media__body u-flex u-justify-between">
                                <p>Profit:</p>
                                <span v-if="investment.is_ended || investment.is_liquidated" class="u-text-small">{{ investment.profit.formatted }}</span>
                                <span v-else="" class="u-text-small">{{ investment.current_profit.formatted }}</span>
                            </div>
                        </div>
-->
                    </div>
                </div>

                <h5 class="u-mb-small">Liquidation</h5>
                <p class="u-mb-small">Liquidation charge is {{ liquidationPenalty }}% of interest earned as at pre-liquidation. <br>You can make a partial liquidation.</p>

                <div class="u-mb-small">
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
                        <input v-model="amount" type="number" step="100" class="form-control c-input" :max="investment.amount.value" min="100">
                    </div>

                </div>

                <div class="u-mb-small">
                    <label class="c-field c-field__label">Type your reason here</label>
                    <textarea class="form-control c-input" v-model="reason"></textarea>
                </div>

                <form-errors :errors="formErrors"></form-errors>

                <loader v-if="isSending">Please wait...</loader>
                <loader v-if="isLoading"></loader>
            </div>

            <div slot="footer">
                <button @click="submit()" class="c-btn c-btn--info">Liquidate</button>
                <button @click="closeModal()" class="c-btn c-btn--primary">Cancel</button>
            </div>
        </modal>

    </div>

</template>

<script>
    import axios  from 'axios';

    export default {

        props: {
            id: {
                required: true,
                type: String
            }
        },

        data() {
            return {
                isLoading: false,
                isLoaded: false,
                isSending: false,

                showLiquidationModal: false,

                investment: {},
                liquidationPenalty: '',

                presets: [],

                reason: '',
                amount: 0,
                formErrors: {}
            }
        },

        methods: {

            closeModal() {
                this.showLiquidationModal = false;
                this.formErrors = {};
            },

            showModal() {
                this.load();
                this.showLiquidationModal = true;
            },


            load() {

                if (this.isLoading) {
                    return false;
                }

                this.isLoading = true;

                axios
                    .get(`/account/investments/${this.id}`)
                    .then(response => {
                        this.investment = response.data.investment;
                        this.liquidationPenalty = response.data.liquidation_penalty;

                        this.generateAmounts(this.investment.amount.value);

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

            generateAmounts(amount) {

                const value25 = parseInt(amount * 0.25);
                const value50 = parseInt(amount * 0.50);
                const value75 = parseInt(amount * 0.75);
                const value100 = amount;

                this.amount = value25;

                this.amounts = [
                    {value: value25, name: `${value25} (25%)`},
                    {value: value50, name: `${value50} (50%)`},
                    {value: value75, name: `${value75} (75%)`},
                    {value: value100, name: `${value100} (100%)`},
                ];
            },

            submit() {

                if (this.isSending) {
                    return false;
                }

                this.isSending = true;
                this.formErrors = {};

                let data = {
                    amount: this.amount,
                    reason: this.reason,
                };

                axios
                    .post(`/account/investments/${this.id}/liquidate`, data)
                    .then(response => {
                        this.amount = 0;
                        this.reason = '';

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

        },
    }
</script>
