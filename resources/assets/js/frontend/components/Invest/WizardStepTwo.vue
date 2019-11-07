<template>
    <div class="getStarted ">
        <div class="g-left mt-5 mb-5">
            <a @click="$emit('back')" class="mb-4">
                <img src="/frontend/images/arrow-up.svg" alt="Go back">
            </a>

            <div class="animated  slideInDown slow ">
                <h6>Before we start</h6>
                <h3 class="text-left">Do you already have a NodCredit account?</h3>
                <div class="d-block">
                    <div class="d-inline mr-3 register"><a @click="$emit('next')" class="btn-outline-md largeButton">No, I don't</a></div>
                    <div class="d-inline"><a href="/v2/auth/login" class="btn-outline-md largeButton">Yes, Log me in</a>
                    </div>
                </div>
            </div>

        </div>
        <div class="g-Right mt-5 mb-5"></div>
    </div>
</template>