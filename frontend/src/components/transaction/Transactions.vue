<script setup>
import { ref, onMounted } from "vue"
import axios from "axios"
import TransactionTable from "./TransactionTable.vue"
import { useUserStore } from "../../stores/user"
const userStore = useUserStore()

const transactions = ref([])

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

onMounted(() => {
    loadTransactions()
})
</script>

<template>
    <!-- Render TransactionTable only if transactions array is filled -->
    <div v-if="transactions.length > 0">
        <TransactionTable :transactions="transactions"></TransactionTable>
    </div>
    <!-- Add a loading indicator or message if transactions array is empty -->
    <div v-else>Loading transactions...</div>
</template>
