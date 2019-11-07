<template>
    <div class="loan-wizard">

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
                :loan-types="loanTypes"
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
                loanTypes: []
            }
        },

        mounted() {
            this.init();
        },

        methods: {

            init() {
                API.loanInfo()
                    .then(response => {
                        this.loanTypes = response.data.loanTypes;
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
