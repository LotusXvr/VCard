<script setup>
import { ref } from "vue"

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
    <createVCard @AddVCard="addVCard"></createVCard>
</template>
