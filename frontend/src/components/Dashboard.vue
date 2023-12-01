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

const totalVCardBalance = ref(0)
const getTotalVCardBalance = () => {
    axios
        .get("statistics/vcards/balance")
        .then((response) => {
            console.log(response.data.vcardBalanceSum)
            totalVCardBalance.value = response.data.vcardBalanceSum
        })
        .catch((error) => {
            console.log(error)
        })
}

const totalActiveVCardBalance = ref(0)
const getTotalActiveVCardBalance = () => {
    axios
        .get("statistics/vcards/active/balance")
        .then((response) => {
            console.log(response.data.activeVCardBalanceSum)
            totalActiveVCardBalance.value = response.data.activeVCardBalanceSum
        })
        .catch((error) => {
            console.log(error)
        })
}

const transactionsCount = ref(0)
const getCountTransactions = () => {
    axios
        .get("statistics/transactions/count")
        .then((response) => {
            console.log(response.data.transactionsCount)
            transactionsCount.value = response.data.transactionsCount
        })
        .catch((error) => {
            console.log(error)
        })
}
const startDate = ref('');
const endDate = ref('');
const transactionsSumBetweenDates = ref(0)
const today = new Date();
const year = today.getFullYear();
const month = String(today.getMonth() + 1).padStart(2, '0'); // Months are zero-based
const day = String(today.getDate()).padStart(2, '0');
const todayDateString = `${year}-${month}-${day}`;

const filterTransactions = async () => {
    if(startDate.value==''){
        await axios.get('statistics/transactions/older', {
            params: {
                startDate: startDate.value,
                endDate: endDate.value
            }
        })
        .then(response => {
            startDate.value = response.data.olderTransaction.date;
        })
        .catch(error => {
            console.error(error);
        });
    }
    if(endDate.value==''){
        endDate.value=todayDateString
    }
    await axios.get('statistics/transactions/sum-between-dates', {
        params: {
            startDate: startDate.value,
            endDate: endDate.value
        }
    })
    .then(response => {
        transactionsSumBetweenDates.value = response.data.sumBetweenDates;
    })
    .catch(error => {
        console.error(error);
    });
    console.log(transactionsSumBetweenDates.value)
};

const transactionsSum = ref(0)
const getSumTransactions = () => {
    axios
        .get("statistics/transactions/sum")
        .then((response) => {
            console.log(response.data.transactionsSum)
            transactionsSum.value = response.data.transactionsSum
        })
        .catch((error) => {
            console.log(error)
        })
}

onMounted(() => {
    loadVCard()
    getCountVCards()
    getCountActiveVCards()
    getTotalVCardBalance()
    getTotalActiveVCardBalance()
    getCountTransactions()
    getSumTransactions()
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
        </div>  
    </div>
            <div class="container mt-5">
    <h1 class="text-center mb-4">Admin Dashboard</h1>

    <div class="row">
        <div class="col-md-6">
            <h4>Current Count of VCards</h4>
            <p>{{ vcardCount }}</p>

            <h4>Current Count of Active VCards</h4>
            <p>{{ activeVCardCount }}</p>

            <h4>Total Balance of All VCards</h4>
            <p>{{ totalVCardBalance }}</p>

            <h4>Total Balance of All Active VCards</h4>
            <p>{{ totalActiveVCardBalance }}</p>
        </div>

        <div class="col-md-6">
            <h4>Current Count of Transactions</h4>
            <p>{{ transactionsCount }}</p>

            <h4>Current Sum of Transactions</h4>
            <p>{{ transactionsSum }}</p>

            <div class="mt-3">
                <label for="startDate"><b>Start Date:</b></label>
                <input type="date" id="startDate" v-model="startDate" class="form-control">

                <label for="endDate" class="mt-2"><b>End Date:</b></label>
                <input type="date" id="endDate" v-model="endDate" class="form-control">

                <button @click="filterTransactions" class="btn btn-primary mt-3 float-end">Filter</button>
            </div>

            <p class="mt-3" v-if="transactionsSumBetweenDates">
                <b>Sum of Transactions Between {{ startDate }} and {{ endDate }}:</b> {{ transactionsSumBetweenDates }}€
            </p>
        </div>
    </div>
</div>
</template>
