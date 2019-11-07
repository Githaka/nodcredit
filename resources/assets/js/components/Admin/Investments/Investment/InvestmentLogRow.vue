<template>
    <tr class="c-table__row" >
        <td class="c-table__cell">{{ log.created_at }}</td>
        <td class="c-table__cell">{{ log.text }}</td>
        <td class="c-table__cell">
            <p v-if="log.created_by && Object.keys(log.created_by).length">{{ log.created_by.email }}</p>
            <p v-else="">system</p>
        </td>
    </tr>

</template>

<script>
    export default {
        props: {
            log: {
                type: Object,
                required: true
            }
        },
    }
</script>
