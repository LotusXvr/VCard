<script setup>
import { ref, onMounted, inject } from "vue"
import axios from "axios"
import MoneyRequestTable from "./MoneyRequestTable.vue"
import { useUserStore } from "../../stores/user"
import { useToast } from "vue-toastification"

const toast = useToast()
const userStore = useUserStore()
const socket = inject('socket')
const moneyRequests = ref([])
let rejectedBy = 'S'
const loadedMoneyRequests = ref(false)
const loadMoneyRequests = () => {
    loadedMoneyRequests.value = false
    axios
        .get("vcard/" + userStore.userPhoneNumber + "/moneyRequests")
        .then((response) => {
            moneyRequests.value = response.data
            loadedMoneyRequests.value = true
        })
        .catch((error) => {
            console.log(error)
        })
}

socket.on('acceptMoneyNotification', () => {
    loadPendingRequests()
})

socket.on('requestMoneyNotification', () => {
    loadMoneyRequests()
})

socket.on('rejectMoneyNotification', () => {
    loadPendingRequests()
    loadMoneyRequests()
})

const pendingRequests = ref([])
const loadedPendingRequests = ref(false)
const loadPendingRequests = () => {
    loadedPendingRequests.value = false
    axios
        .get("vcard/" + userStore.userPhoneNumber + "/moneyRequests/pending")
        .then((response) => {
            pendingRequests.value = response.data
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
            toast.success(response.data.message)
            socket.emit('acceptMoney', {
            receiver: moneyRequest.from_vcard,
            sender: moneyRequest.to_vcard,
            amount: moneyRequest.amount
            })
            loadMoneyRequests()
        })
        .catch((error) => {
            console.log(error)
            toast.error(error.response.data.message)
        })
}

const rejectRequest = (moneyRequest) => {
    if(userStore.userPhoneNumber == moneyRequest.from_vcard){
        rejectedBy = 'R'
    }
    
    axios
        .post("moneyRequests/" + moneyRequest.id + "/update", {
            status: 0,
        })
        .then((response) => {
            toast.success(response.data.message)
            socket.emit('rejectMoney', {
            receiver: moneyRequest.from_vcard,
            sender: moneyRequest.to_vcard,
            amount: moneyRequest.amount,
            whoRejected : rejectedBy
            })
            toast.success(response.data.message)
            loadMoneyRequests()
            loadPendingRequests()
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
