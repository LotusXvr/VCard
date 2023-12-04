<script setup>
import { ref, onMounted } from "vue"
import axios from "axios"
import TransactionTable from "./TransactionTable.vue"
import { useUserStore } from "../../stores/user"
import { useRouter } from 'vue-router'

const userStore = useUserStore()
const transactions = ref([])
const router = useRouter()

const props = defineProps({
  usersTitle: {
    type: String,
    default: 'Transactions'
  }
})

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
    console.log("transacoes"+transactions.value)
}

const addTransaction = () => {
    router.push({ name: 'NewTransaction' })
}

const editTransaction = (transaction) => {
    router.push({ name: 'Transaction', params: { id: transaction.id } })
}

onMounted(() => {
    loadTransactions()
})

</script>

<template>
    <div class="d-flex justify-content-between">
      <div class="mx-2">
        <h3 class="mt-4">{{ transactionsTitle }}</h3>
      </div>
    </div>
    <hr />
    <div class="mb-3 d-flex justify-content-between flex-wrap">
      <div class="mx-2 mt-2 flex-grow-1 filter-div"></div>
      <div class="mx-2 mt-2">
        <router-link class="nav-link w-100 me-3" :to="{ name: 'NewTransaction' }">
          <button type="button" class="btn btn-success px-4 btn-addtransaction" @click="addTransaction">
            <i class="bi bi-xs bi-plus-circle"></i>&nbsp; Add Transaction
          </button>
        </router-link>
      </div>
    </div>
    <div v-if="transactions.length > 0">
      <TransactionTable :transactions="transactions" :showUserId="true" @edit="editTransaction"></TransactionTable>
      </div>
      <div v-else>No Transactions yet</div>
  </template>