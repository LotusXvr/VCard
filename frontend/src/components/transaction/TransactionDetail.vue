<script setup>
import { ref, watch, computed, onMounted } from "vue"
import { useToast } from "vue-toastification"
import { useCategoryStore } from "../../stores/category"

const toast = useToast()
const accountBalance = ref(null)
const categoryStore = useCategoryStore()

const props = defineProps({
    transaction: {
        type: Object,
        required: true,
    },
    inserting: {
        type: String,
        required: true,
    },
    errors: {
        type: Object,
        required: false,
    },
    categories: {
        type: Array,
        required: false,
    },
})

const editingTransaction = ref(props.transaction)

const emit = defineEmits(["save", "cancel"])

watch(
    () => props.transaction,
    (newTransaction) => {
        editingTransaction.value = newTransaction
    },
    { immediate: true },
)

const transactionTitle = computed(() => {
    if (!editingTransaction.value) {
        return ""
    }
    switch (props.inserting) {
        case "debit":
            return "New Transaction"
        case "credit":
            return "New Credit"
        default:
            return "Transaction #" + editingTransaction.value.id
    }
})

const save = async () => {
    if (validateValue() != true) {
        toast.error(validateValue())
        return
    }

    editingTransaction.value.value = parseFloat(editingTransaction.value.value).toFixed(2)

    // isto faz um deepcopy da variavel edittingTransaction para que se possa fazer
    // mutações á variavel newTransaction sem alterar os valores do form
    const newTransaction = { ...editingTransaction.value }

    if (props.inserting === "debit") {
        newTransaction.type = "D"
        if (!validateReference(newTransaction.payment_reference)) {
            toast.error("Invalid payment reference")
            return
        }
    }

    if (props.inserting === "credit") {
        newTransaction.type = "C"
        if (!validateReference(newTransaction.vcard)) {
            toast.error("Invalid payment reference")
            return
        }
    }

    console.log(newTransaction)
    emit("save", newTransaction)
}

const validateReference = (reference) => {
    switch (editingTransaction.value.payment_type) {
        case "MBWAY":
            return /^9\d{8}$/.test(reference)
        case "PAYPAL":
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(reference)
        case "IBAN":
            console.log(reference)
            return /^[A-Z]{2}\d{23}$/.test(reference)
        case "MB":
            return /^\d{5}-\d{9}$/.test(reference)
        case "VISA":
            return /^4\d{15}$/.test(reference)
        case "VCARD":
            return true
        default:
            return false
    }
}

const validateValue = () => {
    const value = parseFloat(editingTransaction.value.value)

    if (isNaN(value) || value <= 0 || value >= 100000) {
        return "Invalid value"
    }

    // Check the maximum value based on payment type
    const paymentType = editingTransaction.value.payment_type
    switch (paymentType) {
        case "MBWAY":
            if (value > 50) return "MBWAY transactions cannot exceed 50€"
            break
        case "PAYPAL":
            if (value > 100) return "PAYPAL transactions cannot exceed 100€"
            break
        case "IBAN":
            if (value > 1000) return "IBAN transactions cannot exceed 1000€"
            break
        case "MB":
            if (value > 500) return "MB transactions cannot exceed 500€"
            break
        case "VISA":
            if (value > 200) return "VISA transactions cannot exceed 200€"
            break
        case "VCARD":
            break
        default:
            return "Invalid payment type"
    }

    return true // Indicates a valid transaction value
}


const cancel = () => {
    emit("cancel", editingTransaction.value)
}

const loadCategories = async () => {
    if (props.inserting !== "credit") {
        try {
            await categoryStore.loadCategory()
        } catch (error) {
            console.log(error)
        }
    }
}

const creditShowFromFieldWhenNotVCard = computed(() => {
    return editingTransaction.value.payment_type !== "VCARD"
})

onMounted(() => {
    loadCategories()
    })
</script>

<template>
    <h3 class="mt-5 mb-3">{{ transactionTitle }}</h3>
    <div>
        <div v-if="accountBalance !== null">
            <p>Account Balance: {{ accountBalance }}</p>
        </div>
        <hr />
        <form @submit.prevent="save">
            <div class="row" v-if="inserting === 'debit' || inserting === 'credit'">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="payment_type">Payment Type:</label>
                        <select
                            v-model="editingTransaction.payment_type"
                            class="form-select"
                            required
                        >
                            <option value="VCARD">VCARD</option>
                            <option value="MBWAY">MBWAY</option>
                            <option value="IBAN">IBAN</option>
                            <option value="MB">MB</option>
                            <option value="VISA">VISA</option>
                            <option value="PAYPAL">PAYPAL</option>
                        </select>
                    </div>
                </div>
                <div v-if="inserting === 'debit'" class="col-md-6">
                    <div class="form-group">
                        <label for="payment_reference">Send money to...</label>
                        <input
                            v-model="editingTransaction.payment_reference"
                            type="text"
                            id="transactionPaymentReference"
                            class="form-control"
                            required
                        />
                    </div>
                </div>
                <div v-if="inserting === 'credit'" class="col-md-6">
                    <div class="form-group">
                        <label for="vcard">Send money to...</label>
                        <input
                            v-model="editingTransaction.payment_reference"
                            type="text"
                            id="transactionPaymentReference"
                            class="form-control"
                            required
                        />
                    </div>
                </div>
            </div>

            <div class="row">
                <div v-if="inserting === 'debit' || inserting === 'credit'" class="col-md-6">
                    <div class="form-group">
                        <label for="value">Amount:</label>
                        <input
                            v-model="editingTransaction.value"
                            type="text"
                            id="transactionValue"
                            class="form-control"
                            autocomplete="off"
                            required
                        />
                    </div>
                </div>
                <div
                    v-if="inserting === 'credit' && creditShowFromFieldWhenNotVCard"
                    class="col-md-6"
                >
                    <div class="form-group">
                        <label for="payment_reference">From...</label>
                        <input
                            v-model="editingTransaction.vcard"
                            type="text"
                            id="transactionPaymentReference"
                            class="form-control"
                            required
                        />
                    </div>
                </div>
                <div v-if="inserting === 'debit'" class="col-md-6">
                    <div class="form-group">
                        <label for="confirmation_code">Confirmation Code:</label>
                        <input
                            v-model="editingTransaction.confirmation_code"
                            type="text"
                            id="transaction_confirmation_code"
                            class="form-control"
                            autocomplete="off"
                            required
                        />
                    </div>
                </div>

                <hr style="margin-top: 20px" />
                <div v-if="inserting != 'credit'" class="col-md-6">
                    <div class="form-group">
                        <label for="confirmation_code">Category:</label>
                        <select
                            v-model="editingTransaction.category_id"
                            class="form-select"
                            required
                        >
                            <option :value="undefined">-- Sem Categoria --</option>
                            <option
                                v-for="category in categoryStore.categories"
                                :key="category.id"
                                :value="category.id"
                            >
                                {{ category.name }}
                            </option>
                        </select>
                    </div>
                </div>
                <div v-if="inserting != 'credit'" class="col-md-6">
                    <div class="form-group">
                        <label for="confirmation_code">Description:</label>
                        <input
                            v-model="editingTransaction.description"
                            type="text"
                            id="transaction_description"
                            class="form-control"
                        />
                    </div>
                </div>
            </div>
            <div v-if=" inserting !== 'credit' " class="mx-2 mt-2">
                <router-link class="nav-link w-100 me-3" :to="{ name: 'NewCategory' }">
                    <button type="button" class="btn btn-success px-4 btn-addtask" @click="addCategory">
                        <i class="bi bi-xs bi-plus-circle"></i>&nbsp; Add Category
                    </button>
                </router-link>
            </div>
            <div class="mb-3 d-flex justify-content-end" style="margin-top: 25px">
                <button type="button" class="btn btn-primary px-5" @click="save">Save</button>
                <button type="button" class="btn btn-light px-5" @click="cancel">Cancel</button>
            </div>
        </form>
    </div>
</template>
