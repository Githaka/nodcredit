<template>
    <div class="loader">
        <div class="loader-content">
            <i class="fas fa-3x fa-circle-notch fa-spin"></i>
            <div class="loader-text"><slot>Loading...</slot></div>
        </div>
    </div>
</template>

<style lang="sass" scoped="">
.loader
    background: rgba(255,255,255,0.5)
    z-index: 1
    position: absolute
    top: 0
    bottom: 0
    left: 0
    right: 0
    text-align: center
    .loader-content
        position: absolute
        top: 50%
        left: 0
        right: 0
        transform: translateY(-50%)
    .loader-text
        margin-top: 20px
        font-weight: bold
        font-size: 18px

</style>