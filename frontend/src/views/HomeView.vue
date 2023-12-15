<script setup>
import axios from 'axios'
import { useToast } from 'vue-toastification'
import { useUserStore } from '../stores/user'
import { ref, onMounted, computed, inject } from 'vue'

import Chart from 'chart.js/auto'

const toast = useToast()
const userStore = useUserStore()
const socket = inject('socket')

const newVCard = () => {
  return {
    phone_number: null,
    name: '',
    email: '',
    photo_url: null,
    password: '',
    password_confirmation: '',
    confirmation_code: ''
  }
}
socket.on('moneySentNotification', () => {
  loadVCard(userStore.userPhoneNumber)
})

const vcard = ref(newVCard())
const errors = ref(null)
const transferAmount = ref(0)

let originalValueStr = ''
const loadVCard = async (phone_number) => {
  originalValueStr
  errors.value = null
  try {
    const response = await axios.get('vcards/' + phone_number)
    vcard.value = response.data.data
    console.log(response.data.data)
    originalValueStr = JSON.stringify(vcard.value)
  } catch (error) {
    console.log(error)
  }
}

const reforcarPoupanca = async () => {
  if (transferAmount.value <= 0) {
    toast.error('Transfer amount must be greater than 0.')
    return
  }

  transferAmount.value = parseFloat(transferAmount.value).toFixed(2)

  try {
    await axios.post('vcards/' + userStore.userPhoneNumber + '/reforcarPoupanca', {
      vcard: userStore.userPhoneNumber,
      valor: transferAmount.value
    })

    toast.success('Savings reinforced sucessfully!')
    vcard.value.balance = parseFloat(vcard.value.balance - transferAmount.value).toFixed(2)
    vcard.value.savings = (parseFloat(vcard.value.savings) + parseFloat(transferAmount.value)).toFixed(2)
  } catch (error) {
    toast.error(error.response.data.message || 'An error occurred.')
  }
}

const retirarPoupanca = async () => {
  if (transferAmount.value <= 0) {
    toast.error('Transfer amount must be greater than 0.')
    return
  }

  transferAmount.value = parseFloat(transferAmount.value).toFixed(2)

  try {
    await axios.post('vcards/' + userStore.userPhoneNumber + '/retirarPoupanca', {
      vcard: userStore.userPhoneNumber,
      valor: transferAmount.value
    })
    toast.success('Savings Withdrawn sucessfully!')
    vcard.value.balance = (parseFloat(vcard.value.balance) + parseFloat(transferAmount.value)).toFixed(2)
    vcard.value.savings = parseFloat(vcard.value.savings - transferAmount.value).toFixed(2)
  } catch (error) {
    toast.error(error.response.data.message || 'An error occurred.')
  }
}

const lastMonthTransactions = ref([])
const loadLastMonthTransactions = () => {
  axios
    .get('vcard/' + userStore.userPhoneNumber + '/transactions/lastmonth', {})
    .then((response) => {
      lastMonthTransactions.value = response.data
      loadChart()
    })
    .catch((error) => {
      console.log(error)
    })
}

const formatDateTime = (dateTimeString) => {
  const [date, time] = dateTimeString.split(' ')
  const options = { month: 'short', day: 'numeric' }
  const formattedDate = new Date(date).toLocaleDateString('en-GB', options)
  return { date: formattedDate, time: time.slice(0, 5) }
}

const monthNames = [
  'January',
  'February',
  'March',
  'April',
  'May',
  'June',
  'July',
  'August',
  'September',
  'October',
  'November',
  'December'
]

const currentDate = new Date()

const lastMonthDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1)

const lastMonthMonth = lastMonthDate.getMonth() + 1

const sumValues = (transactions, type) => {
  return transactions
    .filter((transaction) => transaction.type === type)
    .reduce((sum, transaction) => sum + parseFloat(transaction.value), 0)
}

const sumDebitValues = computed(() => {
  const rawValue = sumValues(lastMonthTransactions.value, 'D')
  return parseFloat(rawValue.toFixed(2))
})

const sumCreditValues = computed(() => {
  const rawValue = sumValues(lastMonthTransactions.value, 'C')
  return parseFloat(rawValue.toFixed(2))
})

const dateindex = ref(0)
const dates = computed(() => {
  const dates = lastMonthTransactions.value
    .map((transaction) => {
      dateindex.value += 1
      return '(' + dateindex.value + ') ' + formatDateTime(transaction.datetime).date
    })
    .reverse()

  return dates
})

const balances = computed(() => {
  const balances = lastMonthTransactions.value
    .map((transaction) => {
      return transaction.new_balance
    })
    .reverse()

  return balances
})

const balanceChartEl = ref(null)
let balanceChart = null

const loadChart = () => {
  if (!balanceChartEl.value) {
    return
  }

  if (balanceChart) {
    balanceChart.destroy()
  }

  balanceChart = new Chart(balanceChartEl.value.getContext('2d'), {
    type: 'line',
    data: {
      labels: dates.value,
      datasets: [
        {
          label: 'Balance (€)',
          data: balances.value,
          backgroundColor: 'rgba(255, 99, 132, 0.2)',
          borderColor: 'rgba(255, 99, 132, 1)',
          borderWidth: 1
        }
      ]
    }
  })
}

onMounted(() => {
  loadVCard(userStore.userPhoneNumber)
  loadLastMonthTransactions()
})
</script>

<template>
  <div class="container mt-5" v-if="userStore.userType === 'V'">
    <div class="container mt-4 text-center">
      <h1 class="display-4">Welcome</h1>
      <hr class="my-4" />
      <h2>{{ vcard.name }}</h2>

      <div class="row justify-content-center" style="margin-top: 25px">
        <div class="col-md-3">
          <div class="card text-white mb-3" style="background-color: #15ba58; max-width: 18rem">
            <div class="card-header text-center">Balance</div>
            <div class="card-body text-center">
              <h2 class="card-title">{{ vcard.balance }}€</h2>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card text-white mb-3" style="background-color: #15baaa; max-width: 18rem">
            <div class="card-header text-center">Savings</div>
            <div class="card-body text-center">
              <h2 class="card-title">{{ vcard.savings }}€</h2>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card text-white mb-3 bg-danger" style=" max-width: 18rem">
            <div class="card-header text-center">Max Debit</div>
            <div class="card-body text-center">
              <h2 class="card-title">{{ vcard.max_debit }}€</h2>
            </div>
          </div>
        </div>
      </div>
      <div class="mb-3" style="margin: 0px 40%">
        <label for="transferAmount" class="form-label">Transfer Amount:</label>
        <input v-model="transferAmount" type="number" class="form-control" id="transferAmount" />
      </div>

      <div class="mb-3">
        <button @click.prevent="reforcarPoupanca" class="btn btn-success me-2">
          Reinforce Savings
        </button>
        <button @click.prevent="retirarPoupanca" class="btn btn-danger">Withdraw Savings</button>
      </div>
    </div>

    <hr />

    <div class="container text-center">
      <h3>{{ monthNames[lastMonthMonth - 1] }}</h3>
      <h4>Your balance last month</h4>
      <canvas
        ref="balanceChartEl"
        height="200px"
        width="200px"
        style="height: 200px; width: 200px"
      ></canvas>

      <div class="row mt-3" style="margin-bottom: 100px">
        <div class="col-md-6">
          <h5>Earnings:</h5>
          <p class="h5 text-success">{{ sumCreditValues }}€</p>
        </div>
        <div class="col-md-6">
          <h5>Expenses:</h5>
          <p class="h5 text-danger">{{ sumDebitValues }}€</p>
        </div>
      </div>
    </div>
  </div>
</template>
