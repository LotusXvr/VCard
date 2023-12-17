<script setup>
import { onMounted, ref, shallowRef } from "vue"
import axios from "axios"
import Chart from "chart.js/auto"

const startDate = ref("")
const endDate = ref("")
const transactionsSumBetweenDates = ref(0)
const today = new Date()
const year = today.getFullYear()
const month = String(today.getMonth() + 1).padStart(2, "0") 
const day = String(today.getDate()).padStart(2, "0")
const todayDateString = `${year}-${month}-${day}`
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

// VCARDS
const vcardCount = ref(0)
const vcardCountActive = ref(0)
const vcardBalanceSum = ref(0)
const vcardBalanceSumActive = ref(0)
// grafico
const vcardBalanceDistributionChartEl = ref(null)
const vcardBalanceDistributionChart = shallowRef(null)

// TRANSAÇOES
const paymentType = ref("Vcard")
const transactionsCount = ref(0)
const transactionsCountBetweenDates = ref(0)
const transactionsCountByType = ref(0)
const transactionsSum = ref(0)
const transactionsQuantityByMonth = ref(0)
const transactionsSumByMonth = ref(0)
const averageTransactionAmountByMonth = ref([])
// graficos
const transactionsByPaymentMethodChartEl = ref(null)
const transactionsByPaymentMethodChart = shallowRef(null)
const transactionsSumChartEl = ref(null)
const transactionsSumChart = shallowRef(null)
const transactionsQuantityChartEl = ref(null)
const transactionsQuantityChart = shallowRef(null)
const averageTransactionAmountChartEl = ref(null)
const averageTransactionAmountChart = shallowRef(null)

const getVCardStatistics = () => {
    axios
        .get("statistics/vcards")
        .then((response) => {
            vcardCount.value = response.data.vcardCount
            vcardCountActive.value = response.data.activeVCardCount
            vcardBalanceSum.value = response.data.vcardBalanceSum
            vcardBalanceSumActive.value = response.data.activeVCardBalanceSum

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

const getTransactionsStatistics = () => {
    axios
        .get("statistics/transactions")
        .then((response) => {
            transactionsSum.value = response.data.transactionsSum
            transactionsCount.value = response.data.transactionsCount
            transactionsSumByMonth.value = response.data.transactionsSumByMonth
            transactionsQuantityByMonth.value = response.data.transactionsCountByMonth
            averageTransactionAmountByMonth.value = response.data.averageTransactionAmounts
            transactionsQuantityByMonth.value = response.data.transactionsCountByMonth

            // GRAFICO DE QUANTIDADE DE TRANSAÇOES POR MES
            const months = transactionsQuantityByMonth.value.map(
                (entry) => `${monthNames[entry.month - 1]} ${entry.year}`,
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

            // GRAFICO DA SOMA DE TRANSAÇOES POR MES
            const monthsArray = transactionsSumByMonth.value.map(
                (entry) => `${monthNames[entry.month - 1]} ${entry.year}`,
            )
            const transactionsSumArray = transactionsSumByMonth.value.map((entry) => entry.sum)

            transactionsSumChart.value = new Chart(transactionsSumChartEl.value.getContext("2d"), {
                type: "line",
                data: {
                    labels: monthsArray,
                    datasets: [
                        {
                            label: "Sum of transactions' value",
                            data: transactionsSumArray,
                            backgroundColor: "rgba(255, 215, 0, 0.2)",
                            borderColor: "rgba(255, 215, 0, 1)",
                            borderWidth: 1,
                        },
                    ],
                },
            })

            // GRAFICO DE MEDIA DE VOLUME DE TRANSAÇOES POR MES
            const monthsWithYear = averageTransactionAmountByMonth.value.map(
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
                        labels: monthsWithYear,
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

            // GRAFICO DE QUANTIDADE DE TRANSAÇOES POR METODO DE PAGAMENTO
            const paymentMethods = response.data.paymentMethods
            const transactionCounts = response.data.transactionCounts

            const data = paymentMethods.map((method, index) => ({
                method,
                count: transactionCounts[index],
            }))

            const vcardIndex = data.findIndex((item) => item.method === "VCARD")

            if (vcardIndex !== -1) {
                data[vcardIndex].count /= 2
            }

            data.sort((a, b) => b.count - a.count)

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
}

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

onMounted(() => {
    getVCardStatistics()
    getTransactionsStatistics()
})
</script>

<template>
    <div class="container mt-4">
        <h1 class="text-center mb-4">Charts</h1>

        <div class="container mt-4">
            <div class="row">
                <div class="col-md-6">
                    <h4>Transactions Quantity</h4>
                    <canvas ref="transactionsQuantityChartEl"></canvas>
                </div>
                <div class="col-md-6">
                    <h4>Sum of transaction value amount</h4>
                    <canvas ref="transactionsSumChartEl"></canvas>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-6">
                    <h4>VCard Balance Distribution</h4>
                    <canvas ref="vcardBalanceDistributionChartEl"></canvas>
                </div>
                <div class="col-md-6">
                    <h4>Average Transaction Amount</h4>
                    <canvas ref="averageTransactionAmountChartEl"></canvas>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-6">
                    <h4>Transactions By Payment Method</h4>
                    <canvas ref="transactionsByPaymentMethodChartEl"></canvas>
                </div>
            </div>
        </div>

        <h1 class="text-center mb-4">Numbers</h1>

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
                            <p class="card-text">{{ vcardCountActive }}</p>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h4>Total Balance of All VCards</h4>
                            <p>{{ vcardBalanceSum }} €</p>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h4>Total Balance of All Active VCards</h4>
                            <p>{{ vcardBalanceSumActive }} €</p>
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
                            <p>{{ transactionsSum }} €</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5><b>Filter Transactions:</b></h5>
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
                                <p v-if="transactionsCountByType" class="mt-4">
                                    <b>Count transactions by {{ paymentType }}: </b
                                    >{{ transactionsCountByType }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body">
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
