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
const startDateC = ref('');
const endDateC = ref('');
const transactionsCountBetweenDates = ref(0)

const filterTransactionsC = async () => {
    if (startDateC.value === '') {
        await axios.get('statistics/transactions/older', {
            params: {
                startDate: startDateC.value,
                endDate: endDateC.value
            }
        })
        .then(response => {
            startDateC.value = response.data.olderTransaction.date;
        })
        .catch(error => {
            console.error(error);
        });
    }
    if (endDateC.value === '') {
        endDateC.value = todayDateString;
    }
    console.log(startDateC.value)
    console.log(endDateC.value)
    await axios.get('statistics/transactions/count-between-dates', {
        params: {
            startDate: startDateC.value,
            endDate: endDateC.value
        }
    })
    .then(response => {
        transactionsCountBetweenDates.value = response.data.countBetweenDates;
    })
    .catch(error => {
        console.error(error);
    });
    console.log(transactionsCountBetweenDates.value);
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
          <h1 class="text-center mb-4">Admin Dashboard</h1>
          <div class="card text-center">
            <div class="card-header">
              <h4 class="card-title">User Information</h4>
            </div>
            <div class="card-body">
              <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Name:</strong> {{ userStore.userName }}</li>
                <li class="list-group-item">
                  <strong>Email:</strong> {{ userStore.userPhoneNumber }}
                </li>
                <!-- Add more list items based on your user properties -->
              </ul>
            </div>
          </div>
        </div>
      </div>
  
      <h1 class="text-center mb-4">Statistics</h1>
  
      <div class="container mt-5">
        <div class="row" style="margin-bottom: 20px;">
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
            <h7><b>Filter:</b></h7>
            <div class="mt-3">
              <label for="startDate"><b>Start Date:</b></label>
              <input type="date" id="startDate" v-model="startDateC" class="form-control">
  
              <label for="endDate" class="mt-2"><b>End Date:</b></label>
              <input type="date" id="endDate" v-model="endDateC" class="form-control">
  
              <button @click="filterTransactionsC" class="btn btn-primary mt-3 float-end">Filter</button>
            </div>
  
            <div class="mt-3">
                <p v-if="transactionsCountBetweenDates">
                    <b>Count of Transactions Between {{ startDateC }} and {{ endDateC }}:</b> {{ transactionsCountBetweenDates }}€
                </p>
            </div>
  
            <h4>Current Sum of Transactions</h4>
            <p>{{ transactionsSum }}</p>
            <h7><b>Filter:</b></h7>
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
    </div>
  </template>
  
