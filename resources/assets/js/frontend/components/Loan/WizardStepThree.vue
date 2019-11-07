<template>
    <div class="getStarted">
        <div class="g-left">
            <a @click="$emit('back')" class="mb-4">
                <img src="/frontend/images/arrow-up.svg" alt="Go back">
            </a>

            <div class="animated slideInDown">
                <h6>Okay, Let's get started</h6>
                <h3 class="text-left">Tell us about your loan request</h3>
                <div class="row mt-4">
                    <div class="col-sm-9">
                        <div class="md-form">
                            <input v-model="amount" type="number" id="loan-wizard-amount" class="form-control">
                            <label for="loan-wizard-amount">How much loan do you want?</label>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="md-form">
                                    <select v-model="loanType" class="browser-default custom-select">
                                        <option value="" disabled="" selected="">Purpose of loan</option>
                                        <option v-for="type in loanTypes" :value="type.id">{{ type.name }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <button @click="validate()" class="btn-outline-md largeButton-2 mt-3">Continue</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="g-Right">
            <div class="gcontent animated slideInRight">
                <h6><span><img src="/frontend/images/help.svg" alt=""></span> Why do you need this?</h6>
                <p>Mainly, we use this information to create a loan application for you. So that when you gain access to you dashboard, you don't have to start the application process again</p>
            </div>
        </div>
    </div>
</template>

<script>

    export default {
        props: {
            loanTypes: {
                type: Array
            }
        },

        data() {
            return {
                amount: '',
                loanType: '',
            }
        },

        methods: {
            validate() {

                if (! this.amount) {
                    alert('Input loan amount');
                    return;
                }

                if (! this.loanType) {
                    alert('Select loan type');
                    return;
                }

                this.$emit('next');
            }
        }
    }

</script>