<template>
    <div class="invest-wizard">

        <wizard-step-one
                v-if="currentStep === 1"
                @next="currentStep = 2"
        />
        <wizard-step-two
                v-else-if="currentStep === 2"
                @back="currentStep = 1"
                @next="currentStep = 3"
        />
        <wizard-step-three
                v-else-if="currentStep === 3"
                :plans="plans"
                :min-amount="minAmount"
                :max-amount="maxAmount"
                @back="currentStep = 2"
                @next="currentStep = 4"
        />
        <wizard-step-four
                v-else-if="currentStep === 4"
                @back="currentStep = 3"

        />


    </div>

</template>

<script>
    import WizardStepOne from './WizardStepOne.vue'
    import WizardStepTwo from './WizardStepTwo.vue'
    import WizardStepThree from './WizardStepThree.vue'
    import WizardStepFour from './WizardStepFour.vue'
    import API from '@/frontend/api'

    export default {
        data() {
            return {
                currentStep: 1,
                plans: [],
                minAmount: 0,
                maxAmount: 0,
            }
        },

        mounted() {
            this.init();
        },

        methods: {

            init() {
                API.investInfo()
                    .then(response => {
                        this.plans = response.data.plans;
                        this.minAmount = response.data.min_amount;
                        this.maxAmount = response.data.max_amount;
                    })
            },

        },

        components: {
            WizardStepOne,
            WizardStepTwo,
            WizardStepThree,
            WizardStepFour
        }
    }
</script>
