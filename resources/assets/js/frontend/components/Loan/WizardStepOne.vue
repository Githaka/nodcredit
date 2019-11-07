<template>
    <div class="getStarted animated  slideInDown slow ">
        <div class="g-left">
            <h3 class="text-left">Lets get you your loan</h3>
            <p class="text-left mb-5">Answer a few simple questions and we'll get your loan approved in 5 minutes.</p>
            <a @click="$emit('next')" class="btn-fill-md largeButton">Get Started</a>
        </div>
        <div class="g-Right">
            <img src="/frontend/images/loan.svg" alt="Nod Credit Hero image">
        </div>
    </div>

</template>