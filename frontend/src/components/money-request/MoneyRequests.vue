<script setup>
import { ref, onMounted} from "vue"
import axios from "axios"
import MoneyRequestTable from "./MoneyRequestTable.vue"
import { useUserStore } from "../../stores/user"

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

onMounted(() => {
    loadMoneyRequests()
})
</script>

<template>
    <div  v-if="moneyRequests.length > 0">
        <MoneyRequestTable :moneyRequests="moneyRequests" />
    </div>
    <div v-else>You have no money requests yet</div>
</template>
