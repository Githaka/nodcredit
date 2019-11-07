<template>

    <div class="c-card">
        <h4 class="u-mb-small">Partial Liquidations</h4>
        <table class="c-table">
            <thead class="c-table__head">
                <tr class="c-table__row">
                    <th class="c-table__cell c-table__cell--head">Date</th>
                    <th class="c-table__cell c-table__cell--head">Amount</th>
                    <th class="c-table__cell c-table__cell--head">Reason</th>
                    <th class="c-table__cell c-table__cell--head">Status</th>
                    <th class="c-table__cell c-table__cell--head">Action</th>
                </tr>
            </thead>

            <tbody v-if="liquidations.length">
                <partial-liquidation-row
                        v-for="liquidation in liquidations"
                        :liquidation="liquidation"
                        :key="liquidation.id"
                        @payout-successful="$emit('refresh')"
                ></partial-liquidation-row>
            </tbody>
            <tbody v-else="">
                <tr>
                    <td class="c-table__cell text-center" colspan="5">
                        <p>No partial liquidations.</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</template>

<script>

    import PartialLiquidationRow from './PartialLiquidationRow.vue'

    export default {
        props: {
            liquidations: {
                type: Array,
                required: true
            }
        },

        components: {
            PartialLiquidationRow
        }
    }
</script>
