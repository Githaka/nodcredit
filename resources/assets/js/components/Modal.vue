<template>
    <div class="v-modal">
        <transition name="modal">
            <div class="v-modal-mask">
                <div class="v-modal-wrapper">
                    <div class="v-modal-container">

                        <button @click="close()" type="button" class="btn-close">
                            <span>&times;</span>
                        </button>

                        <div class="v-modal-header">
                            <slot name="header"></slot>
                        </div>

                        <div class="v-modal-body">
                            <slot name="body"></slot>
                        </div>

                        <div class="v-modal-footer">
                            <slot name="footer">
                                <button class="c-btn c-btn--primary" @click="close()">Close</button>
                            </slot>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>
<script>

    export default {

        created() {
            window.document.getElementsByTagName('body')[0].classList.add('v-modal-opened');
        },

        destroyed() {
            let body = window.document.getElementsByTagName('body')[0];
            let classList = body.classList;

            classList.remove('v-modal-opened');

            if (this.$el.parentNode) {
                this.$el.parentNode.removeChild(this.$el);
            }
        },

        mounted() {
            this.$root.$el.append(this.$el);
        },

        methods: {
            close() {
                this.$emit('close');
            }
        }
    }

</script>