<template>

    <div class="c-card">
        <h4 class="u-mb-small">Investment Details</h4>

        <div v-if="investment.is_changeable" class="u-mb-small">
                <investment-edit :id="investment.id" @changed="$emit('refresh')"></investment-edit>
        </div>

        <div class="row">
            <div class="col-5">
                <div class="o-media u-mb-small">
                    <div class="o-media__body u-flex u-justify-between">
                        <p>Status: </p>
                        <span class="u-text-small">
                            <status-badge :status="investment.status" />
                        </span>
                    </div>
                </div>
                <div class="o-media u-mb-small">
                    <div class="o-media__body u-flex u-justify-between">
                        <p>Amount:</p>
                        <span class="u-text-small">{{ investment.amount.formatted }}</span>
                    </div>
                </div>
                <div class="o-media u-mb-small">
                    <div class="o-media__body u-flex u-justify-between">
                        <p>Name:</p>
                        <span class="u-text-small">{{ investment.plan_name }}</span>
                    </div>
                </div>
                <div class="o-media u-mb-small">
                    <div class="o-media__body u-flex u-justify-between">
                        <p>Tenor:</p>
                        <span class="u-text-small">{{ investment.plan_days }} days</span>
                    </div>
                </div>
                <div class="o-media u-mb-small">
                    <div class="o-media__body u-flex u-justify-between">
                        <p>Percentage:</p>
                        <span class="u-text-small">{{ investment.plan_percentage }}%</span>
                    </div>
                </div>
                <div class="o-media u-mb-small">
                    <div class="o-media__body u-flex u-justify-between">
                        <p>Profit payout:</p>
                        <span v-if="investment.is_profit_payout_type_single" class="u-text-small">In the end</span>
                        <span v-else-if="investment.is_profit_payout_type_monthly" class="u-text-small">Monthly</span>
                    </div>
                </div>
                <div class="o-media">
                    <div class="o-media__body u-flex u-justify-between">
                        <p>Withholding Tax:</p>
                        <span class="u-text-small">{{ investment.withholding_tax_percent }}%</span>
                    </div>
                </div>
                <div class="u-mb-small" style="text-align: right;">
                    <investment-withholding-tax-edit
                            :id="investment.id"
                            :key="investment.id"
                            @changed="$emit('refresh')"
                    />
                </div>
            </div>

            <div class="col-2"></div>

            <div class="col-5">
                <div class="o-media u-mb-small">
                    <div class="o-media__body u-flex u-justify-between">
                        <p>Created:</p>
                        <span class="u-text-small">{{ investment.created_at }}</span>
                    </div>
                </div>
                <div class="o-media">
                    <div class="o-media__body u-flex u-justify-between">
                        <p>Started:</p>

                        <span v-if="investment.is_started" class="u-text-small">{{ investment.started_at }}</span>
                        <span v-else="" class="u-text-small">No</span>

                    </div>
                </div>

                <div class="u-mb-small">
                    <start-date-edit
                            v-if="investment.is_changeable"
                            :id="investment.id"
                            :is-started="investment.is_started"
                            @changed="startDateChanged()"
                            style="text-align: right;"
                    />
                </div>

                <div class="o-media u-mb-small">
                    <div class="o-media__body u-flex u-justify-between">
                        <p>Ended:</p>
                        <span v-if="investment.is_ended" class="u-text-small">{{ investment.ended_at }}</span>
                        <span v-else="" class="u-text-small">No</span>
                    </div>
                </div>
                <div class="o-media u-mb-small">
                    <div class="o-media__body u-flex u-justify-between">
                        <p>Liquidated:</p>
                        <span v-if="investment.is_liquidated" class="u-text-small">{{ investment.liquidated_at }}</span>
                        <span v-else="" class="u-text-small">No</span>
                    </div>
                </div>

                <div v-if="investment.is_started" class="o-media u-mb-small">
                    <div class="o-media__body u-flex u-justify-between">
                        <p>Maturity date:</p>
                        <span  class="u-text-small">{{ investment.maturity_date }}</span>
                    </div>
                </div>

            </div>
        </div>
    </div>

</template>

<script>

    import InvestmentEdit from './InvestmentEdit.vue';
    import StartDateEdit from './StartDateEdit.vue';
    import InvestmentWithholdingTaxEdit from './InvestmentWithholdingTaxEdit.vue';

    export default {

        props: {
            investment: {
                type: Object,
                required: true
            }
        },

        components: {
            InvestmentEdit,
            StartDateEdit,
            InvestmentWithholdingTaxEdit
        },

        methods: {
            startDateChanged() {
                this.$emit('refresh');
            }
        }
    }
</script>
