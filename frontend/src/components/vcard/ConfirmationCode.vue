<script setup>
import { useToast } from "vue-toastification"
import { useRouter } from "vue-router"
import { useUserStore } from "../../stores/user.js"
import { ref } from "vue"
import axios from 'axios';


const toast = useToast()
const router = useRouter()
const userStore = useUserStore()

const codes = ref({
    current_confirmation_code: "",
    confirmation_code: "",
    confirmation_code_confirmation: "",
    phone_number: userStore.userPhoneNumber
})

const errors = ref(null)

const changeCode = async () => {
    axios
        .patch("vcards/" + userStore.userPhoneNumber + "/change-confirmation-code", codes.value)
        .then((response) => {
            toast.success("Code has been changed successfully!")
            router.back()
        }).catch((error) => {
            toast.error(error.response.data.message)
        })

}
</script>

<template>
    <form class="row g-3 needs-validation" novalidate @submit.prevent="changeCode">
        <h3 class="mt-5 mb-3">Change Code</h3>
        <hr />
        <div class="mb-3">
            <div class="mb-3">
                <label for="inputCurrentCode" class="form-label">Current Code</label>
                <input type="password" class="form-control" id="current_confirmation_code" required
                    v-model="codes.current_confirmation_code" />
                <field-error-message :errors="errors" fieldName="current_confirmation_code"></field-error-message>
            </div>
        </div>
        <div class="mb-3">
            <div class="mb-3">
                <label for="inputCode" class="form-label">New Code</label>
                <input type="password" class="form-control" id="confirmation_code" required
                    v-model="codes.confirmation_code" />
                <field-error-message :errors="errors" fieldName="confirmation_code"></field-error-message>
            </div>
        </div>
        <div class="mb-3">
            <div class="mb-3">
                <label for="inputCodeConfirm" class="form-label">Code Confirmation</label>
                <input type="password" class="form-control" id="confirmation_code_confirmation" required
                    v-model="codes.confirmation_code_confirmation" />
                <field-error-message :errors="errors" fieldName="confirmation_code_confirmation"></field-error-message>
            </div>
        </div>
        <div class="mb-3 d-flex justify-content-center">
            <button type="button" class="btn btn-primary px-5" @click="changeCode">
                Change Code
            </button>
        </div>
    </form>
</template>
