<script setup>
import axios from "axios"
import { ref, onMounted } from "vue"
import { useToast } from "vue-toastification"
import { useUserStore } from "../../stores/user"

const toast = useToast()
const userStore = useUserStore()
const accountBalance = ref(null)
const categories = ref([])

const error = ref(null)

const newTransaction = ref({
    payment_type: "",
    vcard: "",
    confirmation_code: "",
    payment_reference: "",
    value: "",
})

const transactionVerifier = ref({
    type: "",
    reference: "",
    value: "",
})

const emit = defineEmits(["createTransaction"])

const createTransaction = async () => {
    try {
        if (newTransaction.value.payment_type != "VCARD") {
            transactionVerifier.value.type = newTransaction.value.payment_type
            transactionVerifier.value.reference = newTransaction.value.payment_reference
            transactionVerifier.value.value = parseFloat(newTransaction.value.value)
            const response = await axios.post(
                "https://dad-202324-payments-api.vercel.app/api/credit",
                transactionVerifier.value,
            )
            console.log(response.data)
            toast.success(response.data.status + " - " + response.data.message)
        }

        newTransaction.value.vcard = userStore.userPhoneNumber
        emit("createTransaction", newTransaction.value)
    } catch (err) {
        toast.error(err.response.data.status + " - " + err.response.data.message)
    }
}

const fetchAccountBalance = () => {
    axios.get("vcards/" + userStore.userPhoneNumber).then((response) => {
        accountBalance.value = response.data.data.balance
    })
}
function clearCategories() {
        categories.value = []
}

async function loadCategory() {
        try {
            const response = await axios.get('vcard/'+ userStore.userPhoneNumber +'/category')
            categories.value = response.data
            return categories.value
        } catch (error) {
            clearCategories()
            throw error
        }
}
onMounted(async () => {
    await loadCategory()
    fetchAccountBalance()
})
</script>

<template>
    <div>
        <h3 class="mt-5 mb-3">Transaction</h3>
        <div v-if="accountBalance !== null">
            <p>Account Balance: {{ accountBalance }}</p>
        </div>
        <hr />
        <form @submit.prevent="createTransaction">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="payment_type">Payment Type:</label>
                        <select v-model="newTransaction.payment_type" class="form-select" required>
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
                            v-model="newTransaction.payment_reference"
                            type="text"
                            id="transactionPaymentReferenec"
                            class="form-control"
                            required
                        />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="value">Amount:</label>
                        <input
                            v-model="newTransaction.value"
                            type="text"
                            id="transactionValue"
                            class="form-control"
                            required
                        />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="confirmation_code">Confirmation Code:</label>
                        <input
                            v-model="newTransaction.confirmation_code"
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
                        <select v-model="newTransaction.category" class="form-select" required>~
                            <option value="" selected>Sem Categoria</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id"> {{ category.name }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-3 d-flex justify-content-end" style="margin-top: 10px">
                <button type="button" class="btn btn-primary px-5" @click="createTransaction">
                    Send money
                </button>
                <!-- <button type="button" class="btn btn-light px-5" @click="cancel">Cancel</button> -->
            </div>
        </form>
    </div>
</template>
