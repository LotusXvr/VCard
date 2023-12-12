<script setup>
import axios from "axios"
import { useToast } from "vue-toastification"
import { useUserStore } from "../stores/user"
import { ref, watch, onMounted } from "vue"

const toast = useToast()
const userStore = useUserStore()

const props = defineProps({
    phone_number: {
        type: Number,
        default: null,
    },
})

const newVCard = () => {
    return {
        phone_number: null,
        name: "",
        email: "",
        photo_url: null,
        password: "",
        password_confirmation: "",
        confirmation_code: "",
    }
}

const vcard = ref(newVCard())
const errors = ref(null)
const transferAmount = ref(0);

let originalValueStr = ""
const loadVCard = async (phone_number) => {
    originalValueStr
    errors.value = null

    try {
        const response = await axios.get("vcards/" + phone_number)
        vcard.value = response.data.data
        console.log(response.data.data)
        originalValueStr = JSON.stringify(vcard.value)
    } catch (error) {
        console.log(error)
    }
}

watch(
    () => props.phone_number,
    (newValue) => {
        loadVCard(newValue)
    },
    { immediate: true },
)

onMounted(() => {
    loadVCard(userStore.userPhoneNumber)
})

const reforcarPoupanca = async() => {
    if(transferAmount.value <= 0) {
        toast.error("Transfer amount must be greater than 0.");
        return;
    };
    try {
        await axios.post("vcards/" + userStore.userPhoneNumber + "/reforcarPoupanca", {
            vcard: userStore.userPhoneNumber,
            valor: transferAmount.value,
        });

        toast.success("Savings reinforced sucessfully!");
        vcard.value.balance = parseFloat((vcard.value.balance - transferAmount.value).toFixed(2));
        const savings = parseFloat(vcard.value.savings);
        vcard.value.savings = parseFloat((savings + transferAmount.value).toFixed(2));
    } catch (error) {
        toast.error(error.response.data.message || "An error occurred.");
    }
};

const retirarPoupanca = async () => {
    if(transferAmount.value <= 0) {
        toast.error("Transfer amount must be greater than 0.");
        return;
    };
    try {
        await axios.post("vcards/" + userStore.userPhoneNumber + "/retirarPoupanca", {
            vcard: userStore.userPhoneNumber,
            valor: transferAmount.value,
        });

        toast.success("Savings Withdrawn sucessfully!");
        const balance = parseFloat(vcard.value.balance);
        vcard.value.balance = parseFloat((balance + transferAmount.value).toFixed(2));
        const savings = parseFloat(vcard.value.savings);
        vcard.value.savings = parseFloat((savings - transferAmount.value).toFixed(2));
    } catch (error) {
        toast.error(error.response.data.message || "An error occurred.");
    }
};

</script>

<template>
    <div class="container mt-5">
      <h1 class="display-4">Welcome</h1>
      <hr class="my-4">
      <h2>{{ vcard.name }}</h2>
  
      <div class="mt-4">
        <p><strong>Balance:</strong> {{ vcard.balance }}</p>
      </div>
  
      <div class="mt-4">
        <p><strong>Savings:</strong> {{ vcard.savings }}</p>
  
        <!-- Input for transfer amount -->
        <div class="mb-3">
          <label for="transferAmount" class="form-label">Transfer Amount:</label>
          <input v-model="transferAmount" type="number" class="form-control" id="transferAmount" />
        </div>
  
        <!-- Buttons to initiate transfer -->
        <div class="mb-3">
          <button @click.prevent="reforcarPoupanca" class="btn btn-success me-2">Reinforce Savings</button>
          <button @click.prevent="retirarPoupanca" class="btn btn-danger">Withdraw Savings</button>
        </div>
      </div>
    </div>
  </template>
