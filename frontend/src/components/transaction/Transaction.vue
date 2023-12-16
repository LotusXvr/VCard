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


const newVCard = () => {
  return {
    phone_number: null,
    name: '',
    balance: '',
    email: '',
    photo_url: null,
    password: '',
    password_confirmation: '',
    confirmation_code: ''
  }
}

const vcard = ref(newVCard())

const loadVCard = async () => {
  originalValueStr
  errors.value = null
  try {
    const response = await axios.get("vcards/" + userStore.userPhoneNumber)
    vcard.value = response.data.data
    originalValueStr = JSON.stringify(vcard.value)
  } catch (error) {
    console.log(error)
  }
}

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
      if (
        userStore.userType !== 'A' &&
        transactionToSave.payment_type != 'VCARD' &&
        transactionToSave.value >= 10
      ) {
        toast.info('You just received ' + response.data.spins + ' spins')
      }
      toast.success(response.data.message)

      if (transactionToSave.payment_type == 'VCARD') {
        socket.emit('moneySent', {
          receiver: transactionToSave.payment_reference,
          sender: userStore.userPhoneNumber,
          amount: transactionToSave.value
        })
      }

      if (userStore.userType == 'A') {
        socket.emit('moneySent', {
          receiver: transactionToSave.vcard,
          sender: userStore.userName,
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
  if (userStore.userType == 'V') {
    loadVCard()
  }
  categoriesRef.value = categoryStore.categories
})
</script>

<template>
  <transaction-detail :transaction="transaction" :errors="errors" :vcard="vcard" :inserting="inserting(id)" @save="save"
    @cancel="cancel"></transaction-detail>
</template>
