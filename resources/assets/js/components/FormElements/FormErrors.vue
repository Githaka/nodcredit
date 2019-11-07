<template>
    <div class="form-errors">
        <div class="error" v-for="error in errors">
            {{ error[0] }}
        </div>
    </div>
</template>

<script>
    export default  {
        name: 'FormErrors',

        props: {
            errors: {
                type: Object,
                default: function () {
                    return {};
                }
            }
        }

    }
</script>
<style scoped="" lang="sass">
.form-errors
    .error
        color: red
        font-size: 14px

</style>
