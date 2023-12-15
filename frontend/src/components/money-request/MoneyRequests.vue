<script setup>
import { ref, onMounted } from "vue"
import axios from "axios"
import MoneyRequestTable from "./MoneyRequestTable.vue"
import { useUserStore } from "../../stores/user"
import { useToast } from "vue-toastification"

const toast = useToast()
const userStore = useUserStore()

const moneyRequests = ref([])
const loadedMoneyRequests = ref(false)
const loadMoneyRequests = () => {
    loadedMoneyRequests.value = false
    axios
        .get("vcard/" + userStore.userPhoneNumber + "/moneyRequests")
        .then((response) => {
            moneyRequests.value = response.data
            console.log("mine: " + response.data)
            loadedMoneyRequests.value = true
        })
        .catch((error) => {
            console.log(error)
        })
}

const pendingRequests = ref([])
const loadedPendingRequests = ref(false)
const loadPendingRequests = () => {
    loadedPendingRequests.value = false
    axios
        .get("vcard/" + userStore.userPhoneNumber + "/moneyRequests/pending")
        .then((response) => {
            pendingRequests.value = response.data
            console.log("pending: " + response.data)
            loadedPendingRequests.value = true
        })
        .catch((error) => {
            console.log(error)
        })
}

const acceptRequest = (moneyRequest, confirmationCode) => {
    axios
        .post("moneyRequests/" + moneyRequest.id + "/update", {
            status: 1,
            confirmation_code: confirmationCode,
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

const newVCard = () => {
    return {
        phone_number: null,
        name: "",
        balance: "",
        email: "",
        photo_url: null,
        password: "",
        password_confirmation: "",
        confirmation_code: "",
    }
}

const vcard = ref(newVCard())

const loadVCard = async () => {
    try {
        const response = await axios.get("vcards/" + userStore.userPhoneNumber)
        vcard.value = response.data.data
    } catch (error) {
        toast.error(error.response.data.message)
    }
}

onMounted(() => {
    loadVCard()
    loadMoneyRequests()
    loadPendingRequests()
})
</script>

<template>
    <h3 class="mt-3">Money Requests</h3>

    <hr />

    <div v-if="loadedMoneyRequests && loadedPendingRequests">
        <MoneyRequestTable
            :pendingRequests="pendingRequests"
            :moneyRequests="moneyRequests"
            :vcard="vcard"
            @acceptRequest="acceptRequest"
            @rejectRequest="rejectRequest"
        >
        </MoneyRequestTable>
    </div>
    <div v-else>You have no money requests yet</div>
</template>
