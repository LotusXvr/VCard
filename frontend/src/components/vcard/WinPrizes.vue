<script setup>
import { onMounted, ref } from "vue"
import axios from "axios"
import { useUserStore } from "../../stores/user"

const userStore = useUserStore()

// get spins number
const spins = ref(0)
const loadVCard = async () => {
    try {
      const response = await axios.get('vcards/' + userStore.userPhoneNumber)
      spins.value = response.data.data.spins
      console.log(response.data.data)
    } catch (error) {
      console.log(error)
    }
}




const showLoading = ref(false)
const isAlreadySpinning = ref(false)

const spinWheel = () => {
    showLoading.value = true
    goodPrize.value = false
    badPrize.value = false

    // se já estiver a rodar, sai logo fora
    if (isAlreadySpinning.value == true) {
        return
    }
    isAlreadySpinning.value = true

    setTimeout(() => {
        generatePrize()

        showLoading.value = false
        isAlreadySpinning.value = false
    }, 2000)
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
        const prizes = [
            "0.01€",
            "0.02€",
            "0.10€",
            "1€",
            "Vale no Brasa Rio",
            "Manicure gratuita",
            "Viagem a Espanha",
            "MacBook",
            "Rolex",
            "Tesla Model 3",
        ]
        const randomIndex = Math.floor(Math.random() * prizes.length)
        prizeWon.value = prizes[randomIndex]
        goodPrize.value = true
    }

}

onMounted(() => {
    loadVCard()
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
                You won {{ prizeWon }}!
            </h2>
        </div>
    </div>
</template>
