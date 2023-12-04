<script setup>
import { onMounted, ref, shallowRef } from "vue"
import axios from "axios"
import { useUserStore } from "../stores/user"
import Chart from "chart.js/auto"
const userStore = useUserStore()

const newVCard = () => {
    return {
        phone_number: "",
        name: "",
        email: "",
        photo_url: null,
        balance: 0,
        max_debit: 0,
        password: "",
        confirmation_code: "",
    }
}

const vcard = ref(newVCard())
const loadVCard = () => {
    axios
        .get("vcards/" + userStore.userPhoneNumber)
        .then((response) => {
            vcard.value = response.data.data
        })
        .catch((error) => {
            console.log(error)
        })
}

const vcardCount = ref(0)
const getCountVCards = () => {
    axios
        .get("statistics/vcards/count")
        .then((response) => {
            console.log(response.data.vcardCount)
            vcardCount.value = response.data.vcardCount
        })
        .catch((error) => {
            console.log(error)
        })
}

const activeVCardCount = ref(0)
const getCountActiveVCards = () => {
    axios
        .get("statistics/vcards/active/count")
        .then((response) => {
            console.log(response.data.activeVCardCount)
            activeVCardCount.value = response.data.activeVCardCount
        })
        .catch((error) => {
            console.log(error)
        })
}

const totalVCardBalance = ref(0)
const getTotalVCardBalance = () => {
    axios
        .get("statistics/vcards/balance")
        .then((response) => {
            console.log(response.data.vcardBalanceSum)
            totalVCardBalance.value = response.data.vcardBalanceSum
        })
        .catch((error) => {
            console.log(error)
        })
}

const totalActiveVCardBalance = ref(0)
const getTotalActiveVCardBalance = () => {
    axios
        .get("statistics/vcards/active/balance")
        .then((response) => {
            console.log(response.data.activeVCardBalanceSum)
            totalActiveVCardBalance.value = response.data.activeVCardBalanceSum
        })
        .catch((error) => {
            console.log(error)
        })
}

const transactionsCount = ref(0)
const getCountTransactions = () => {
    axios
        .get("statistics/transactions/count")
        .then((response) => {
            console.log(response.data.transactionsCount)
            transactionsCount.value = response.data.transactionsCount
        })
        .catch((error) => {
            console.log(error)
        })
}
const startDate = ref("")
const endDate = ref("")
const transactionsSumBetweenDates = ref(0)
const today = new Date()
const year = today.getFullYear()
const month = String(today.getMonth() + 1).padStart(2, "0") // Months are zero-based
const day = String(today.getDate()).padStart(2, "0")
const todayDateString = `${year}-${month}-${day}`

const filterTransactions = async () => {
    if (startDate.value == "") {
        await axios
            .get("statistics/transactions/older", {
                params: {
                    startDate: startDate.value,
                    endDate: endDate.value,
                },
            })
            .then((response) => {
                startDate.value = response.data.olderTransaction.date
            })
            .catch((error) => {
                console.error(error)
            })
    }
    if (endDate.value == "") {
        endDate.value = todayDateString
    }
    await axios
        .get("statistics/transactions/sum-between-dates", {
            params: {
                startDate: startDate.value,
                endDate: endDate.value,
            },
        })
        .then((response) => {
            transactionsSumBetweenDates.value = response.data.sumBetweenDates
            transactionsCountBetweenDates.value = response.data.countBetweenDates
        })
        .catch((error) => {
            console.error(error)
        })
    console.log(transactionsSumBetweenDates.value)
}
const transactionsCountBetweenDates = ref(0)
const paymentType = ref("Vcard")
const transactionsCountByType = ref(0)
const filterTransactionByType = async () => {
    await axios
        .get("statistics/transactions/count-by-type", {
            params: {
                paymentType: paymentType.value,
            },
        })
        .then((response) => {
            transactionsCountByType.value = response.data.countByPayementType
        })
        .catch((error) => {
            console.error(error)
        })
}

const transactionsSum = ref(0)
const getSumTransactions = () => {
    axios
        .get("statistics/transactions/sum")
        .then((response) => {
            console.log(response.data.transactionsSum)
            transactionsSum.value = response.data.transactionsSum
        })
        .catch((error) => {
            console.log(error)
        })
}

const transactionsQuantityChartEl = ref(null)
const transactionsQuantityChart = shallowRef(null)
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
const transactionsQuantityByMonth = ref(0)
const getTransactionsQuantityByMonthGraph = () => {
    axios
        .get("statistics/transactions/quantity-by-month")
        .then((response) => {
            console.log(response.data.transactionsCountByMonth)
            transactionsQuantityByMonth.value = response.data.transactionsCountByMonth

            const months = transactionsQuantityByMonth.value.map(
                (entry) => monthNames[entry.month - 1],
            )
            const transactionsQuantity = transactionsQuantityByMonth.value.map(
                (entry) => entry.count,
            )

            transactionsQuantityChart.value = new Chart(
                transactionsQuantityChartEl.value.getContext("2d"),
                {
                    type: "line",
                    data: {
                        labels: months,
                        datasets: [
                            {
                                label: "Number of monthly transactions",
                                data: transactionsQuantity,
                                backgroundColor: "rgba(255, 99, 132, 0.2)",
                                borderColor: "rgba(255, 99, 132, 1)",
                                borderWidth: 1,
                            },
                        ],
                    },
                },
            )
        })
        .catch((error) => {
            console.log(error)
        })
}

const vcardBalanceDistributionChartEl = ref(null)
const vcardBalanceDistributionChart = shallowRef(null)

const getVCardBalanceDistributionGraph = () => {
    axios
        .get("statistics/vcards/balance-distribution")
        .then((response) => {
            const balanceRanges = response.data.balanceRanges
            const vcardCounts = response.data.vcardCounts

            vcardBalanceDistributionChart.value = new Chart(
                vcardBalanceDistributionChartEl.value.getContext("2d"),
                {
                    type: "bar",
                    data: {
                        labels: balanceRanges,
                        datasets: [
                            {
                                label: "VCard Balance Distribution",
                                data: vcardCounts,
                                backgroundColor: "rgba(75, 192, 192, 0.2)",
                                borderColor: "rgba(75, 192, 192, 1)",
                                borderWidth: 1,
                            },
                        ],
                    },
                },
            )
        })
        .catch((error) => {
            console.log(error)
        })
}

const transactionsByPaymentMethodChartEl = ref(null)
const transactionsByPaymentMethodChart = shallowRef(null)

const getTransactionsByPaymentMethodGraph = () => {
    axios
        .get("statistics/transactions/by-payment-method")
        .then((response) => {
            const paymentMethods = response.data.paymentMethods
            const transactionCounts = response.data.transactionCounts

            // Combine payment methods and counts into an array of objects
            const data = paymentMethods.map((method, index) => ({
                method,
                count: transactionCounts[index],
            }))

            // Find the index of VCard dynamically
            const vcardIndex = data.findIndex((item) => item.method === "VCARD")

            // If VCard is found, divide its transaction count by 2
            if (vcardIndex !== -1) {
                data[vcardIndex].count /= 2
            }

            // Sort the array based on transaction count in descending order
            data.sort((a, b) => b.count - a.count)

            // Extract sorted payment methods and transaction counts
            const sortedPaymentMethods = data.map((item) => item.method)
            const sortedTransactionCounts = data.map((item) => item.count)

            transactionsByPaymentMethodChart.value = new Chart(
                transactionsByPaymentMethodChartEl.value.getContext("2d"),
                {
                    type: "pie",
                    data: {
                        labels: sortedPaymentMethods,
                        datasets: [
                            {
                                data: sortedTransactionCounts,
                                backgroundColor: [
                                    "rgba(255, 99, 132, 0.5)",
                                    "rgba(54, 162, 235, 0.5)",
                                    "rgba(255, 206, 86, 0.5)",
                                    "rgba(75, 192, 192, 0.5)",
                                    "rgba(153, 102, 255, 0.5)",
                                    "rgba(255, 159, 64, 0.5)",
                                ],
                                borderColor: [
                                    "rgba(255, 99, 132, 1)",
                                    "rgba(54, 162, 235, 1)",
                                    "rgba(255, 206, 86, 1)",
                                    "rgba(75, 192, 192, 1)",
                                    "rgba(153, 102, 255, 1)",
                                    "rgba(255, 159, 64, 1)",
                                ],
                                borderWidth: 1,
                            },
                        ],
                    },
                },
            )
        })
        .catch((error) => {
            console.log(error)
        })
}

const averageTransactionAmountChartEl = ref(null)
const averageTransactionAmountChart = shallowRef(null)
const averageTransactionAmountByMonth = ref([])
const getAverageTransactionAmountGraph = () => {
    axios
        .get("statistics/transactions/average-amount-by-month")
        .then((response) => {
            averageTransactionAmountByMonth.value = response.data

            const months = averageTransactionAmountByMonth.value.map(
                (entry) => `${monthNames[entry.month - 1]} ${entry.year}`,
            )
            const averageAmounts = averageTransactionAmountByMonth.value.map(
                (entry) => entry.average_amount,
            )

            averageTransactionAmountChart.value = new Chart(
                averageTransactionAmountChartEl.value.getContext("2d"),
                {
                    type: "line",
                    data: {
                        labels: months,
                        datasets: [
                            {
                                label: "Average Transaction Amount",
                                data: averageAmounts,
                                backgroundColor: "rgba(0, 128, 0, 0.2)",
                                borderColor: "rgba(0, 128, 0, 1)",
                                borderWidth: 1,
                            },
                        ],
                    },
                },
            )
        })
        .catch((error) => {
            console.log(error)
        })
}

onMounted(() => {
    loadVCard()
    getCountVCards()
    getCountActiveVCards()
    getTotalVCardBalance()
    getTotalActiveVCardBalance()
    getCountTransactions()
    getSumTransactions()
    getTransactionsQuantityByMonthGraph()
    getVCardBalanceDistributionGraph()
    getTransactionsByPaymentMethodGraph()
    getAverageTransactionAmountGraph()
})
</script>

<template>
    <div class="container mt-4">
        <div class="row">
            <div class="col-lg-12 mb-4">
                <h1 class="text-center mb-4">Admin Dashboard</h1>
                <div class="card text-center">
                    <div class="card-header">
                        <h4 class="card-title">User Information</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong>Name:</strong> {{ userStore.userName }}
                            </li>
                            <li class="list-group-item">
                                <strong>Email:</strong> {{ userStore.userPhoneNumber }}
                            </li>
                            <!-- Add more list items based on your user properties -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <h1 class="text-center mb-4">Statistics</h1>

        <div class="container mt-4">
            <div class="row">
                <div class="col-md-6">
                    <h4>Transactions Quantity</h4>
                    <canvas ref="transactionsQuantityChartEl"></canvas>
                </div>
                <div class="col-md-6">
                    <h4>Average Transaction Amount</h4>
                    <canvas ref="averageTransactionAmountChartEl"></canvas>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-6">
                    <h4>VCard Balance Distribution</h4>
                    <canvas ref="vcardBalanceDistributionChartEl"></canvas>
                </div>
                <div class="col-md-6">
                    <h4>Transactions By Payment Method</h4>
                    <canvas ref="transactionsByPaymentMethodChartEl"></canvas>
                </div>
            </div>
        </div>

        <div class="container mt-5">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Current Count of VCards</h4>
                            <p class="card-text">{{ vcardCount }}</p>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h4 class="card-title">Current Count of Active VCards</h4>
                            <p class="card-text">{{ activeVCardCount }}</p>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h4>Total Balance of All VCards</h4>
                            <p>{{ totalVCardBalance }}</p>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h4>Total Balance of All Active VCards</h4>
                            <p>{{ totalActiveVCardBalance }}</p>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h4>Current Count of Transactions</h4>
                            <p>{{ transactionsCount }}</p>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h4>Current Sum of Transactions</h4>
                            <p>{{ transactionsSum }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mt-4">
                        <div class="card-body">
                            <h7><b>Filter Transactions:</b></h7>
                            <h4>Filter Transactions by Type</h4>
                            <div class="container mt-4">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mt-3">
                                            <label for="paymentType" class="form-label"
                                                >Select Payment Method:</label
                                            >
                                            <select
                                                v-model="paymentType"
                                                id="paymentType"
                                                name="paymentType"
                                                class="form-select"
                                            >
                                                <option value="Vcard">Vcard</option>
                                                <option value="Mbway">Mbway</option>
                                                <option value="Iban">Iban</option>
                                                <option value="MB">MB</option>
                                                <option value="Visa">Visa</option>
                                                <option value="Paypal">Paypal</option>
                                            </select>

                                            <button
                                                @click="filterTransactionByType"
                                                class="btn btn-primary mt-3"
                                            >
                                                Filter Transactions
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body">
                            <p v-if="transactionsCountByType" class="mt-4">
                                <b>Count transactions by {{ paymentType }}: </b
                                >{{ transactionsCountByType }}
                            </p>
                            <div class="mt-3">
                                <label for="startDate"><b>Start Date:</b></label>
                                <input
                                    type="date"
                                    id="startDate"
                                    v-model="startDate"
                                    class="form-control"
                                />

                                <label for="endDate" class="mt-2"><b>End Date:</b></label>
                                <input
                                    type="date"
                                    id="endDate"
                                    v-model="endDate"
                                    class="form-control"
                                />

                                <button
                                    @click="filterTransactions"
                                    class="btn btn-primary mt-3 float-end"
                                >
                                    Filter
                                </button>
                            </div>

                            <p class="mt-3" v-if="transactionsSumBetweenDates">
                                <b
                                    >Sum of Transactions Between {{ startDate }} and
                                    {{ endDate }}:</b
                                >
                                {{ transactionsSumBetweenDates }}€
                            </p>
                            <div class="mt-3">
                                <p v-if="transactionsCountBetweenDates">
                                    <b
                                        >Count of Transactions Between {{ startDate }} and
                                        {{ endDate }}:</b
                                    >
                                    {{ transactionsCountBetweenDates }}€
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
