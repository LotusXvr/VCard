<script setup>
import { useCategoryStore } from "../../stores/category"
import { ref, onMounted, computed, watchEffect } from "vue"

const categoryStore = useCategoryStore()
const categories = ref([])
const props = defineProps({
    transactions: {
        type: Array,
        default: () => [],
    },
    lastMonthTransactions: {
        type: Array,
        default: () => [],
    },
    showLastMonthStatistics: {
        type: Boolean,
        default: true,
    },
})

const emit = defineEmits(["edit", "hideStatistics"])

const loadCategories = async () => {
    categories.value = await categoryStore.loadCategory()
}

const editClick = (transaction) => {
    emit("edit", transaction)
}

const transactionsRef = ref([])
const wasSent = (transaction) => {
    return transaction.type == "D" ? true : false
}

const formatDateTime = (dateTimeString) => {
    const [date, time] = dateTimeString.split(" ")
    const options = { month: "short", day: "numeric" }
    const formattedDate = new Date(date).toLocaleDateString("en-GB", options)
    return { date: formattedDate, time: time.slice(0, 5) }
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

const getCategoryNameById = (categoryId) => {
    if (categoryId != null) {
        const matchingCategory = categories.value.find((category) => category.id == categoryId)

        if (matchingCategory) {
            return matchingCategory.name
        } else {
            return "Sem Categoria"
        }
    } else {
        return "Sem Categoria"
    }
}

const categoryColorMap = {}

const getCategoryColor = (categoryId) => {
    const categoryIdString = String(categoryId)

    if (!categoryColorMap[categoryIdString]) {
        const hash = categoryIdString.split("").reduce((acc, char) => char.charCodeAt(0) + acc, 0)
        const hue = (hash % 160) + 100
        categoryColorMap[categoryIdString] = `hsl(${hue}, 70%, 80%)`
    }

    return categoryColorMap[categoryIdString]
}

const getCategoryColorForTransaction = (transaction) => {
    const categoryId = transaction.category_id
    return getCategoryColor(categoryId)
}

const truncateDescription = (description) => {
    const maxLength = 27
    if (description.length <= maxLength) {
        return description
    } else {
        return description.substring(0, maxLength) + "..."
    }
}

const hideStatisticsState = ref(props.showLastMonthStatistics)
const hideStatistics = (hideStatisticsState) => {
    console.log(hideStatisticsState)
    emit("hideStatistics", hideStatisticsState)
}

// Assista a alterações em props.transactions
watchEffect(() => {
    transactionsRef.value = props.transactions
})

onMounted(() => {
    loadCategories()
})
</script>

<template>
    <div>
        <h1>Transactions</h1>

        <hr />
        <table class="table table-striped">
            <thead>
                <tr>
                    <th style="width: 50px">Date</th>
                    <th style="width: 50px">Time</th>
                    <th style="width: 100px">Value</th>
                    <th style="width: 100px">Balance</th>
                    <th style="width: 100px">Type</th>
                    <th style="width: 100px">Reference</th>
                    <th style="width: 100px">Description</th>
                    <th style="width: 100px">Category</th>
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
                    <td>{{ transaction.payment_type }}</td>
                    <td>{{ transaction.payment_reference }}</td>
                    <td>
                        {{
                            transaction.description
                                ? truncateDescription(transaction.description)
                                : ""
                        }}
                    </td>
                    <td
                        :style="{ backgroundColor: getCategoryColorForTransaction(transaction) }"
                        class="d-flex justify-content-between align-items-center"
                    >
                        <div>
                            {{ getCategoryNameById(transaction.category_id) }}
                        </div>
                        <button class="btn btn-xs btn-light" @click="editClick(transaction)">
                            <i class="bi bi-xs bi-pencil"></i>
                        </button>
                    </td>
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
