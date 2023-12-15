<script setup>
import { ref, onMounted, } from "vue"
import axios from "axios"
import MoneyRequestTable from "./MoneyRequestTable.vue"
import { useUserStore } from "../../stores/user"
import { useToast } from "vue-toastification"

const toast = useToast()
const userStore = useUserStore()

const moneyRequests = ref([])

const loadMoneyRequests = () => {
    axios
        .get("vcard/" + userStore.userPhoneNumber + "/moneyRequests")
        .then((response) => {
            moneyRequests.value = response.data
            console.log(response.data)
        })
        .catch((error) => {
            console.log(error)
        })
}

const acceptRequest = (moneyRequest) => {
    axios
        .post("moneyRequests/" + moneyRequest.id + "/update", {
            status: 1,
            confirmation_code: 123,
        })
        .then((response) => {
            console.log(response.data)
            toast.success(response.data.message)
            loadMoneyRequests()
        })
        .catch((error) => {
            console.log(error)
            toast.error(error.response.data.message)
        })
}

const rejectRequest = (moneyRequest) => {
    axios
        .post("moneyRequests/" + moneyRequest.id + "/update", {
            status: 0,
            confirmation_code: 123,
        })
        .then((response) => {
            console.log(response.data)
            toast.success(response.data.message)
            loadMoneyRequests()
        })
        .catch((error) => {
            console.log(error)
            toast.error(error.response.data.message)
        })
}

onMounted(() => {
    loadMoneyRequests()
})
</script>

<template>
    <h3 class="mt-3">Money Requests</h3>

    <hr />

    <div v-if="moneyRequests.length > 0">
        <MoneyRequestTable
            :moneyRequests="moneyRequests"
            @acceptRequest="acceptRequest"
            @rejectRequest="rejectRequest"
        >
        </MoneyRequestTable>
    </div>
    <div v-else>You have no money requests yet</div>
</template>
