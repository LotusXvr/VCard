<script setup>
import { ref, inject } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import { useUserStore } from '../../stores/user'
import MoneyRequestDetail from './MoneyRequestDetail.vue'

const userStore = useUserStore()
const toast = useToast()
const socket = inject('socket')
const router = useRouter()
const errors = ref(null)
let originalValueStr = ''

const newMoneyRequest = () => {
    return {
        from_vcard: userStore.userPhoneNumber,
        value: '',
        to_vcard: '',
        description: '',
    }
}
const moneyRequest = ref(newMoneyRequest())

const save = async (editingMoneyRequest) => {
    try {
        await axios.post("moneyRequests", moneyRequest.value)
        toast.success('Money request saved successfully')
        socket.emit('requestMoney', {
            receiver: editingMoneyRequest.from_vcard,
            sender: editingMoneyRequest.to_vcard,
            amount: editingMoneyRequest.value
        })
        router.back()
    } catch (error) {
        toast.error(error.response.data.message)
    }
}

const cancel = () => {
    router.back()
}

</script>

<template>
    <money-request-detail :money-request="moneyRequest" :errors="errors" @save="save"
        @cancel="cancel"></money-request-detail>
</template>
