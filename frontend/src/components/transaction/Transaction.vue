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
const inserting = (id) => {
    switch (id) {
        case -1:
            return "debit"
        case -2:
            return "credit"
        default:
            return "edit"
    }
}
const loadTransaction = async (id) => {
    originalValueStr = ""
    errors.value = null
    if (inserting(id) == "debit" || inserting(id) == "credit") {
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
    if (inserting(props.id) == "debit" || inserting(props.id) == "credit") {
        try {
            console.log(transactionToSave)
            const response = await axios.post("transactions", transactionToSave)
            toast.success(response.data.message)
            router.back()
        } catch (error) {
            errors.value = error.response.data.message
            toast.error(error.value)
        }
    } else {
        try {
            await axios.put("transactions/" + props.id, transactionToSave)
            toast.success("Transaction # " + transactionToSave.id + " updated successfully")
            router.back()
        } catch (error) {
            errors.value = error.response.data.message
            toast.error("Transaction #" + props.id + " - " + error.value)
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
