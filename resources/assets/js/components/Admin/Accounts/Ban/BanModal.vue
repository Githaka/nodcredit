<template>
    <modal
            class="modal-account-ban"
            @close="$emit('close')"
    >
        <h4 slot="header">Ban Account</h4>

        <div slot="body">

            <div v-if="Object.keys(account).length" class="row">
                <div class="col-md-12">
                    <div class="u-mb-small u-text-bold">Account Details</div>
                </div>
                <div class="col-md-12">
                    <div class="o-media u-mb-small">
                        <div class="o-media__body u-flex u-justify-between">
                            <p>Name:</p>
                            <span class="u-text-small">{{ account.name }}</span>
                        </div>
                    </div>
                    <div class="o-media u-mb-small">
                        <div class="o-media__body u-flex u-justify-between">
                            <p>Email:</p>
                            <span class="u-text-small">{{ account.email }}</span>
                        </div>
                    </div>
                    <div class="o-media u-mb-small">
                        <div class="o-media__body u-flex u-justify-between">
                            <p>Phone:</p>
                            <span class="u-text-small">{{ account.phone }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 u-mb-small">
                    <p>Ban reason</p>
                    <textarea v-model="reason" class="c-input"></textarea>
                </div>
            </div>

            <form-errors :errors="formErrors"></form-errors>

            <loader v-if="isSending">Please wait...</loader>
        </div>

        <div slot="footer">
            <button @click="submit()" class="c-btn c-btn--danger c-btn--large c-btn--fullwidth">Ban</button>
        </div>
    </modal>
</template>
<script>

    import API from '@/api'

    export default {

        props: {
            id: {
                type: String,
                required: true
            }
        },

        data() {
            return {
                isSending: false,
                formErrors: {},
                reason: '',
                account: {}
            }
        },

        mounted() {
            API.Admin.Accounts.getAccount(this.id).then(response => {
                this.account = response.data.account;
            });
        },

        methods: {
            submit() {

                if (this.isSending) {
                    return false;
                }

                this.isSending = true;
                this.formErrors = {};

                API.Admin.Accounts.banAccount(this.id, {reason: this.reason})
                    .then(response => {
                        this.$emit('banned');
                    })
                    .catch(error => {
                        this.formErrors = error.response.data.errors;
                    })
                    .then(() => {
                        this.isSending = false;
                    });
            }
        }

    }

</script>