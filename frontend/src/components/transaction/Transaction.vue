<script setup>
import { onMounted, ref, watch } from "vue"
import TransactionDetail from "./TransactionDetail.vue"
import { useRouter } from "vue-router"
import { useToast } from "vue-toastification"
import axios from "axios"
import { useCategoryStore } from "../../stores/category"
const categoryStore = useCategoryStore()

const toast = useToast()
const router = useRouter()
const errors = ref(null)
const inserting = (id) => !id || id <= 0
const error = ref(null)
const categoriesRef = ref([])
let originalValueStr = ""

const props = defineProps({
    id: {
        type: Number,
        default: null,
    },
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
const transaction = ref(newTransaction())

const loadTransaction = async (id) => {
    originalValueStr = ""
    errors.value = null
    if (inserting(id)) {
        transaction.value = newTransaction()
    } else {
        try {
            const response = await axios.get("transactions/" + id)
            transaction.value = response.data.data
            originalValueStr = JSON.stringify(transaction.value)
        } catch (error) {
            console.error("Error loading transaction:", error)
        }
    }
}

const save = async (transactionToSave) => {
    errors.value = null
    if (inserting(props.id)) {
        try {
            const response = await axios.post("transactions", transactionToSave)
            toast.success(response.data.message)
            router.back()
        } catch (error) {
            errors.value = error.response.data.message
            if (error.response.status == 422) {
                toast.error("422: " + errors.value)
            } else {
                toast.error(errors.value)
            }
        }
    } else {
        try {
            await axios.put("transactions/" + props.id, transactionToSave)
            toast.success("Transaction # "+transactionToSave.id+" updated successfully")
            router.back()
        } catch (error) {
            errors.value = error.response.data.message
            if (error.response.status == 422) {
                toast.error("422: Transaction #" + props.id + " - " + error.value)
            } else {
                toast.error("Transaction #" + props.id + " - " + error.value)
            }
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
    { immediate: true },
)

const loadCategories= async () => {
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
