<script setup>
import { ref, computed, watch } from "vue"
import VCardDetail from "./VCardDetail.vue"
import axios from "axios"
// import config from "../utils/config"
import { useToast } from "vue-toastification"
import { useRouter } from "vue-router"
import { useUserStore } from "../../stores/user"

const userStore = useUserStore()
const router = useRouter()
const toast = useToast()

const newVCard = () => {
    return {
        phone_number: "",
        name: "",
        email: "",
        photo_url: null,
        password: "",
        confirmation_code: "",
    }
}

const vcard = ref(newVCard())
const errors = ref({})

const operation = computed(() =>
    !userStore.userPhoneNumber || userStore.userPhoneNumber < 0 ? "insert" : "update",
)

const loadVCard = () => {
    if (!userStore.userPhoneNumber || userStore.userPhoneNumber < 0) {
        vcard.value = newVCard()
    } else {
        axios
            .get("vcards/" + userStore.userPhoneNumber)
            .then((response) => {
                vcard.value = response.data.data
            })
            .catch((error) => {
                console.log(error)
            })
    }
}

const save = () => {
    if (operation.value == "insert") {
        axios
            .post("vcards", vcard.value)
            .then((response) => {
                console.log("VCard Created")
                console.dir(response.data.data)
                toast.success(
                    "VCard with phone number " +
                        response.data.data.phone_number +
                        " created successfully",
                    router.back(),
                )
            })
            .catch((error) => {
                console.dir(error)

                if (error.response.status == 422) {
                    errors.value = error.response.data.errors
                    toast.error("Validation error")
                }
            })
    } else {
        axios
            .put("vcards/" + userStore.userPhoneNumber, vcard.value)
            .then((response) => {
                console.log("VCard Updated")
                console.dir(response.data.data)
                toast.success("VCard Updated")
                router.back()
            })
            .catch((error) => {
                errors.value = error.response.data.errors
                toast.error("Error updating VCard")
            })
    }
}

watch(
    () => userStore.userPhoneNUmber,
    (newValue) => {
        loadVCard(newValue)
    },
    { immediate: true },
)
</script>

<template>
    <VCardDetail
        :vcard="vcard"
        :operationType="operation"
        @hide="closeEdit"
        @save="save"
    ></VCardDetail>
</template>
