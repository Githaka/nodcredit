<template>
    <div
            class="status-badge c-badge"
            :class="'c-badge--' + style + ' c-badge--' + size"
            :title="tooltip"
    >
        <slot>{{ status }}</slot>
    </div>
</template>

<script>
    export default  {
        props: {
            status: {
                type: String,
                required: true
            },

            title: {
                type: String,
                required: false
            },

            size: {
                type: String,
                required: false,
                default: 'small'
            }
        },

        computed: {
            style() {
                if (this.status === 'new') {
                    return 'info';
                }

                if (this.status === 'active') {
                    return 'info';
                }

                if (this.status === 'paid') {
                    return 'success';
                }

                if (this.status === 'scheduled') {
                    return 'warning';
                }

                if (this.status === 'ended') {
                    return 'success';
                }

                if (this.status === 'liquidated') {
                    return 'danger';
                }
            },

            tooltip() {
                return (this.title || this.status);
            }
        }
    }
</script>
