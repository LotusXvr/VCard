<script setup>
import { ref } from "vue"
import TransactionDetail from "./TransactionDetail.vue"

import { useToast } from "vue-toastification"
const toast = useToast()
// import { useRouter } from "vue-router"
// const router = useRouter()

import axios from "axios"

const error = ref(null)

const props = defineProps ({
    phone_number: {
        type: Number,
        default: null,
    },
})



const createTransaction = async (newTransaction) => {
    try {
        const response = await axios.post("transactions", newTransaction)
        console.log(response.data)
        toast.success(response.data.message)
    } catch (err) {
        error.value = err.response.data.message
        toast.error(err.response.data.message)
    }
}





</script>

<template>
    <transaction-detail
        :phone_number="phone_number"
        @createTransaction="createTransaction"
    ></transaction-detail>
</template>
