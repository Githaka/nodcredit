<template>
    <div class="disbursed-and-repayment">
        <div class="row">
            <div class="col-12">
                <div class="c-card">
                    <div class="row">
                        <div class="col-12 text-center u-mb-medium">
                            <h3>Loan disbursed and repayment</h3>
                        </div>
                    </div>
                    <div class="row u-mb-medium">
                        <div class="col-3">
                            <select @change="load()" v-model="filterDateType" class="c-input ">
                                <option value="custom">Custom date...</option>
                                <option value="today">Today</option>
                                <option value="last-7-days">Last 7 days</option>
                                <option value="last-14-days">Last 14 days</option>
                                <option value="last-30-days">Last 30 days</option>
                            </select>
                        </div>
                        <div class="col-3">
                            <input v-if="filterDateType === 'custom'" @change="load()" type="date" v-model="filterCustomDate" class="c-input">
                        </div>
                    </div>
                    <div class="col-12">
                        <charts v-if="Object.keys(chartOptions).length" :options="chartOptions"></charts>
                    </div>
                    <loader v-if="isLoading">Loading data...</loader>
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

                filterDateType: 'last-7-days',
                filterCustomDate: '',

                chartOptions: {},
            }
        },

        mounted() {
            this.load();
        },

        methods: {

            canFilter() {
                if (this.filterDateType === 'custom' && !this.filterCustomDate) {
                    return false;
                }

                return true;
            },

            load() {

                if (this.isLoading || !this.canFilter()) {
                    return false;
                }

                this.isLoading = true;
                this.loadCount++;

                let data = {
                    filter_date_type: this.filterDateType,
                    filter_custom_date: this.filterCustomDate,
                };

                axios
                    .post('/mainframe/disbursed-and-repayment-chart', data)
                    .then(response => {

                        this.chartOptions = response.data.chart;

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
