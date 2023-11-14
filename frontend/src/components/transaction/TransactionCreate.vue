<script setup>
import { ref, onMounted } from "vue"

const newTransaction = ref({
    id: "",
    phone_number: "",
    password: "",
    name: "",
    email: "",
    confirmation_code: "",
})
//const transactionInput = ref(null)

const emit = defineEmits(["addTransaction"])

const createTransaction = () => {
    // Create a new transaction
    emit("addtransaction", {
        phone_number: newTransaction.value.phone_number,
        password: newTransaction.value.password,
        name: newTransaction.value.name,
        email: newTransaction.value.email,
        confirmation_code: newTransaction.value.confirmation_code,
        balance: "0.00",
        blocked: 0,
        max_debit: "5000.00",
    })
    console.log("Calling addtransaction()")
    // Clear the input
    newTransaction.value = {
        phone_number: "",
        password: "",
        name: "",
        email: "",
        confirmation_code: "",
    }
}

onMounted(() => {
    // Focus the input when the component is mounted
})
</script>

<template>
    <div>
        <h2>Transaction</h2>
        <form @submit.prevent="createTransaction">
            <div class="form-group">
                <label for="phone_number">Phone Number:</label>
                <input
                    v-model="newTransaction.phone_number"
                    type="text"
                    id="transactionPhoneNumber"
                    class="form-control"
                    required
                />
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input
                    v-model="newTransaction.password"
                    type="password"
                    id="transactionPassword"
                    class="form-control"
                    required
                />
            </div>

            <div class="form-group">
                <label for="name">Name:</label>
                <input
                    v-model="newTransaction.name"
                    type="text"
                    id="transactionName"
                    class="form-control"
                    required
                />
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input
                    v-model="newTransaction.email"
                    type="email"
                    id="transactionEmail"
                    class="form-control"
                    required
                />
            </div>

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

            <button type="submit" class="btn btn-primary" @click="addTransaction">Create transaction</button>
        </form>
    </div>
</template>
