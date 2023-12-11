<script setup>
import { useToast } from "vue-toastification"
import { useUserStore } from "../../stores/user.js"
import { ref } from "vue"
import axios from 'axios';
import router from "../../router";


const toast = useToast()
const userStore = useUserStore()

const codes = ref({
    confirmation_code: "",
    password: "",
    phone_number: userStore.userPhoneNumber
})

const errors = ref(null)

const deleteVcard = async () => {
    try {
        const response = await axios.delete("vcards/" + userStore.userPhoneNumber + "/dismiss", { data: codes.value });

        if (response.status === 200) {
            toast.success("Vcard has been deleted successfully!")
            useUserStore.logout()
            router.push({ name: "Home" })
        } else {
            toast.error("Failed to delete Vcard");
        }
    } catch (error) {
        toast.error(error.response?.data?.message || "An error occurred");
    }
}
</script>

<template>
    <form class="row g-3 needs-validation" novalidate @submit.prevent="deleteVcard">
        <h3 class="mt-5 mb-3">Dismiss Vcard</h3>
        <hr />
        <div class="mb-3">
            <div class="mb-3">
                <label for="inputCode" class="form-label">Code</label>
                <input type="password" class="form-control" id="confirmation_code" required
                    v-model="codes.confirmation_code" />
                <field-error-message :errors="errors" fieldName="confirmation_code"></field-error-message>
            </div>
        </div>
        <div class="mb-3">
            <div class="mb-3">
                <label for="inputPassword" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" required v-model="codes.password" />
                <field-error-message :errors="errors" fieldName="password"></field-error-message>
            </div>
        </div>
        <div class="mb-3 d-flex justify-content-center">
            <button type="button" class="btn btn-danger px-5" @click="deleteVcard">
                Dismiss Vcard
            </button>
        </div>
    </form>
</template>
