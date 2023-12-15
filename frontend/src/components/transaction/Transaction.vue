<script setup>
import { onMounted, ref, watch, inject } from 'vue'
import TransactionDetail from './TransactionDetail.vue'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import { useCategoryStore } from '../../stores/category'
const categoryStore = useCategoryStore()
import { useUserStore } from '../../stores/user'

const userStore = useUserStore()

const toast = useToast()
const socket = inject('socket')
const router = useRouter()
const errors = ref(null)
const categoriesRef = ref([])
let originalValueStr = ''

const props = defineProps({
  id: {
    type: Number,
    default: null
  }
})

const newTransaction = () => {
  return {
    payment_type: '',
    vcard: '',
    confirmation_code: '',
    payment_reference: '',
    value: '',
    type: ''
  }
}
const transaction = ref(newTransaction())
const inserting = (id) => {
  switch (id) {
    case -1:
      return 'debit'
    case -2:
      return 'credit'
    default:
      return 'edit'
  }
}
const loadTransaction = async (id) => {
  originalValueStr = ''
  errors.value = null
  if (inserting(id) == 'debit' || inserting(id) == 'credit') {
    transaction.value = newTransaction()
  } else {
    try {
      const response = await axios.get('transactions/' + id)
      transaction.value = response.data.data
      originalValueStr = JSON.stringify(transaction.value)
    } catch (error) {
      console.error('Error loading transaction:', error)
    }
  }
}

const save = async (transactionToSave) => {
  errors.value = null
  if (inserting(props.id) == 'debit' || inserting(props.id) == 'credit') {
    try {
      if (transactionToSave.type == 'C') {
        if (transactionToSave.payment_type != 'VCARD') {
          // isto é necessario visto a logica do crédito é oposta á de debito portanto existe esta troca
          // para facilitar a execuçao do codigo por parte da api
          const paymentReferenceToVCard = transactionToSave.payment_reference
          transactionToSave.payment_reference = transactionToSave.vcard
          transactionToSave.vcard = paymentReferenceToVCard
        } else {
          transactionToSave.vcard = userStore.userPhoneNumber
        }
      }
      if (transactionToSave.type == 'D') {
        transactionToSave.vcard = userStore.userPhoneNumber
      }

      const response = await axios.post('transactions', transactionToSave)
      if (transactionToSave.payment_type != 'VCARD' && transactionToSave.value > 10) {
        toast.info('You just received ' + response.data.spins + ' spins')
      }
      toast.success(response.data.message)

      if (transactionToSave.payment_type == 'VCARD') {
        // No lado do cliente
        socket.emit('moneySent', {
          receiver: transactionToSave.payment_reference,
          sender: userStore.userPhoneNumber,
          amount: transactionToSave.value
        })
      }

      router.back()
    } catch (error) {
      errors.value = error.response.data.message
      toast.error(error.response.data.message)
    }
  } else {
    try {
      const response = await axios.put('transactions/' + props.id, transactionToSave)
      console.log(response.data)
      toast.success('Transaction # ' + transactionToSave.id + ' updated successfully')
      router.back()
    } catch (error) {
      errors.value = error.response.data.message
      toast.error('Transaction #' + props.id + ' - ' + error.response.data.message)
    }
  }
}

const cancel = () => {
  originalValueStr = JSON.stringify(transaction.value)
  router.back()
}

watch(
  () => props.id,
  (newValue) => {
    loadTransaction(newValue)
  },
  { immediate: true }
)

const loadCategories = async () => {
  try {
    await categoryStore.loadCategory()
  } catch (error) {
    console.log(error)
  }
}

onMounted(() => {
  loadCategories()
  categoriesRef.value = categoryStore.categories
})
</script>

<template>
  <transaction-detail
    :transaction="transaction"
    :errors="errors"
    :inserting="inserting(id)"
    @save="save"
    @cancel="cancel"
  ></transaction-detail>
</template>
