<script setup>
import { ref, onMounted } from "vue"

const newVCard = ref({
    id: "",
    phone_number: "",
    password: "",
    name: "",
    email: "",
    confirmation_code: "",
})
//const vcardInput = ref(null)

const emit = defineEmits(["addVCard"])

const createVCard = () => {
    // Create a new vCard
    emit("addVCard", {
        phone_number: newVCard.value.phone_number,
        password: newVCard.value.password,
        name: newVCard.value.name,
        email: newVCard.value.email,
        confirmation_code: newVCard.value.confirmation_code,
        balance: "0.00",
        blocked: 0,
        max_debit: "5000.00",
    })
    // Clear the input
    newVCard.value = {
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
        <h2>Create a New VCard</h2>
        <form @submit.prevent="createVCard">
            <div class="form-group">
                <label for="phone_number">Phone Number:</label>
                <input
                    v-model="newVCard.phone_number"
                    type="text"
                    id="VCardPhoneNumber"
                    class="form-control"
                    required
                />
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input
                    v-model="newVCard.password"
                    type="password"
                    id="VCardPassword"
                    class="form-control"
                    required
                />
            </div>

            <div class="form-group">
                <label for="name">Name:</label>
                <input
                    v-model="newVCard.name"
                    type="text"
                    id="VCardName"
                    class="form-control"
                    required
                />
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input
                    v-model="newVCard.email"
                    type="email"
                    id="VCardEmail"
                    class="form-control"
                    required
                />
            </div>

            <div class="form-group">
                <label for="confirmation_code">Confirmation Code:</label>
                <input
                    v-model="newVCard.confirmation_code"
                    type="text"
                    id="VCard_confirmation_code"
                    class="form-control"
                    required
                />
            </div>

            <button type="submit" class="btn btn-primary">Create VCard</button>
        </form>
    </div>
</template>
