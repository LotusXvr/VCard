<script setup>
import { onMounted, ref, computed } from "vue"
import axios from "axios"
import { useUserStore } from "../stores/user"
const userStore = useUserStore()

const newVCard = () => {
    return {
        phone_number: "",
        name: "",
        email: "",
        photo_url: null,
        balance: 0,
        max_debit: 0,
        password: "",
        confirmation_code: "",
    }
}

const vcard = ref(newVCard())
const loadVCard = () => {
    axios
        .get("vcards/" + userStore.userPhoneNumber)
        .then((response) => {
            vcard.value = response.data.data
        })
        .catch((error) => {
            console.log(error)
        })
}

const vcardCount = ref(0)
const getCountVCards = () => {
    axios
        .get("statistics/vcards/count")
        .then((response) => {
            console.log(response.data.vcardCount)
            vcardCount.value = response.data.vcardCount
        })
        .catch((error) => {
            console.log(error)
        })
}

const activeVCardCount = ref(0)
const getCountActiveVCards = () => {
    axios
        .get("statistics/vcards/active/count")
        .then((response) => {
            console.log(response.data.activeVCardCount)
            activeVCardCount.value = response.data.activeVCardCount
        })
        .catch((error) => {
            console.log(error)
        })
}

onMounted(() => {
    loadVCard()
    getCountVCards()
    getCountActiveVCards()
})
</script>

<template>
    <div class="container mt-4">
        <div class="row">
            <div class="col-lg-12 mb-4">
                <!-- Phone Number Title -->
                <h2 class="text-primary text-center" style="font-size: 50px">
                    {{ vcard.phone_number }}
                </h2>
            </div>

            <div class="col-lg-6 mb-4">
                <!-- Balance Card -->
                <div class="card bg-success text-white text-center">
                    <div class="card-header">
                        <h4 class="card-title" style="font-size: 24px">Balance</h4>
                    </div>
                    <div class="card-body">
                        <p class="card-text" style="font-size: 36px">{{ vcard.balance }}€</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <!-- Max Debit Card -->
                <div class="card bg-warning text-dark text-center">
                    <div class="card-header">
                        <h4 class="card-title" style="font-size: 24px">Max Debit</h4>
                    </div>
                    <div class="card-body">
                        <p class="card-text" style="font-size: 36px">{{ vcard.max_debit }}€</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-12 mb-4">
                <!-- Email and Name List -->
                <div class="card text-center">
                    <div class="card-header">
                        <h4 class="card-title">Information</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Name:</strong> {{ vcard.name }}</li>
                            <li class="list-group-item">
                                <strong>Email:</strong> {{ vcard.email }}
                            </li>
                            <!-- Add more list items based on your vCard properties -->
                        </ul>
                    </div>
                </div>
            </div>

            <h1 class="text-center">Admin</h1>
            <h4>Current count of vcards</h4>
            <p>{{ vcardCount }}</p>
            <h4>Current count of active vcards</h4>
            <p>{{ activeVCardCount }}</p>
            <!-- Add more cards based on your vCard properties -->
        </div>
    </div>
</template>
