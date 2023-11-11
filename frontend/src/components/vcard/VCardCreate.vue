<script setup>
import { ref, defineEmits } from "vue"
import axios from "axios"

const newVCard = ref({
    phone_number: "",
    password: "",
    name: "",
    email: "",
    confirmation_code: "",
})

const createVCard = async () => {
    console.log("addVCard() called with:", newVCard.value);
    try {
        await axios.post("vcards", newVCard.value);
        console.log("VCard created successfully");
        // success.value = "VCard created successfully"; 
        // error.value = null;
    } catch (e) {
        console.error("Error creating VCard:", e);
        // success.value = null;
        // error.value = e.response.data.errors;
    }
};

// const emit = defineEmits(["addVCard"]) // Fix the case here

// const addVCard = () => {
//     console.log("createVCard() called");

//     // Emitir o evento para adicionar o VCard
//     emit("addVCard", newVCard.value); // Fix the case here

//     console.log("VCard created successfully: ", newVCard.value);

//     // Limpar os inputs
//     newVCard.value = {
//         phone_number: "",
//         password: "",
//         name: "",
//         email: "",
//         confirmation_code: "",
//     }
// }
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
