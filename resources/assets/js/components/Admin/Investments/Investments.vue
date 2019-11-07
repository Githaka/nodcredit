<template>

    <div class="c-table-responsiv">
        <h4>Investments</h4>

        <div class="u-mt-small u-mb-medium">
            <investment-add
                @added="load()"
            ></investment-add>
        </div>

        <table class="c-table">
            <thead class="c-table__head">
            <tr class="c-table__row">
                <th class="c-table__cell c-table__cell--head">Amount</th>
                <th class="c-table__cell c-table__cell--head">Customer</th>
                <th class="c-table__cell c-table__cell--head">Date</th>
                <th class="c-table__cell c-table__cell--head">Status</th>
                <th class="c-table__cell c-table__cell--head">Tenor</th>
                <th class="c-table__cell c-table__cell--head">Action</th>
            </tr>
            </thead>

            <tbody>

            <investment-row
                    v-for="investment in investments"
                    :investment="investment"
                    :key="investment.id"
            ></investment-row>
            </tbody>
        </table>
    </div>
</template>

<script>
    import API from './../../../api';
    import InvestmentAdd from './InvestmentAdd.vue'
    import InvestmentRow from './InvestmentRow.vue'

    export default {

        data() {
            return {
                isLoading: false,
                isLoaded: false,
                isSending: false,

                investments: []
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

                API.Admin.Investments.getAll()
                    .then(response => {
                        this.investments = response.data.investments;
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
        },

        components: {
            InvestmentAdd,
            InvestmentRow
        }
    }
</script>
