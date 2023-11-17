<script setup>
import { ref } from "vue"
import TransactionDetail from "./TransactionDetail.vue"

import axios from "axios"

const error = ref(null)

const addTransaction = async (newVCard) => {
    console.log("addVCard() called")
    if (newVCard) {
        try {
            await axios.post(`$vcards`, newVCard)
        } catch (e) {
            error.value = e.response.data.errors // Capture and display API validation errors
        }
    }
}
</script>

<template>
    <transaction-detail @AddTransaction="addTransaction"></transaction-detail>
</template>
