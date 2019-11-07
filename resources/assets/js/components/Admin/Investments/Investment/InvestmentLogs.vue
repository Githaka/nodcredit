<template>

    <div class="c-card">
        <h4 class="u-mb-small">Actions on Investment</h4>
        <table class="c-table">
            <thead class="c-table__head">
                <tr class="c-table__row">
                    <th class="c-table__cell c-table__cell--head">Date</th>
                    <th class="c-table__cell c-table__cell--head">Action</th>
                    <th class="c-table__cell c-table__cell--head">Author</th>
                </tr>
            </thead>

            <tbody v-if="logs.length">
                <investment-log-row
                        v-for="log in logs"
                        :log="log"
                        :key="log.id"
                ></investment-log-row>
            </tbody>
        </table>
    </div>

</template>

<script>
    import InvestmentLogRow from './InvestmentLogRow.vue'

    export default {
        props: {
            logs: {
                type: Array,
                required: true
            }
        },

        components: {
            InvestmentLogRow
        }
    }
</script>
