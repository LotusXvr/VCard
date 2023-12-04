<script setup>
import { ref } from "vue"
import TransactionDetail from "./TransactionDetail.vue"
import { useRouter } from "vue-router";
import { useToast } from "vue-toastification"
import axios from "axios"

const toast = useToast()
const router = useRouter();

const error = ref(null)
let originalValueStr = ''
const props = defineProps({
    id: {
    type: Number,
    default: null
  }
})

const newTransaction = () => {
    return {
        payment_type: "",
        vcard: "",
        confirmation_code: "",
        payment_reference: "",
        value: "",
    }
}

const transaction = ref(newTransaction());
const errors = ref(null);
const inserting = (id) => !id || (id < 0)


const save = async (transactionToSave) => {
  errors.value = null
  if (inserting(props.id)) {
    try {
      const response = await axios.post('transactions', transactionToSave)
      transaction.value = response.data.data
      originalValueStr = JSON.stringify(transaction.value)
      toast.success('Transaction #' + transaction.value.id + ' was registered successfully.')
      router.back()
    } catch (error) {
      if (error.response.status == 422) {
        errors.value = error.response.data.errors
        toast.error('Transaction was not registered due to validation errors!')
      } else {
        toast.error('Transaction was not registered due to unknown server error!')
      }
    }
  } else {
    try {
      const response = await axios.put('transactions/' + props.id, transactionToSave)
      transaction.value = response.data.data
      originalValueStr = JSON.stringify(transaction.value)
      toast.success('Transaction #' + transaction.value.id + ' was updated successfully.')
      router.back()
    } catch (error) {
      if (error.response.status == 422) {
        errors.value = error.response.data.errors
        toast.error('Transaction #' + props.id + ' was not updated due to validation errors!')
      } else {
        toast.error('Transaction #' + props.id + ' was not updated due to unknown server error!')
      }
    }
  }
}

const cancel = () => {
  originalValueStr = JSON.stringify(transaction.value)
  router.back()
}

</script>

<template>
    <transaction-detail @createTransaction="createTransaction"></transaction-detail>
    <transaction-detail :transaction="trasaction" :errors="errors" :inserting="inserting(id)" @save="save" @cancel="cancel"></transaction-detail>
</template>
