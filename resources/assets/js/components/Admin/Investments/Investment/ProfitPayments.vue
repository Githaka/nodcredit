<template>

    <div class="c-card">
        <h4 class="u-mb-small">Profit Payments</h4>
        <table class="c-table">
            <thead class="c-table__head">
                <tr class="c-table__row">
                    <th class="c-table__cell c-table__cell--head">Scheduled At</th>
                    <th class="c-table__cell c-table__cell--head">Amount</th>
                    <th class="c-table__cell c-table__cell--head">Period Start</th>
                    <th class="c-table__cell c-table__cell--head">Period End</th>
                    <th class="c-table__cell c-table__cell--head">Auto-payout</th>
                    <th class="c-table__cell c-table__cell--head">Status</th>
                    <th class="c-table__cell c-table__cell--head">Action</th>
                </tr>
            </thead>

            <tbody v-if="payments.length">
                <profit-payment-row
                        v-for="payment in payments"
                        :payment="payment"
                        :key="payment.id"
                        @changed="$emit('refresh')"
                        @payout-successful="$emit('refresh')"
                ></profit-payment-row>
            </tbody>
            <tbody v-else="">
                <tr>
                    <td class="c-table__cell text-center" colspan="7">
                        <p>To build payments you need to start the Investment.</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</template>

<script>
    import ProfitPaymentRow from './ProfitPaymentRow.vue'


    export default {
        props: {
            payments: {
                type: Array,
                required: true
            }
        },

        components: {
            ProfitPaymentRow
        }
    }
</script>
