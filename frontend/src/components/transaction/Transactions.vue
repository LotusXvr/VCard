<script setup>
import { ref, onMounted } from "vue"
import axios from "axios"
import TransactionTable from "./TransactionTable.vue"

const props = defineProps({
    phone_number: {
        type: Number,
        default: null,
    },
})

const phone_number = ref(props.phone_number)
const transactions = ref([])

const loadTransactions = () => {
    // Change later when authentication is implemented
    axios
        .get("vcard/" + phone_number.value + "/transactions")
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
