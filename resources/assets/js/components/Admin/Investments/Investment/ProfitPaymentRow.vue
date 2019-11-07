<template>
    <tr class="c-table__row">
        <td class="c-table__cell">{{ payment.scheduled_at }}</td>
        <td class="c-table__cell">
            Amount: {{ payment.amount.formatted }} <br>
            WHT: {{ payment.withholding_tax_amount.formatted }} ({{ payment.withholding_tax_percent }}%) <br>
            <b>Payout: {{ payment.payout_amount.formatted }}</b>
        </td>
        <td class="c-table__cell">{{ payment.period_start }}</td>
        <td class="c-table__cell">{{ payment.period_end }}</td>
        <td class="c-table__cell">
            <label class="c-switch">
                <input @change="toggleAutoPayout($event.target.checked)"
                       type="checkbox"
                       :checked="payment.is_auto_payout"
                       :disabled="!payment.is_scheduled"
                       class="c-switch__input">
                <span class="c-switch__label"></span>
            </label>
        </td>
        <td class="c-table__cell">
            <status-badge :status="payment.status"></status-badge>
        </td>
        <td class="c-table__cell">
            <button
                    @click="payout()"
                    :disabled="! payment.is_payable"
                    class="c-btn c-btn--small"
            >Payout</button>
        </td>
    </tr>
</template>

<script>
    import API from '@/api/'

    export default {

        props: {
            payment: {
                type: Object,
                required: true
            }
        },

        data() {
            return {
                isSending: false
            }
        },

        methods: {

            toggleAutoPayout(value) {

                if (this.isSending) {
                    return false;
                }

                this.isSending = true;

                API.Admin.Investments.editProfitPaymentAutoPayout(this.payment.id, {auto_payout: value})
                    .then(response => {
                        this.$emit('changed');
                    })
                    .catch(error => {
                        alert('Error. Please, try again or contact administrator');
                    })
                    .then(() => {
                        this.isSending = false;
                    })
            },

            payout() {

                if (this.isSending) {
                    return false;
                }

                if (! confirm('Please, confirm payout action. \n\nClick OK to continue or Cancel to abort.')) {
                    return;
                }

                this.isSending = true;

                API.Admin.Investments.payoutProfitPayment(this.payment.id)
                    .then(response => {
                        this.$emit('payout-successful');
                    })
                    .catch(error => {
                        this.$emit('payout-failed');
                        alert(error.response.data.message)
                    })
                    .then(() => {
                        this.isSending = false;
                    })
            }
        },
    }
</script>
