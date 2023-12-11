<script setup>
import { useCategoryStore } from "../../stores/category"
import { ref, onMounted, computed, shallowRef } from "vue"
import Chart from "chart.js/auto"
import axios from "axios";

const categories = ref([])
const props = defineProps({
    transactions: {
        type: Array,
        default: () => [],
    },
    filtered: {
        type: Boolean,
        default: false,
    },
})
const emit = defineEmits(["edit"])

const editClick = (transaction) => {
    emit("edit", transaction)
}
const loadCategories = async () => {
    try {
        await axios.get("vcard/" + props.transactions[0].vcard + "/category/all").then((response) => {
            console.log(response.data)
            categories.value = response.data
        })
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

     // Sort transactions within each day by time
    Object.keys(groupedTransactions).forEach((key) => {
        groupedTransactions[key] = groupedTransactions[key].sort((a, b) => {
            const timeA = new Date(a.datetime).getTime()
            const timeB = new Date(b.datetime).getTime()
            return timeA - timeB;
        });

        groupedTransactions[key].reverse();
    })

    
    return groupedTransactions
})

const monthNames = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December",
]

const currentDate = new Date()

const lastMonthDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1)

const lastMonthYear = lastMonthDate.getFullYear()
const lastMonthMonth = lastMonthDate.getMonth() + 1

const lastMonthTransactions = computed(() => {
    return transactionsRef.value.filter((transaction) => {
        const transactionDate = new Date(transaction.datetime)
        return (
            transactionDate.getFullYear() === lastMonthYear &&
            transactionDate.getMonth() + 1 === lastMonthMonth
        )
    })
})

const sumValues = (transactions, type) => {
    return transactions
        .filter((transaction) => transaction.type === type)
        .reduce((sum, transaction) => sum + parseFloat(transaction.value), 0)
}

const sumDebitValues = computed(() => {
    const rawValue = sumValues(lastMonthTransactions.value, "D");
    return parseFloat(rawValue.toFixed(2));
});

const sumCreditValues = computed(() => {
    const rawValue = sumValues(lastMonthTransactions.value, "C");
    return parseFloat(rawValue.toFixed(2));
});

const dateindex = ref(0)
const dates = computed(() => {
    const dates = lastMonthTransactions.value
        .map((transaction) => {
            dateindex.value += 1
            return "(" + dateindex.value + ") " + formatDateTime(transaction.datetime).date
        }).reverse()

    return dates
})

const balances = computed(() => {
    const balances = lastMonthTransactions.value
        .map((transaction) => {
            return transaction.new_balance
        })
        .reverse()

    return balances
})

const balanceChartEl = ref(null)
const balanceChart = shallowRef(null)

const loadChart = () => {
    balanceChart.value = new Chart(balanceChartEl.value.getContext("2d"), {
        type: "line",
        data: {
            labels: dates.value,
            datasets: [
                {
                    label: "Balance (€)",
                    data: balances.value,
                    backgroundColor: "rgba(255, 99, 132, 0.2)",
                    borderColor: "rgba(255, 99, 132, 1)",
                    borderWidth: 1,
                },
            ],
        },
    })
}

const getCategoryNameById = (categoryId) => {
    if (categoryId != null) {
        const matchingCategory = categories.value.find(category => category.id == categoryId);

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

onMounted(async () => {
    transactionsRef.value = props.transactions
    loadCategories()
    loadChart()
    console.log(props.filtered)
})
</script>

<template>
    <div>
        <h1>Transactions</h1>

        <div class="container">
            <h4>Your balance in {{ monthNames[lastMonthMonth - 1] }}</h4>
            <canvas
                ref="balanceChartEl"
                height="200px"
                width="200px"
                style="height: 200px; width: 200px"
            ></canvas>

            <div class="row mt-3">
                <div class="col-md-6">
                    <h5>Your earnings:</h5>
                    <p class="h5 text-success">{{ sumCreditValues }}€</p>
                </div>
                <div class="col-md-6">
                    <h5>Your expenses:</h5>
                    <p class="h5 text-danger">{{ sumDebitValues }}€</p>
                </div>
            </div>
        </div>

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
