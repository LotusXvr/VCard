<script setup>
import axios from "axios"
import { ref, onMounted, watch } from "vue"

const accountBalance = ref(null)

const newTransaction = ref({
    payment_type: "VCARD",
    vcard: "",
    confirmation_code: "",
    payment_reference: "",
    value: "",
})

const props = defineProps({
    phone_number: {
        type: Number,
        default: null,
    },
})

const emit = defineEmits(["createTransaction"])

const createTransaction = () => {
    newTransaction.value.vcard = props.phone_number
    emit("createTransaction", newTransaction.value)
}

const fetchAccountBalance = (vcard) => {
    axios.get("vcards/" + vcard).then((response) => {
        accountBalance.value = response.data.data.balance
    })
}

// Watch for changes in vcard and fetch account balance when it is filled
watch(
    () => newTransaction.value.vcard,
    (vcard) => {
        if (vcard.trim() !== "") {
            fetchAccountBalance(vcard)
        } else {
            accountBalance.value = null // Clear the balance if vcard is empty
        }
    },
)

onMounted(() => {
    // Focus the input when the component is mounted
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
