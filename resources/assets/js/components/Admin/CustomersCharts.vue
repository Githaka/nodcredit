<template>
    <div class="customers-charts">

        <div class="row">
            <div class="col-12 text-center">
                <h3 v-if="isLoaded" class="u-mb-medium">
                    Customers: Responsive ({{ counters.responsive }}) vs Defaulters ({{ counters.defaulters }}):

                    <span v-if="Object.keys(responsiveDefaultersChartOptions).length" class="responsive-defaulters-counters">
                        {{ responsiveDefaultersChartOptions.series[0].data[0].y }} <span>(≤ 5%)</span>,
                        {{ responsiveDefaultersChartOptions.series[0].data[1].y }} <span>(≤ 10%)</span>,
                        {{ responsiveDefaultersChartOptions.series[0].data[2].y }} <span>(≤ 25%)</span>,
                        {{ responsiveDefaultersChartOptions.series[0].data[3].y }} <span>(≤ 50%)</span>,
                        {{ responsiveDefaultersChartOptions.series[0].data[4].y }} <span>(≤ 100%)</span>
                    </span>

                </h3>
                <h3 v-else="" class="u-mb-medium">Customers: Loading data...</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="c-card">
                    <charts v-if="Object.keys(loanAmountRequestedChartOptions).length" :options="loanAmountRequestedChartOptions"></charts>
                </div>
            </div>
            <div class="col-md-6">
                <div class="c-card">
                    <charts v-if="Object.keys(loanTypeChartOptions).length" :options="loanTypeChartOptions"></charts>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="c-card">
                    <charts v-if="Object.keys(sexChartOptions).length" :options="sexChartOptions"></charts>
                </div>
            </div>
            <div class="col-md-6">
                <div class="c-card">
                    <charts v-if="Object.keys(ageChartOptions).length" :options="ageChartOptions"></charts>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <div class="c-card">
                    <charts v-if="Object.keys(bankChartOptions).length" :options="bankChartOptions"></charts>
                </div>
            </div>
            <div class="col-6">
                <div class="c-card">
                    <charts v-if="Object.keys(responsiveDefaultersChartOptions).length" :options="responsiveDefaultersChartOptions"></charts>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import axios  from 'axios';

    export default {

        data() {
            return {
                isLoading: false,
                isLoaded: false,
                loadCount: 0,

                counters: {
                    defaulters: 0,
                    responsive: 0,
                },

                sexChartOptions: {},
                ageChartOptions: {},
                loanTypeChartOptions: {},
                bankChartOptions: {},
                loanAmountRequestedChartOptions: {},
                responsiveDefaultersChartOptions: {},
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
                this.loadCount++;

                axios
                    .get('/mainframe/customers-charts')
                    .then(response => {

                        this.sexChartOptions = response.data.sex_chart;
                        this.ageChartOptions = response.data.age_chart;
                        this.loanTypeChartOptions = response.data.loan_type_chart;
                        this.bankChartOptions = response.data.bank_chart;
                        this.loanAmountRequestedChartOptions = response.data.loan_amount_requested_chart;
                        this.responsiveDefaultersChartOptions = response.data.responsive_defaulters_chart;
                        this.counters = response.data.counters;

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
            }
        },
    }
</script>