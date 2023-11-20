<script setup>
import { ref, onMounted } from "vue"

const props = defineProps({
    transactions: {
        type: Array,
        default: () => [],
    },
})

const transactionsRef = ref([])

onMounted(() => {
    transactionsRef.value = props.transactions
})

const wasSent = (transaction) => {
    console.log("Checking if transaction was sent:", transaction)
    console.log("old balance" + transaction.old_balance)
    console.log("new balance" + transaction.new_balance)
    return transaction.old_balance > transaction.new_balance
}
</script>

<template>
    <table class="table">
        <thead>
            <tr>
                <th>id</th>
                <th>Date</th>
                <th>Old Balance</th>
                <th>Value</th>
                <th>New Balance</th>
                <th>Reference</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="transaction in transactionsRef" :key="transaction.id">
                <td>{{ transaction.id }}</td>
                <td>{{ transaction.datetime }}</td>
                <td>{{ transaction.old_balance }}</td>
                <td :style="{ color: wasSent(transaction) ? 'red' : 'green' }">
                    {{ wasSent(transaction) ? "-" : "+" }}{{ transaction.value }}
                </td>
                <td>{{ transaction.new_balance }}</td>
                <td>{{ transaction.payment_reference }}</td>
            </tr>
        </tbody>
    </table>
</template>

<style scoped>
button {
    margin-left: 3px;
    margin-right: 3px;
}
</style>
