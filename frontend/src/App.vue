<template>
    <div>
        <div style="display: flex; flex-direction: row; align-items: center; justify-content: center; margin-top: 5%;">
            <button @click="showUsers" class="btn btn-primary">Show Users</button>
            <button @click="showVCards" class="btn btn-primary">Show vCards</button>
            <button @click="clearView" class="btn btn-primary">Clear View</button>
        </div>

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

<script>
import axios from "../plugins/axios"

export default {
    data() {
        return {
            users: [], // Store the fetched users
            vcards: [], // Store the fetched vCards
            showingUsers: false, // Control whether to show users
            showingVCards: false, // Control whether to show vCards
        }
    },
    methods: {
        showUsers() {
            // Set the flag to show users and hide vCards
            this.showingUsers = true
            this.showingVCards = false
        },
        showVCards() {
            // Set the flag to show vCards and hide users
            this.showingUsers = false
            this.showingVCards = true
        },
        clearView(){
            // Set the flag to hide both users and vCards
            this.showingUsers = false
            this.showingVCards = false
        },
        fetchUsers() {
            axios
                .get("users")
                .then((response) => {
                    this.users = response.data
                })
                .catch((error) => {
                    console.error("Error fetching Users data:", error)
                })
        },
        fetchVCards() {
            axios
                .get("vcards")
                .then((response) => {
                    console.log("vCards response:", response.data)
                    this.vcards = response.data
                })
                .catch((error) => {
                    console.error("Error fetching vCards data:", error)
                })
        },
    },
    mounted() {
        // Fetch users and vCards when the component is mounted
        this.fetchUsers()
        this.fetchVCards()
    },
}
</script>
