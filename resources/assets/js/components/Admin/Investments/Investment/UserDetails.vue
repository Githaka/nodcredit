<template>

    <div class="c-card">
        <h4 class="u-mb-small">Customer Details</h4>
        <div class="o-media u-mb-small">
            <div class="o-media__body u-flex u-justify-between">
                <p>Name:</p>
                <span class="u-text-small">{{ user.name }}</span>
            </div>
        </div>
        <div class="o-media u-mb-small">
            <div class="o-media__body u-flex u-justify-between">
                <p>Email:</p>
                <span class="u-text-small">{{ user.email }}</span>
            </div>
        </div>
        <div class="o-media u-mb-small">
            <div class="o-media__body u-flex u-justify-between">
                <p>Phone:</p>
                <span class="u-text-small">{{ user.phone }}</span>
            </div>
        </div>
    </div>

</template>

<script>
    export default {

        props: {
            user: {
                type: Object,
                required: true
            }
        },
    }
</script>
