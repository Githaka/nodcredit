<template>
    <div>
        <div class="row u-justify-center">

            <button class="c-btn c-btn--info" @click="openForm">
                Add Work History
            </button>
        </div>

        <p v-if="loadingWorkHistory">Loading..</p>

        <div class="row">
            <div class="col-12">
                <div class="row justify-content-center" style="padding-top: 30px;">
                    <div class="col-md-6 col-xl-4" v-for="item in items" :key="item.id">
                        <div class="c-card is-animated">
                            <h5 class="u-mb-xsmall">{{item.employer_name}}</h5>

                            <span class="c-text--subtitle">Email Address</span>
                            <p class="u-mb-small u-text-large">{{item.work_email}}</p>

                            <span class="c-text--subtitle">Phone</span>
                            <p class="u-mb-small u-text-large">{{item.work_phone}}</p>

                            <span class="c-text--subtitle">Work Date</span>
                            <p class="u-mb-small u-text-large">{{item.started_date}} to {{item.stopped_date}}</p>

                            <span class="c-text--subtitle">Address</span>
                            <p class="u-mb-small u-text-large">{{item.work_address}}</p>

                            <p>
                                <button class="c-btn c-btn--small c-btn--primary" @click="editWorkHistory(item)">Edit</button>
                                <button class="c-btn c-btn--small c-btn--danger" @click="deleteWorkHistory(item)">X</button>
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="c-modal modal fade" id="modal1" tabindex="-1" role="dialog" aria-labelledby="modal1">
            <div class="c-modal__dialog modal-dialog" role="document">
                <div class="modal-content">
                    <div class="c-card u-p-medium u-mh-auto" style="max-width:500px;">
                        <h3>Add new work history</h3> <br>
                        <div class="row">
                            <div class="col-xl-6">

                                <div class="c-field u-mb-medium">
                                    <label class="c-field__label" for="employer_name">Employer name</label>
                                    <input class="c-input" v-model="currentWorkHistory.employer_name" type="text" id="employer_name"
                                        placeholder="Employer name">
                                    <small class="c-field__message u-color-danger" v-if="errors.employer_name"> <i
                                            class="fa fa-times-circle"></i>{{errors.employer_name[0]}}</small>
                                </div>

                                <div class="c-field u-mb-medium">
                                    <label class="c-field__label" for="work_phone">Work Phone</label>
                                    <input class="c-input" v-model="currentWorkHistory.work_phone" type="text" id="work_phone"
                                        placeholder="Enter your office phone line">
                                    <small class="c-field__message u-color-danger" v-if="errors.work_phone"> <i class="fa fa-times-circle"></i>{{errors.work_phone[0]}}</small>
                                </div>

                            </div>

                            <div class="col-xl-6">
                                <div class="c-field u-mb-medium">
                                    <label class="c-field__label" for="work_email">Work email address</label>
                                    <input class="c-input" v-model="currentWorkHistory.work_email" type="text" id="work_email"
                                        placeholder="Email address">
                                    <small class="c-field__message u-color-danger" v-if="errors.work_email"> <i class="fa fa-times-circle"></i>{{errors.work_email[0]}}</small>
                                </div>

                                <div class="c-field u-mb-medium">
                                    <label class="c-field__label" for="work_website">Company website</label>
                                    <input class="c-input" v-model="currentWorkHistory.work_website" type="text" id="work_website"
                                        placeholder="Company website">
                                    <small class="c-field__message u-color-danger" v-if="errors.work_website"> <i class="fa fa-times-circle"></i>{{errors.work_website[0]}}</small>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-xl-12">
                                <div class="c-field u-mb-medium">
                                    <label class="c-field__label" for="work_address">Employer Full Address</label>
                                    <input class="c-input" v-model="currentWorkHistory.work_address" type="text" id="work_address"
                                        placeholder="Full address">
                                    <small class="c-field__message u-color-danger" v-if="errors.work_address"> <i class="fa fa-times-circle"></i>{{errors.work_address[0]}}</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xl-12">
                                <div class="c-field u-mb-medium">
                                    <div class="c-choice c-choice--checkbox">
                                        <input class="c-choice__input" id="checkbox1" v-model="currentWorkHistory.is_current"
                                            type="checkbox">
                                        <label class="c-choice__label" for="checkbox1">Current Work Place</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xl-6">
                                <div class="c-field u-mb-medium">
                                    <label class="c-field__label" for="started_date">Date you started work</label>
                                    <input class="c-input" v-model="currentWorkHistory.started_date" type="date" id="started_date"
                                        placeholder="Enter date" required pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}">
                                    <small class="c-field__message u-color-danger" v-if="errors.started_date"> <i class="fa fa-times-circle"></i>{{errors.started_date[0]}}</small>
                                </div>
                            </div>

                            <div class="col-xl-6" v-show="!currentWorkHistory.is_current">
                                <div class="c-field u-mb-medium">
                                    <label class="c-field__label" for="started_date">Date you stopped work</label>
                                    <input class="c-input" v-model="currentWorkHistory.stopped_date" type="date" id="stopped_date"
                                        placeholder="Enter date" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}">
                                    <small class="c-field__message u-color-danger" v-if="errors.stopped_date"> <i class="fa fa-times-circle"></i>{{errors.stopped_date[0]}}</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xl-8">
                                <button type="button" class="c-btn c-btn--info c-btn--fullwidth" @click="saveWorkHistory"
                                    :disabled="savingWorkHistory">
                                    <span v-if="currentWorkHistory.id">Update</span>
                                    <span v-else>Create Work History</span>
                                </button>
                            </div>

                            <div class="col-xl-4">
                                <button type="button" class="c-btn c-btn--danger c-btn--fullwidth" @click="closeForm()">Close</button>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    function isNumber(n) {
        return !isNaN(parseFloat(n)) && isFinite(n);
    }

    import axios from 'axios';
    import DatePicker from 'vue-md-date-picker'

    export default {
        name: 'LoanForm',
        data() {
            return {
                loadingWorkHistory: false,
                savingWorkHistory: false,
                currentWorkHistory: {},
                errors: {},
                items: []
            }
        },

        mounted() {
            $('#modal1').modal({
                keyboard: false,
                show: false
            });
            this.loadWorkHistory();
        },

        methods: {

            resetForm() {

            },

            openForm() {
                this.currentWorkHistory = {};
                this.savingWorkHistory = false;
                $('#modal1').modal('show');
                return false;
            },

            closeForm() {
                this.currentWorkHistory = {};
                this.savingWorkHistory = false;
                $('#modal1').modal('hide');
                return false;
            },

            loadWorkHistory() {
                let vm = this;
                vm.loadingWorkHistory = true;
                axios.get('/account/me/work-history').then(function (response) {
                    vm.items = response.data;
                    vm.loadingWorkHistory = false;
                }).catch(function (error) {
                    vm.loadingWorkHistory = false;
                    alert('Error ' + error);
                });
            },

            saveWorkHistory() {
                let vm = this;
                vm.savingWorkHistory = true;
                axios.post('/account/me/work-history', vm.currentWorkHistory).then(function (response) {

                    if (response.data.status == 'error') {
                        vm.errors = response.data.messages;
                        vm.savingWorkHistory = false;
                    } else {
                        vm.loadWorkHistory();
                        vm.currentWorkHistory = {};
                        vm.savingWorkHistory = false;
                        $('#modal1').modal('hide');
                    }
                }).catch(function (error) {
                    vm.savingWorkHistory = false;
                });
            },

            editWorkHistory(item) {
                this.currentWorkHistory = item;
                $('#modal1').modal('show');
            },

            deleteWorkHistory(item) {

                let vm = this;
                if (confirm('Are you sure you want to delete this work history?')) {
                    ///me/work-history/
                    axios.delete('/account/me/work-history/' + item.id).then(function (response) {
                        if (response.data.status == 'ok') {
                            vm.loadWorkHistory();
                        } else {
                            alert('ERROR: ' + response.data.message);
                        }
                    }).catch(function (error) {
                        alert('ERROR: ' + error);
                    });
                }
            }

        }
    }
</script>