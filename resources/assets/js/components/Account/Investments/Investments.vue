<template>

    <div class="row u-mt-large" v-if="isLoaded">

        <div class="col-md-5">
            <div class="c-card">
                <h3 class="c-text--subtitle">Current Investment</h3>
                <div v-if="Object.keys(lastInvestment).length" class="row u-pt-small">
                    <div class="col">
                        <h1>{{ lastInvestment.amount.formatted }}</h1>
                    </div>
                    <div class="col border-left-1 align-self-center">
                        <p v-if="lastInvestment.is_started">Started at {{ lastInvestment.started_at }}</p>
                        <p v-else="">Not Started</p>
                    </div>
                </div>
                <div v-else="" class="row u-pt-small">
                    <div class="col"><h1>0</h1></div>
                </div>
                <button class="c-btn c-btn--info u-mt-medium" data-toggle="modal" data-target="#invest-modal">
                    Invest <i class="feather icon-chevron-up"></i>
                </button>
            </div>

            <div class="row u-mt-medium">

                <div class="col-md-6 col-sm-12">
                    <div class="c-card">
                        <p class="c-text--subtitle">Total Investment</p>
                        <h3>{{ investmentsAmount.formatted }}</h3>
                    </div>
                </div>

                <div class="col-md-6 col-sm-12">
                    <div class="c-card">
                        <p class="c-text--subtitle">Investments</p>
                        <h3>{{ investmentsCount }}</h3>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-md-7">
            <div class="c-card">
                <h4>Investment History</h4>
                <p style="margin-bottom:30px;">You can liquidate your investment at any point. Liquidation charge is {{ liquidationPenalty }}% of interest earned as at pre-liquidation.</p>

                <div v-for="investment in investments" class="o-line u-pb-small u-mb-small u-border-bottom">
                    <div class="o-media">
                        <div class="o-media__body">
                            <h6>{{ investment.amount.formatted }}</h6>

                            <p v-if="investment.is_started">
                                {{ investment.started_at_days_ago }} days ago<br />

<!--
                                <strong v-if="investment.is_ended || investment.is_liquidated" style="color: green;">Profit: {{ investment.profit.formatted }}</strong>
                                <strong v-else="" style="color: green;">Profit: {{ investment.current_profit.formatted }}</strong>
-->
                            </p>

                            <strong v-if="investment.paid_out_at" style="color: green;">Paid Out on: {{ investment.paid_out_at }}</strong>
                        </div>
                    </div>

                    <div style="text-align:right;">

                        <div v-if="! investment.is_ended && ! investment.is_liquidated" class="u-mb-small">
                            <investment-liquidation :id="investment.id"></investment-liquidation>
                        </div>

                        <span v-if="investment.is_liquidated"
                              class="c-badge c-badge--small c-badge--danger c-tooltip c-tooltip--bottom"
                              :aria-label="`Liquidated on ${investment.liquidated_at}`"
                        >
                            Liquidated
                        </span>
                        <span v-else-if="investment.is_ended"
                              class="c-badge c-badge--small c-badge--success c-tooltip c-tooltip--bottom"
                              :aria-label="`Ended on ${investment.ended_at}`"
                        >
                            Ended
                        </span>
                        <span v-else-if="investment.is_started"
                              class="c-badge c-badge--small c-badge--info c-tooltip c-tooltip--bottom"
                              :aria-label="`Started on ${investment.started_at}`"
                        >
                            Started
                        </span>
                        <span v-else=""
                              class="c-badge c-badge--small c-badge--warning c-tooltip c-tooltip--bottom"
                              :aria-label="`Invested on ${investment.created_at}`"
                        >
                            Not Started
                        </span>

                    </div>
                </div>

                <p v-if="! investments.length">No investments</p>

            </div>
        </div>

    </div>

</template>

<script>
    import axios  from 'axios';
    import InvestmentLiquidation from './Liquidation.vue'

    export default {

        data() {
            return {
                isLoading: false,
                isLoaded: false,
                isSending: false,

                investments: [],
                investmentsCount: null,
                investmentsAmount: 0,
                latestInvestment: {},
                liquidationPenalty: ''
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
                    .get(`/account/investments`)
                    .then(response => {
                        this.investments = response.data.investments;
                        this.lastInvestment = response.data.investments.length ? response.data.investments[0] : {};
                        this.investmentsCount = response.data.investments_count;
                        this.investmentsAmount = response.data.investments_amount;
                        this.liquidationPenalty = response.data.liquidation_penalty;
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

        },

        components: {
            InvestmentLiquidation
        }
    }
</script>
