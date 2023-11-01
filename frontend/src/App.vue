<script setup>
import { ref, onMounted } from "vue"
import axios from "axios"
import config from "./utils/config"

import createVCard from "./components/createVCard.vue";

const vcards = ref([])
const users = ref([])
const showingUsers = ref(false)
const showingVCards = ref(false)

const showUsers = () => {
    // Set the flag to show users and hide vCards
    showingUsers.value = true
    showingVCards.value = false
}
const showVCards = () => {
    // Set the flag to show vCards and hide users
    showingUsers.value = false
    showingVCards.value = true
}
const clearView = () => {
    // Set the flag to hide both users and vCards
    showingUsers.value = false
    showingVCards.value = false
}

const fetchVCards = async () => {
    // Fetch the vCards from the API
    const response = await axios.get(`${config.baseAPI}/vcards`)
    vcards.value = response.data
}

const fetchUsers = async () => {
    // Fetch the users from the API
    const response = await axios.get(`${config.baseAPI}/users`)
    users.value = response.data
}

const newVCard = async (newVCard) => {
    if (newVCard){
        // Create a new vCard
        newVCard.balance = 0
        newVCard.max_debit = 5000
        const response = await axios.post(`${config.baseAPI}/vcards`, newVCard)
    }
}

onMounted(() => {
    // Fetch the vCards and users when the component is mounted
    fetchVCards()
    fetchUsers()
})
</script>

<template>
    <div>
        <div
            style="
                display: flex;
                flex-direction: row;
                align-items: center;
                justify-content: center;
                margin-top: 5%;
            "
        >
            <button @click="showUsers" class="btn btn-primary">Show Users</button>
            <button @click="showVCards" class="btn btn-primary">Show vCards</button>
            <button @click="clearView" class="btn btn-primary">Clear View</button>
        </div>

        <createVCard @createVCard="newVCard"></createVCard>

        <!-- Display Users -->
        <div v-if="showingUsers">
            <h2>Users</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(user, index) in users" :key="index">
                        <td>{{ user.name }}</td>
                        <td>{{ user.email }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Display vCards -->
        <div v-if="showingVCards">
            <h2>vCards</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone Number</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(vcard, index) in vcards" :key="index">
                        <td>{{ vcard.name }}</td>
                        <td>{{ vcard.phone_number }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
