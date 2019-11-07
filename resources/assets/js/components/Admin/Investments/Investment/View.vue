<template>

    <div class="investment-view" style="min-height: 300px">

        <div v-if="isLoaded">
            <div class="row">
                <div class="col-md-8">
                    <investment-details
                            :investment="investment"
                            @refresh="load()"
                    ></investment-details>
                </div>
                <div class="col-md-4">
                    <user-details :user="investment.user"></user-details>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <partial-liquidations
                            :liquidations="investment.partial_liquidations"
                            @refresh="load()"
                    ></partial-liquidations>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                     <profit-payments
                             :payments="investment.profit_payments"
                             @refresh="load()"
                     ></profit-payments>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <investment-logs :logs="investment.all_logs"></investment-logs>
                </div>
            </div>

        </div>

        <loader v-if="isLoading"></loader>

    </div>

</template>

<script>
    import API  from '../../../../api';

    import ProfitPayments from './ProfitPayments.vue';
    import PartialLiquidations from './PartialLiquidations.vue';
    import InvestmentDetails from './InvestmentDetails.vue';
    import UserDetails from './UserDetails.vue';
    import InvestmentLogs from './InvestmentLogs.vue';

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

                investment: {},
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

                API.Admin.Investments.get(this.id)
                    .then(response => {
                        this.investment = response.data.investment;

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
            ProfitPayments,
            InvestmentDetails,
            UserDetails,
            PartialLiquidations,
            InvestmentLogs,
        }
    }
</script>
