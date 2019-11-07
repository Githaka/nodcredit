<template>
    <tr class="c-table__row">
        <td class="c-table__cell">{{ investment.amount.formatted }}</td>
        <td class="c-table__cell">
            {{ investment.user.name }} <br />
            <a :href="'/mainframe/accounts/' + investment.user.id + '/message'">{{ investment.user.email }}</a> <br />
            <a :href="'/mainframe/accounts/' + investment.user.id + '/message'">{{ investment.user.phone }}</a>
        </td>
        <td class="c-table__cell u-text-small">
            <div>{{ investment.created_at }} Created</div>

            <div v-if="investment.is_started">{{ investment.started_at }} Started</div>

            <div v-if="investment.is_liquidated">{{ investment.liquidated_at }} Liquidated</div>

            <div v-if="investment.is_ended">{{ investment.ended_at }} Ended</div>

        </td>
        <td class="c-table__cell">
            <status-badge :status="investment.status"></status-badge>
        </td>
        <td class="c-table__cell">{{ investment.plan_name }}</td>
        <td class="c-table__cell">
            <a :href="`/mainframe/investments/${investment.id}/manage`"
               class="c-btn c-btn--small c-btn--info"
            >Manage</a>
        </td>
    </tr>
</template>

<script>
    export default {
        props: {
            investment: {
                type: Object,
                required: true
            }
        }
    }
</script>
