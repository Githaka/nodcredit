<template>
    <div class="c-card">

        <loader v-if="isLoading">Loading...</loader>

        <h4 class="u-mb-medium">
            Part Payments
            <button v-if="canAdd && isLoaded && payment.status === 'scheduled'" class="c-btn c-btn--small u-ml-small" @click="showAddModal = true">Add</button>
        </h4>

        <table v-if="parts.length > 0" class="c-table">
            <thead class="c-table__head">
                <tr class="c-table__row">
                    <th class="c-table__cell c-table__cell--head">Date</th>
                    <th class="c-table__cell c-table__cell--head">Amount, NGN</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="part in parts" class="c-table__row">
                    <td class="c-table__cell">{{ part.created_at }}</td>
                    <td class="c-table__cell">{{ part.amount }}</td>
                </tr>
            </tbody>
        </table>
        <div v-else="">
            <p>No records</p>
        </div>

        <modal
                v-if="canAdd && showAddModal"
                @close="showAddModal = false"
        >
            <h5 slot="header">Add part payment</h5>

            <div slot="body">
                <div class="c-field u-mb-small">
                    <label class="c-field__label">Amount, NGN</label>
                    <input v-model="amount" type="number" class="c-input" min="1">
                </div>
                <form-errors :errors="addFormErrors"></form-errors>
                <loader v-if="isSending">Please wait...</loader>
            </div>

            <div slot="footer">
                <button @click="addPaymentPart()" class="c-btn c-btn--info">Add</button>
                <button @click="closeAddModal()" class="c-btn c-btn--primary">Close</button>
            </div>
        </modal>

    </div>
</template>

<script>
    import axios  from 'axios';

    export default {

        props: {
            id: {
                type: String,
                required: true,
            },
            canAdd: {
                type: Boolean,
                default: false
            }
        },

        data() {
            return {
                isLoading: false,
                isLoaded: false,
                isSending: false,

                showAddModal: false,

                addFormErrors: {},
                amount: 1000,

                parts: [],
                payment: {}
            }
        },

        mounted() {
            this.load();
        },

        methods: {
            load() {

                if (this.isLoading) {
                    return false;
                }

                this.isLoading = true;

                axios
                    .get(`/mainframe/payments/${this.id}/parts`)
                    .then(response => {
                        this.parts = response.data.parts;
                        this.payment = response.data.payment;
                        this.isLoaded = true;
                    })
                    .catch(error => {
                        if (this.loadCount < 3) {
                            this.load();
                        }

                        this.isLoaded = false;
                    })
                    .then(() => {
                        this.isLoading = false;
                    })
                ;
            },

            addPaymentPart() {
                if (this.isSending) {
                    return false;
                }

                this.isSending = true;

                let data = {
                    amount: this.amount
                };

                axios
                    .post(`/mainframe/payments/${this.id}/parts/add`, data)
                    .then(response => {
                        window.location.reload();
                    })
                    .catch(error => {
                        this.addFormErrors = error.response.data.errors;
                    })
                    .then(() => {
                        this.isSending = false;
                    })
                ;
            },

            closeAddModal() {
                this.showAddModal = false;
                this.addFormErrors = {};
            }
        },
    }
</script>
