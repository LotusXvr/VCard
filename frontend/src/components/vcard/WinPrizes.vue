<script setup>
import { onMounted, ref } from "vue"
import axios from "axios"
import { useUserStore } from "../../stores/user"
import { useToast } from "vue-toastification"

const userStore = useUserStore()
const toast = useToast()

/*
 *
 *   VCard Comunicações com a BD
 *   Desde carregar, dar update ao numero de spins e efetuar a transaçao
 */

const spins = ref(0)
const loadVCardSpins = async () => {
    try {
        const response = await axios.get("vcards/" + userStore.userPhoneNumber)
        spins.value = response.data.data.spins
        console.log(response.data.data)
    } catch (error) {
        console.log(error)
    }
}

const updateVCardSpins = async () => {
    try {
        const response = await axios.put("vcard/" + userStore.userPhoneNumber + "/spins", {
            spins: spins.value,
        })
        console.log(response.data.data)
    } catch (error) {
        console.log(error)
    }
}

const transactionToSave = ref({
    payment_type: "VCARD",
    vcard: "VCard Enterprise Prizes",
    payment_reference: userStore.userPhoneNumber,
    value: "",
    type: "C",
})

const givePrizeTransaction = async () => {
    try {
        transactionToSave.value.value = prizeWon.value
        const response = await axios.post("transactions", transactionToSave.value)
        toast.success(response.data.message)
    } catch (error) {
        console.log(error)
    }
}

/*
 *
 *  SPIN WHEEL
 *
 */

const showLoading = ref(false)
const isAlreadySpinning = ref(false)

const spinWheel = () => {
    if (spins.value <= 0) {
        toast.error("You don't have any spins left!")
        return
    }
    showLoading.value = true
    goodPrize.value = false
    badPrize.value = false

    // se já estiver a rodar, sai logo fora
    if (isAlreadySpinning.value == true) {
        return
    }
    isAlreadySpinning.value = true

    // timeout para simular o tempo de espera
    setTimeout(() => {
        generatePrize()

        showLoading.value = false
        isAlreadySpinning.value = false
    }, 2000)

    //update spins
    spins.value -= 1
    updateVCardSpins()
}

const prizeWon = ref(null)
const goodPrize = ref(false)
const badPrize = ref(false)

const generatePrize = () => {
    // Define the probability distribution (80% chance of nothing, 20% chance of winning)
    const nothingProbability = 0.8

    // Generate a random number to determine the outcome
    const randomNumber = Math.random()

    if (randomNumber < nothingProbability) {
        prizeWon.value = "Nothing"
        badPrize.value = true
    } else {
        const highChancePrizes = ["0.01", "0.02", "0.05", "0.10", "0.20", "0.50", "1"]
        const lowChancePrizes = ["2", "5", "10", "20", "50"]
        const jackpotPrizes = ["100", "200", "500", "1000"]

        const randomValue = Math.random()
        let chosenArray, prize

        if (randomValue < 0.7) {
            chosenArray = highChancePrizes
        } else if (randomValue < 0.9) {
            chosenArray = lowChancePrizes
        } else {
            chosenArray = jackpotPrizes
        }

        const randomPrizeIndex = Math.floor(Math.random() * chosenArray.length)
        prize = chosenArray[randomPrizeIndex]

        prizeWon.value = prize
        goodPrize.value = true
    }

    // if good prize, send the money to the user
    if (goodPrize.value == true) {
        givePrizeTransaction()
    }
}

onMounted(() => {
    loadVCardSpins()
})
</script>

<template>
    <div class="card text-center mt-3">
        <div class="card-body">
            For every 10€ spent using our application you get a chance to win a prize!
        </div>
    </div>

    <div class="text-center mt-3">
        <h4>You have {{ spins }} spins!</h4>
    </div>

    <div class="text-center mt-3">
        <button type="button" class="btn btn-success" @click="spinWheel">Play!</button>
    </div>

    <div class="text-center mt-3" v-if="showLoading">
        <!-- Add a loading image or spinner -->
        <img src="/loading.gif" alt="Loading..." />
    </div>

    <div class="text-center mt-3" v-if="badPrize">
        <h4>Sorry, you didn't win anything this time.</h4>
    </div>

    <div class="text-center mt-3 mb-3" v-if="goodPrize">
        <!-- Display for good prizes with text on top of the smaller image -->
        <div style="position: relative">
            <img
                src="/congratulations.jpg"
                alt="Congratulations!"
                style="width: 600px; height: auto"
            />
            <h2
                style="
                    position: absolute;
                    top: 70%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    color: white;
                    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
                "
            >
                You won {{ prizeWon }} €!
            </h2>
        </div>
    </div>
</template>
