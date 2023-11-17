<script setup>
import { ref, onMounted } from "vue"

const newTransaction = ref({
    phone_number: "",
    confirmation_code: "",
    destination_phone_number: "",
    amount: "",
})
//const transactionInput = ref(null)

const emit = defineEmits(["addTransaction"])

onMounted(() => {
    // Focus the input when the component is mounted
    newTransaction.value.focus()
})
</script>

<template>
    <div>
        <h3 class="mt-5 mb-3">Transaction</h3>
        <hr>
        <form @submit.prevent="createTransaction">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="phone_number">Your phone number:</label>
                        <input
                            v-model="newTransaction.phone_number"
                            type="text"
                            id="transactionPhoneNumber"
                            class="form-control"
                            required
                        />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">Send money to...</label>
                        <input
                            v-model="newTransaction.destination_phone_number"
                            type="email"
                            id="transactionEmail"
                            class="form-control"
                            required
                        />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Amount:</label>
                        <input
                            v-model="newTransaction.amount"
                            type="text"
                            id="transactionName"
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

            <div class="mb-3 d-flex justify-content-end" style="margin-top: 10px;">
                <button type="button" class="btn btn-primary px-5" @click="createTransaction">
                    Send money
                </button>
                <button type="button" class="btn btn-light px-5" @click="cancel">Cancel</button>
            </div>
        </form>
    </div>
</template>
