<script setup>
import axios from "axios"
import { ref, onMounted } from "vue"
import { useToast } from "vue-toastification"
const toast = useToast()

const accountBalance = ref(null)
const error = ref(null)

const newTransaction = ref({
    payment_type: "",
    vcard: "",
    confirmation_code: "",
    payment_reference: "",
    value: "",
})

const verifyIntegrityOfTransaction = ref({
    type: "",
    reference: "",
    value: "",
})

const props = defineProps({
    phone_number: {
        type: Number,
        default: null,
    },
})

const emit = defineEmits(["createTransaction"])

const createTransaction = async () => {
    try {
        verifyIntegrityOfTransaction.value.type = newTransaction.value.payment_type
        verifyIntegrityOfTransaction.value.reference = newTransaction.value.payment_reference
        verifyIntegrityOfTransaction.value.value = parseFloat(newTransaction.value.value)
        const response = await axios.post(
            "https://dad-202324-payments-api.vercel.app/api/credit",
            verifyIntegrityOfTransaction.value,
        )
        console.log(response.data)
        toast.success(response.data.message)

        if (newTransaction.value.payment_type == "MBWAY") {
            newTransaction.value.payment_type = "VCARD"
        }
        newTransaction.value.vcard = props.phone_number
        emit("createTransaction", newTransaction.value)
    } catch (err) {
        error.value = err.message
        toast.error(err.message)
    }
}

const fetchAccountBalance = (phone_number) => {
    axios.get("vcards/" + phone_number).then((response) => {
        accountBalance.value = response.data.data.balance
    })
}

onMounted(() => {
    fetchAccountBalance(props.phone_number)
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
