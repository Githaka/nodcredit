<template>
    <tr class="c-table__row" >
        <td class="c-table__cell">{{ liquidation.created_at }}</td>
        <td class="c-table__cell">
            <p>Amount: {{ liquidation.amount.formatted }}</p>
            <p>Profit: {{ liquidation.profit.formatted }}</p>
            <p>Penalty: {{ liquidation.penalty_amount.formatted }} ({{ liquidation.penalty_percent }}%)</p>
        </td>
        <td class="c-table__cell">{{ liquidation.reason }}</td>
        <td class="c-table__cell">
            <status-badge :status="liquidation.status" :title="liquidation.paid_out_at"></status-badge>
        </td>
        <td class="c-table__cell">
            <button
                    @click="payout()"
                    :disabled="liquidation.is_paid"
                    class="c-btn c-btn--small"
            >Payout</button>

            <loader v-if="isSending">Processing...</loader>
        </td>
    </tr>

</template>

<script>

    import API from '@/api/'

    export default {
        props: {
            liquidation: {
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
            payout() {

                if (this.isSending) {
                    return false;
                }

                if (! confirm('Please, confirm payout action. \n\nClick OK to continue or Cancel to abort.')) {
                    return;
                }

                this.isSending = true;

                API.Admin.Investments.payoutPartialLiquidation(this.liquidation.id)
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
        }
    }
</script>
