<template>

    <div class="admin-accounts-investor-add" style="display: inline-block;">

        <button @click="showModal()" class="c-btn c-btn--info c-btn--small">+ Add Investor</button>

        <modal
                v-if="showAddModal"
                class="modal-investor-add"
                @close="closeModal()"
        >
            <h4 slot="header">Add Investor</h4>

            <div slot="body">
                <div class="row">
                    <div class="col-md-12 u-mb-medium">
                        <p>Name</p>
                        <input v-model="data.name" type="text" class="c-input">
                    </div>
                    <div class="col-md-12 u-mb-medium">
                        <p>Email Address</p>
                        <input v-model="data.email" type="text" class="c-input">
                    </div>
                    <div class="col-md-12 u-mb-medium">
                        <p>Phone number</p>
                        <input v-model="data.phone" type="text" class="c-input">
                    </div>
                    <div class="col-md-12 u-mb-medium">
                        <p>Password</p>
                        <input v-model="data.password" type="password" class="c-input">
                    </div>
                    <div class="col-md-12 u-mb-medium">
                        <p>Password confirmation</p>
                        <input v-model="data.password_confirmation" type="password" class="c-input">
                    </div>
                </div>

                <form-errors :errors="formErrors"></form-errors>

                <loader v-if="isSending">Please wait...</loader>
                <loader v-if="isLoading"></loader>
            </div>

            <div slot="footer">
                <button @click="submit()" class="c-btn btn-primary c-btn--large c-btn--fullwidth">Add Investor</button>
            </div>
        </modal>

    </div>

</template>

<script>
    import axios  from 'axios';

    export default {

        data() {
            return {
                isLoading: false,
                isLoaded: false,
                isSending: false,

                showAddModal: false,

                data: {
                    name: '',
                    email: '',
                    phone: '',
                    password: '',
                    password_confirmation: '',
                },

                formErrors: {}
            }
        },

        methods: {

            closeModal() {
                this.showAddModal = false;
                this.formErrors = {};
            },

            showModal() {
                this.showAddModal = true;
            },

            resetForm() {
                this.data = {
                    name: '',
                    email: '',
                    phone: '',
                    password: '',
                    password_confirmation: '',
                };
            },

            submit() {

                if (this.isSending) {
                    return false;
                }

                this.isSending = true;
                this.formErrors = {};

                axios
                    .post(`/mainframe/accounts/investor/add`, this.data)
                    .then(response => {
                        this.resetForm();
                        this.closeModal();
                        this.$emit('added');

                        window.location.reload();
                    })
                    .catch(error => {
                        this.formErrors = error.response.data.errors;
                    })
                    .then(() => {
                        this.isSending = false;
                    })
                ;
            },

        },
    }
</script>
