<template>
    <div class="" data-mh="dashboard3-cards">
        <h4>Apply for Loan</h4>
        <p class="u-mb-medium">Complete the form below to apply for a new loan.</p>

        <form action="" method="post" v-on:submit.prevent="submitForm">
            <div class="row">
                <div class="col-xl-6">
                    <div class="c-card">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="c-field u-mb-medium">
                                <label class="c-field__label" for="amount">How much loan do you want? </label>
                                <input class="c-input" name="amount" value="" type="text" id="amount" 
                                    v-model="form.amount" v-on:keyup="handAmountInput">
                                <small class="c-input-info">(Min: {{initState.loanMin | currency}} Max: {{initState.loanMax | currency}})</small>
                                <small class="c-field__message u-color-danger" v-if="errors.amount"> <i class="fa fa-times-circle"></i>{{errors.amount[0]}}</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="c-field u-mb-medium">
                                <label class="c-field__label" for="loanType">What is the loan for?</label>
                                <div class="c-select">
                                    <select class="c-select__input" name="loanType" id="loanType" v-model="form.loanType">
                                        <option>Select a loan type</option>
                                        <option v-for="loanType in initState.loanTypes" v-bind:value="loanType.id"
                                            :selected="form.loanType == 'Select a loan type'">{{loanType.name}}</option>
                                    </select>
                                    <small class="c-field__message u-color-danger" v-if="errors.loanType"> <i class="fa fa-times-circle"></i>{{errors.loanType[0]}}</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="c-field u-mb-medium" v-show="rangeInfo">
                                <label class="c-field__label" for="loanType">Select loan tenor</label>
                                <div class="c-select">
                                    <select class="c-select__input" name="tenor" id="tenor" v-model="form.tenor"
                                        v-on:change="handAmountInput">
                                        <option>Select a loan tenor</option>
                                        <option v-for="mm in rangeInfo" v-bind:value="mm" :selected="form.tenor == '1'">{{mm}}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>

                </div>

                <div class="col-xl-6">
                    <div class="c-card">
                    <!-- <h4 style="margin-bottom:0px;">Loan Re-Payment Plans</h4> -->
                    <!-- <p style="margin-bottom:20px;">You can change your monthly re-payment plan here.</p> -->
                    <div class="row">
                        <div class="col-md-6" v-for="(pmonth, i) in monthlyPayments">
                            <div class="c-field u-mb-medium">
                                <label class="c-field__label" for="amount">Month {{pmonth.month}} - you can change value</label>
                                <input type="text" name="" id="" :value="pmonth.amount | currency" class="c-input"
                                    v-on:keyup="onMonthlyAmountInputChanged($event, pmonth)">
                            </div>
                        </div>
                    </div>
                    </div>
                </div>

            </div>

            <div class="row" v-show="doneWithRequest">
                <div class="c-field u-mb-medium col-xl-6">
                    <button class="c-btn c-btn--info c-btn--fullwidth" type="button" @click="submitForm">Apply</button>
                </div>
            </div>
        </form>


    </div>
</template>


<script>
    import axios from 'axios';

    export default {

        name: 'ApplyLoan',
        data() {
            return {
                items: [],
                form: {
                    loanType: 'Select a loan type',
                    tenor: 1
                },
                initState: {},
                monthlyPayments: [],
                selectedMonthPayment: {},
                timeout: null,
                rangeInfo: null,
                errors: {},
                doneWithRequest: null,
                amountError: ''
            }
        },
        methods: {
            initLoanForm() {
                var vm = this;
                axios.get('/account/me/apply/init').then(function (res) {
                    vm.initState = res.data;
                }).catch(function (error) {
                    alert('Error: ' + error);
                });
            },

            onMonthlyAmountInputChanged(event, item) {
                clearTimeout(this.timeout);
                var vm = this;
                vm.doneWithRequest = null;

                this.timeout = setTimeout(function () {

                    var newValue = parseFloat(event.target.value.replace('NGN ', '').replace(',', ''));
                    if (newValue) {
                        item.amount = vm.form.amount;
                        item.newValue = newValue;
                        axios.post('/account/me/apply/init-recalculate', item).then(function (res) {
                            if (res.data.status == 'error') {
                                alert(res.data.message);
                                vm.monthlyPayments = res.data.payments;
                            } else {
                                vm.monthlyPayments = res.data.payments;
                                vm.doneWithRequest = true;
                            }
                        }).catch(function (error) {
                            alert('Error: ' + error);
                        });

                    }

                }, 2000);
            },

            submitForm(e) {
                var vm = this;
                e.preventDefault();
                axios.post('/account/me/apply/create', this.form).then(function (res) {
                    var resData = res.data;
                    if (resData.status == 'error') {
                        vm.errors = resData.errors;
                    } else {
                        document.location = resData.goto;
                    }
                }).catch(function (error) {
                    alert('Error: ' + error);
                });
            },

            handAmountInput(e) {
                var vm = this;

                if (this.validateAmount() == true) {
                    // resubmit to api
                    vm.doneWithRequest = null;
                    this.form.sess = vm.initState.sess;
                    axios.post('/account/me/apply/init', this.form).then(function (res) {
                        if (res.data.status !== 'error') {
                            vm.monthlyPayments = res.data.payments;
                            vm.rangeInfo = res.data.range_info;
                            vm.form.amount = res.data.amount;
                            vm.doneWithRequest = true;
                        } else {
                            console.log(res.data.message);
                        }
                    }).catch(function (error) {
                        alert(error);
                    });
                }
            },

            validateAmount() {

                var amount = parseInt(this.form.amount.replace(/,/g, ""));
                if (isNaN(amount)) {
                    return false;
                }
                if (amount < this.initState.loanMin || amount > this.initState.loanMax) {
                    return false;
                }

                return true;
            }
        },
        mounted() {
            this.initLoanForm()
        }
    }
</script>
