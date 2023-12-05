<script setup>
import { ref, onMounted, computed } from "vue"
import { useCategoryStore } from "../../stores/category"

const categoryStore = useCategoryStore()

const props = defineProps({
    transactions: {
        type: Array,
        default: () => [],
    },
})
const emit = defineEmits(["edit"])

const editClick = (transaction) => {
    emit("edit", transaction)
}
const loadCategories= async () => {
  try {
    await categoryStore.loadCategory()
  } catch (error) {
    console.log(error)
  }
}

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

// Get the current date
const currentDate = new Date();
// Calculate the first day of the current month
const lastMonthDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
// Extract the year and month of the current month
const lastMonthYear = lastMonthDate.getFullYear();
const lastMonthMonth = lastMonthDate.getMonth() + 1; // Months are zero-indexed

// Compute last month's transactions
const lastMonthTransactions = computed(() => {
    return transactionsRef.value.filter((transaction) => {
        const transactionDate = new Date(transaction.datetime);
        return (
            transactionDate.getFullYear() === lastMonthYear &&
            transactionDate.getMonth() + 1 === lastMonthMonth
        );
    });
});

// Function to calculate the sum of values for a given type ("D" or "C")
const sumValues = (transactions, type) => {
    return transactions
        .filter((transaction) => transaction.type === type)
        .reduce((sum, transaction) => sum + parseFloat(transaction.value), 0);
};

// Compute the sum of debit and credit values for last month
const sumDebitValues = computed(() => sumValues(lastMonthTransactions.value, "D"));
const sumCreditValues = computed(() => sumValues(lastMonthTransactions.value, "C"));

const getCategoryNameById = (categoryId) => {
    const categoriesValue = categoryStore.categories;
    console.log("categoriesValue", categoriesValue)

    if (categoriesValue && categoriesValue.name && categoriesValue.name[categoryId]) {
        return categoriesValue.name[categoryId];
    } else {
        return "Sem Categoria";
    }
};

const getCategoryNameForTransaction = (transaction) => {
    const categoryId = transaction.category_id
    return getCategoryNameById(categoryId)
}

const categoryColorMap = {}

const getCategoryColor = (categoryId) => {
    // Ensure categoryId is a string
    const categoryIdString = String(categoryId)

    if (!categoryColorMap[categoryIdString]) {
        // If not, generate a color based on the category ID
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

onMounted(async () => {
    transactionsRef.value = props.transactions
    loadCategories()
})
</script>

<template>
    <div>
        <h1>Transactions</h1>
        <p> Credits: {{ sumCreditValues }}</p>
        <p> Debits: {{ sumDebitValues }}</p>
        <hr />
        <table class="table table-striped">
            <thead>
                <tr>
                    <th style="width: 100px">Date</th>
                    <th style="width: 100px">Time</th>
                    <th style="width: 100px">Value</th>
                    <th style="width: 100px">Balance</th>
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
                            {{ getCategoryNameForTransaction(transaction) }}
                        </div>
                        <button
                            class="btn btn-xs btn-light"
                            @click="editClick(transaction)"
                        >
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
