<script setup>
import { ref, onMounted, computed } from "vue"

const props = defineProps({
    transactions: {
        type: Array,
        default: () => [],
    },
})

const transactionsRef = ref([])

const wasSent = (transaction) => {
    return transaction.type == "D" ? true : false
}

const formatDateTime = (dateTimeString) => {
    const [date, time] = dateTimeString.split(" ")
    const options = { month: "short", day: "numeric" }
    const formattedDate = new Date(date).toLocaleDateString("en-GB", options)
    return { date: formattedDate, time: time.slice(0, 5) } // Extract only hours and minutes
}

const transactionsByYearMonth = computed(() => {
    const groupedTransactions = {}
    transactionsRef.value.forEach((transaction) => {
        const year = new Date(transaction.datetime).getFullYear()
        const monthName = new Date(transaction.datetime).toLocaleString("en-GB", { month: "long" })
        const key = `${monthName} ${year}`

        if (!groupedTransactions[key]) {
            groupedTransactions[key] = []
        }

        groupedTransactions[key].push(transaction)
    })
    return groupedTransactions
})

onMounted(() => {
    transactionsRef.value = props.transactions
})
</script>

<template>
    <div>
        <h1>Transactions</h1>
        <hr />

        <table class="table table-striped">
            <thead>
                <tr>
                    <th style="width: 100px">Date</th>
                    <th style="width: 100px">Time</th>
                    <th style="width: 100px">Value</th>
                    <th style="width: 100px">Balance</th>
                    <th style="width: 100px">Reference</th>
                </tr>
            </thead>
            <tbody v-for="(transactions, key) in transactionsByYearMonth" :key="key">
                <h4 style="margin-top: 10px">{{ key }}</h4>
                <tr v-for="transaction in transactions" :key="transaction.id">
                    <td>{{ formatDateTime(transaction.datetime).date }}</td>
                    <td>{{ formatDateTime(transaction.datetime).time }}</td>
                    <td :style="{ color: wasSent(transaction) ? 'red' : 'green' }">
                        {{ wasSent(transaction) ? "-" : "+" }}{{ transaction.value }}
                    </td>
                    <td>{{ transaction.new_balance }}</td>
                    <td>{{ transaction.payment_reference }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
button {
    margin-left: 3px;
    margin-right: 3px;
}
</style>
