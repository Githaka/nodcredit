<template>
    <a @click="showBanModal = true" class="u-color-danger">
        <slot>Ban Account</slot>

        <admin-accounts-ban-modal
                v-if="showBanModal"
                :id="id"
                @close="showBanModal = false"
                @banned="handleBanned()"
        />
    </a>
</template>
<script>

    export default {
        props: {
            id: {
                type: String,
                required: true
            }
        },

        data() {
            return {
                showBanModal: false
            }
        },

        methods: {
            handleBanned() {
                this.showBanModal = false;
                window.location.reload();
            }
        }

    }

</script>