<script setup>
import { ref } from "vue"

const showLoading = ref(false)

const spinWheel = () => {
    showLoading.value = true
    goodPrize.value = false
    badPrize.value = false

    setTimeout(() => {
        generatePrize()

        showLoading.value = false
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
        const prizes = ["Prize 1", "Prize 2", "Prize 3", "Prize 4", "Prize 5", "Prize 6"]
        const randomIndex = Math.floor(Math.random() * prizes.length)
        prizeWon.value = prizes[randomIndex]
        goodPrize.value = true
    }
}
</script>

<template>
    <div class="card text-center mt-3">
        <div class="card-body">
            For every 10€ spent using our application you get a chance to win a prize!
        </div>
    </div>

    <div class="text-center mt-3">
        <button type="button" class="btn btn-success" @click="spinWheel">Play!</button>
    </div>

    <div class="text-center mt-3" v-if="showLoading">
        <!-- Add a loading image or spinner -->
        <img src="/loading.gif" alt="Loading..." />
    </div>

    <div class="text-center mt-3" v-if="badPrize">
        <h4 v-if="badPrize">Sorry, you didn't win anything this time.</h4>
    </div>

    <div class="text-center mt-3" v-if="goodPrize">
        <h2>Ganhaste {{ prizeWon }}!</h2>
    </div>
</template>
