<script setup>
import { ref, onMounted } from "vue"
import axios from "axios"
import TransactionTable from "./TransactionTable.vue"
import { useUserStore } from "../../stores/user"
import { useRouter } from "vue-router"

const userStore = useUserStore()
const transactions = ref([])
const categories = ref([])
const router = useRouter()

const props = defineProps({
    usersTitle: {
        type: String,
        default: "Transactions",
    },
})

const loadTransactions = () => {
    axios
        .get("vcard/" + userStore.userPhoneNumber + "/transactions")
        .then((response) => {
            transactions.value = response.data.data
            console.log(response.data)
        })
        .catch((error) => {
            console.log(error)
        })
}

const loadCategories = () => {
    axios
        .get("vcard/" + userStore.userPhoneNumber + "/category")
        .then((response) => {
            categories.value = response.data.data
            console.log(response.data)
        })
        .catch((error) => {
            console.log(error)
        })
}

const addTransaction = () => {
    router.push({ name: "NewTransaction" })
}

const editTransaction = (transaction) => {
    router.push({ name: "Transaction", params: { id: transaction.id } })
}

onMounted(() => {
    loadTransactions()
    loadCategories()
})
</script>

<template>
    <hr />
    <div v-if="transactions.length > 0">
        <TransactionTable
            :transactions="transactions"
            :showUserId="true"
            :categories="categories"
            @edit="editTransaction"
        ></TransactionTable>
    </div>
    <div v-else>No Transactions yet</div>
</template>
