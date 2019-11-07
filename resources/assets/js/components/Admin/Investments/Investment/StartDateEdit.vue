<template>

    <div class="investment-start-edit-wrapper">

        <button v-if="isStarted" @click="showModal()" class="c-btn c-btn--small">Edit</button>
        <button v-else @click="showModal()" class="c-btn c-btn--success c-btn--small">Start</button>

        <modal
                v-if="showEditModal"
                @close="closeModal()"
                class="modal-investment-start-edit"
        >
            <h5 slot="header">Investment Start Date</h5>

            <div slot="body" v-if="isLoaded">

                <p v-if="! investment.is_changeable" class="u-mb-small">You can`t change Investment if it is ended/liquidated or has any paid profit.</p>
                <div v-else>
                    <div class="u-mb-small">
                        <datetime-picker
                                v-model="startedAt"
                                :no-header="true"
                                :no-label="true"
                                :no-shortcuts="true"
                                :no-button="true"
                                :format="'YYYY-MM-DD HH:mm'"
                                :formatted="'YYYY-MM-DD HH:mm'"
                        ></datetime-picker>
                    </div>
                    <p class="u-mb-small"><b><i>Submit will re-build profit payments plan.</i></b></p>

                    <div class="u-mb-small">
                        <label class="c-switch">
                            <input v-model="sendMessage" type="checkbox" class="c-switch__input">
                            <span class="c-switch__label">Send "Investment started" to Investor</span>
                        </label>
                    </div>

                    <form-errors :errors="formErrors"></form-errors>
                </div>

                <loader v-if="isSending">Please wait...</loader>
                <loader v-if="isLoading"></loader>
            </div>

            <div slot="footer">
                <button v-if="isLoaded && investment.is_changeable" @click="submit()" class="c-btn c-btn--info">Submit</button>
                <button @click="closeModal()" class="c-btn c-btn--primary">Close</button>
            </div>
        </modal>

    </div>


</template>

<script>

    import API from './../../../../api'
    import {DateTime} from 'luxon'

    export default {

        props: {
            id: {
                type: String,
                required: true
            },
            isStarted: {
                type: Boolean,
                default() {
                    return false
                }
            }
        },

        data() {
            return {
                isLoading: false,
                isLoaded: false,
                isSending: false,

                showEditModal: false,

                startedAt: null,
                sendMessage: false,

                formErrors: {},
                investment: {},
            }
        },

        methods: {
            load() {

                if (this.isLoading) {
                    return false;
                }

                this.isLoading = true;

                API.Admin.Investments.get(this.id)
                    .then(response => {
                        this.investment = response.data.investment;
                        this.startedAt = this.investment.started_at;
                        this.isLoaded = true;
                    })
                    .catch(error => {
                        this.isLoaded = false;
                    })
                    .then(() => {
                        this.isLoading = false;
                    })
                ;
            },

            submit() {
                if (this.isSending) {
                    return false;
                }

                this.isSending = true;
                this.formErrors = {};

                let data = {
                    started_at: this.startedAt,
                    send_message: this.sendMessage,
                };

                API.Admin.Investments.startDateEdit(this.id, data)
                    .then(response => {
                        this.$emit('changed');
                        this.closeModal();
                    })
                    .catch(error => {
                        this.formErrors = error.response.data.errors;
                    })
                    .then(() => {
                        this.isSending = false;
                    })
                ;
            },

            closeModal() {
                this.showEditModal = false;
                this.formErrors = {};
            },

            showModal() {
                this.load();
                this.showEditModal = true;
            }
        }

    }
</script>
