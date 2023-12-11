<script setup>
import { ref, onMounted, watch, nextTick } from "vue"
import axios from "axios"
import TransactionTable from "./TransactionTable.vue"
import { useUserStore } from "../../stores/user"
import { useRouter } from "vue-router"
import { Bootstrap5Pagination } from "laravel-vue-pagination"


const userStore = useUserStore()
const transactions = ref([])
const router = useRouter()
const paginationData = ref({})

const props = defineProps({
    usersTitle: {
        type: String,
        default: "Transactions",
    },
})

const loadTransactions = (page = 1) => {
    axios
        .get("vcard/" + userStore.userPhoneNumber + "/transactions", {
            params: {
                page: page,
            },
        })
        .then((response) => {
            console.log(response.data)
            console.log(response.data.data)
            paginationData.value = response.data
            transactions.value = response.data.data
            filteredTransactions.value = transactions.value
        })
        .catch((error) => {
            console.log(error)
        })
}

const editTransaction = (transaction) => {
    router.push({ name: "Transaction", params: { id: transaction.id } })
}

// Reactive filter properties
const startDate = ref(null)
const endDate = ref(null)
const type = ref(null)
const method = ref(null)
const filtered = ref(false)
const filteredTransactions = ref([])


// Method to apply filters
const applyFilters = () => {
    filteredTransactions.value = transactions.value.filter((transaction) => {
        // Check if transaction is between start and end date
        const transactionDate = new Date(transaction.datetime)
        const isAfterStartDate = startDate.value
            ? transactionDate >= new Date(startDate.value)
            : true
        const isBeforeEndDate = endDate.value ? transactionDate <= new Date(endDate.value) : true
        // Check if transaction type is correct
        const isCorrectType = type.value ? transaction.type === type.value : true
        // Check if transaction method is correct
        const isCorrectMethod = method.value ? transaction.payment_type === method.value : true
        return isAfterStartDate && isBeforeEndDate && isCorrectType && isCorrectMethod
    })
    filtered.value = true
}

// Method to clear filters
const clearFilters = () => {
    startDate.value = null
    endDate.value = null
    type.value = null
    method.value = null
    filteredTransactions.value = transactions.value
    filtered.value = false
}

onMounted(() => {
    loadTransactions()
})
</script>

<template>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <!-- Filter inputs -->
                <div class="mb-3">
                    <label for="startDate" class="form-label">Start Date:</label>
                    <input type="date" class="form-control" v-model="startDate" />
                </div>
            </div>

            <div class="col-md-3">
                <!-- Filter inputs -->
                <div class="mb-3">
                    <label for="endDate" class="form-label">End Date:</label>
                    <input type="date" class="form-control" v-model="endDate" />
                </div>
            </div>
            <div class="col-md-3">
                <!-- Filter inputs -->
                <div class="mb-3">
                    <label for="type" class="form-label">Type:</label>
                    <select class="form-select" v-model="type">
                        <option value="">All</option>
                        <option value="C">Credit</option>
                        <option value="D">Debit</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <!-- Filter inputs -->
                <div class="mb-3">
                    <label for="method" class="form-label">Method:</label>
                    <select class="form-select" v-model="method">
                        <option value="">All</option>
                        <option value="VCARD">VCARD</option>
                        <option value="MBWAY">MBWAY</option>
                        <option value="IBAN">IBAN</option>
                        <option value="MB">MB</option>
                        <option value="VISA">VISA</option>
                        <option value="PAYPAL">PAYPAL</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <!-- Button to apply filters -->
                <div class="mb-3">
                    <button @click="applyFilters" class="btn btn-primary">Apply Filters</button>
                </div>
            </div>

            <div class="col-md-3">
                <!-- Button to clear filters and show all transactions -->
                <div class="mb-3">
                    <button @click="clearFilters" class="btn btn-secondary">Clear Filters</button>
                </div>
            </div>
        </div>
    </div>

    <hr />
    <div v-if="filteredTransactions.length > 0">
        <TransactionTable :transactions="filteredTransactions" :filtered="filtered" @edit="editTransaction">
        </TransactionTable>
        <Bootstrap5Pagination :data="paginationData" @pagination-change-page="loadTransactions" :limit="3">
        </Bootstrap5Pagination>

    </div>
    <div v-else>No Transactions yet</div>
</template>
