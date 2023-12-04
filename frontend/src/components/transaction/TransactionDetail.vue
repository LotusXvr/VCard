<script setup>
import axios from "axios"
import { ref, watch, computed, onMounted } from "vue"
import { useToast } from "vue-toastification"
import { useUserStore } from "../../stores/user"

const toast = useToast()
const userStore = useUserStore()
const accountBalance = ref(null)
const categoriesRef = ref([])

const props = defineProps({
    transaction: {
      type: Object,
      required: true
    },
    inserting: {
        type: Boolean,
        default: false,
    },
    errors: {
      type: Object,
      required: false,
    },
    categories: {
      type: Array,
      required: true,
    },
  })

const editingTransaction = ref(props.transaction)

const transactionVerifier = ref({
    type: "",
    reference: "",
    value: "",
})

const emit = defineEmits(['save', 'cancel'])

watch(
    () => props.transaction,
    (newTransaction) => {
      editingTransaction.value = newTransaction
    },
    { immediate: true }
)

const transactionTitle = computed( () => {
    if (!editingTransaction.value) {
        return ''
    } 
      return props.inserting == 'insert' ? 'New Transaction' : 'Task #' + editingTransaction.value.id
  })

const save = async () => {
    const newTransaction = editingTransaction.value;
    try {
        console.log(newTransaction)
        if (newTransaction.payment_type != "VCARD") {
            transactionVerifier.value.type = newTransaction.payment_type
            transactionVerifier.value.reference = newTransaction.payment_reference
            transactionVerifier.value.value = parseFloat(newTransaction.value)
            const response = await axios.post(
                "https://dad-202324-payments-api.vercel.app/api/credit",
                transactionVerifier.value,
            )
            
            toast.success(response.data.status + " - " + response.data.message)
        }
        newTransaction.vcard = userStore.userPhoneNumber
        emit("save", newTransaction)
    } catch (err) {
        toast.error(err.response.data.status + " - " + err.response.data.message)
    }
}

const cancel = () => {
emit('cancel', editingTransaction.value)
}

onMounted(() => {
  categoriesRef.value = props.categories
})

</script>

<template>
    <h3 class="mt-5 mb-3">{{ transactionTitle }}</h3>
    <div>
        <h3 class="mt-5 mb-3">Transaction</h3>
        <div v-if="accountBalance !== null">
            <p>Account Balance: {{ accountBalance }}</p>
        </div>
        <hr />
        <form @submit.prevent="save">
            <div class="row" v-if="inserting">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="payment_type">Payment Type:</label>
                        <select v-model="editingTransaction.payment_type" class="form-select" required>
                            <option value="VCARD">VCARD</option>
                            <option value="MBWAY">MBWAY</option>
                            <option value="IBAN">IBAN</option>
                            <option value="MB">MB</option>
                            <option value="VISA">VISA</option>
                            <option value="PAYPAL">PAYPAL</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="payment_reference">Send money to...</label>
                        <input
                            v-model="editingTransaction.payment_reference"
                            type="text"
                            id="transactionPaymentReferenec"
                            class="form-control"
                            required
                        />
                    </div>
                </div>
            </div>

            <div class="row">
                <div v-if="inserting" class="col-md-6">
                    <div class="form-group">
                        <label for="value">Amount:</label>
                        <input
                            v-model="editingTransaction.value"
                            type="text"
                            id="transactionValue"
                            class="form-control"
                            required
                        />
                    </div>
                </div>
                <div v-if="inserting" class="col-md-6">
                    <div class="form-group">
                        <label for="confirmation_code">Confirmation Code:</label>
                        <input
                            v-model="editingTransaction.confirmation_code"
                            type="text"
                            id="transaction_confirmation_code"
                            class="form-control"
                            required
                        />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="confirmation_code">Category:</label>
                        <select v-model="editingTransaction.category" class="form-select" required>~
                            <option value="" selected>{{editingTransaction.category}}</option>
                            <option v-for="category in categoriesRef.value" :key="category.id" :value="category.id"> {{ category.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
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

            <div class="mb-3 d-flex justify-content-end" style="margin-top: 10px">
                <button
                    type="button"
                    class="btn btn-light px-5"
                    @click="cancel"
                >Cancel</button>
                <button type="button" class="btn btn-primary px-5" @click="save">
                    Save
                </button>
            </div>
        </form>
    </div>
</template>
