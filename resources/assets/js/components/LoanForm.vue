<template>
    <div>
        <form class="nod-form" v-show="activeStep == '1'">
            <h5 class="blue-text"><b>Get small loans starting from {{loanSetting.min | currency}} and Access Personal Loans of Upto {{loanSetting.max | currency}}.</b></h5>
            <div class="form-group naira-include">
                <input type="text" v-model="form.loanAmount" class="form-control" :placeholder="loanInfoFromTo" />
            </div>
            <div class="form-group">
                <select class="form-control" id="sel1" v-model="form.loanType">
                    <option selected disabled>What is the loan for?</option>
                    <option v-for="(item, index) in loanTypes" :value="item.id">{{item.name}}</option>
                </select>
            </div>
            <div class="form-group">
                <button type="button" class="btn blue-bkg" @click="setActiveStep(2);">Get a Loan</button>
                <!-- <button class="btn neutralz">Check your Rate</button> -->
            </div>
        </form>

        <form class="nod-form has-steps" v-show="activeStep == '2'">

            <h3>Verification</h3>
            <div class="form-group">
                <input type="text" v-model="form.bvn" class="form-control" placeholder="Enter your BVN" />
            </div>

            <div class="form-group">
                <input type="text" v-model="form.phone" class="form-control" placeholder="Your phone number" />
            </div>

            <div class="form-group">
                <input type="text" v-model="form.email" class="form-control" placeholder="Enter your email address" />
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" v-model="form.agree">
                    I agree to the <a target="_blank" href="/term-conditions">Terms and Conditions</a>
                </label>
            </div>

            <div class="form-group">
                <button type="button" class="btn blue-bkg" @click="processFormData" :disabled="processingForm">{{processingForm ? 'Processing..' : 'Next'}}</button>
            </div>
        </form>




    </div>

</template>

<script>




    function isNumber(n) {
        return !isNaN(parseFloat(n)) && isFinite(n);
    }

    import axios from 'axios';

    export default {
        name: 'LoanForm',
        data() {
            return {
                steps: [
                    {
                        name: '1',
                        beforeFunc: this.checkStep1
                    },
                    {
                        name: '2',
                        beforeFunc: this.checkStep2
                    },
                    {
                        name: '3',
                        beforeFunc: this.processFormData
                    },
                ],
                activeStep: '1',
                processingForm: false,
                loanTypes: [],
                loanTypeInitValue: 'What is the loan for?',
                form: {
                    fullName: '',
                    email: '',
                    bvn: '',
                    agree: false,
                },
                loanSetting: {}
            }
        },

        mounted() {
            this.loadLoanType();
            this.form.loanType = this.loanTypeInitValue;
        },

        methods: {

            loadLoanType: function() {
                var vm = this;
                vm.showLoading = true;
                axios.get('/api/commons/loanTypes')
                        .then(function (response) {
                            vm.loanTypes = response.data.data.loanTypes;
                            vm.loanSetting  = response.data.data.loanSetting;

                            if(response.data.loanInfo) {
                                vm.form.loanType =  response.data.loanInfo.loanType;
                                vm.form.loanAmount =  response.data.loanInfo.loanAmount;
                            }
                        })
                        .catch(function (error) {
                            alert(error);
                        });
            },
            getCurrentStep: function() {
                let self = this;
                let out = undefined;
                this.steps.forEach(function(item){
                   if(item.name == self.activeStep){
                       out = item;
                   }
                });
                return out;
            },
            setActiveStep: function(step) {
                let self = this;
                let currentStep = self.getCurrentStep();
                if(_.has(currentStep, 'beforeFunc')) {
                    if(!currentStep.beforeFunc()) {
                        return;
                    }
                }

                this.steps.forEach(function(item){
                    if(item.name == step) {
                       self.activeStep = item.name;
                        return;
                    }
                });
            },
            processStep1: function() {
                return true;
            },

            checkStep1: function() {
                let cleanData = parseInt(this.form.loanAmount.replace(/,/g , ""));
                if(!isNumber(cleanData)) {
                    alert('Please enter at least ' + this.$options.filters.currency(this.loanSetting.min));
                    return false;
                }
                if(cleanData < this.loanSetting.min) {
                        alert('Minimum loan is ' + this.$options.filters.currency(this.loanSetting.min));
                        return false;
                }

                if(cleanData > this.loanSetting.max) {
                    alert('Maximum loan is ' + this.$options.filters.currency(this.loanSetting.max));
                    return false;
                }

                if(this.form.loanType.length <= 0 || this.form.loanType == this.loanTypeInitValue) {
                    alert('Please select a loan type');return;
                }
                return true;
            },

            checkStep2: function() {

                var vm = this;

                if(this.form.bvn.length  != 10) {
                    alert('Please provide a valid BVN');
                    return;
                }

                return true;
            },

            validateBVN: function() {
                return true;
            },

            processFormData: function() {
                var vm = this;
                vm.processingForm = true;
                axios.post('/account/me/full-loan-application', this.form).then(function(data){
                    if(data.data.status == 'ok') {
                       document.location = '/verify-mobile';
                    } else {
                        alert("ERROR: " + data.data.message);
                    }
                    vm.processingForm = false;
                }).catch(function(error){
                    vm.processingForm = false;
                    alert('error here ' + error);
                });
                return false;
            }
        },

        computed: {
            loanInfoFromTo: function () {
                return "From " + this.$options.filters.currency(this.loanSetting.min) + " to " + this.$options.filters.currency(this.loanSetting.max);
            }
        }
    }
</script>
