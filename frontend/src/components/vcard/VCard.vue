<script setup>
import { ref, computed, watch } from "vue"
import VCardDetail from "./VCardDetail.vue"
import axios from "axios"
// import config from "../utils/config"
import { useToast } from "vue-toastification"

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

const props = defineProps({
    phone_number: {
        type: Number,
        default: null,
    },
})

const vcard = ref(newVCard())
const errors = ref({})

const operation = computed(() => (!props.phone_number || props.phone_number < 0 ? "insert" : "update"))

const loadVCard = (phone_number) => {
    if (!phone_number || phone_number < 0) {
        vcard.value = newVCard()
    } else {
        axios
            .get("vcards/" + phone_number)
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
                toast.success("VCard Created")
            })
            .catch((error) => {
                console.dir(error)

                if (error.response.status == 422) {
                    errors.value = error.response.data.errors
                    toast.error("Valphone_numberation Error")
                }
            })
    } else {
        axios
            .put("vcards/" + props.phone_number, vcard.value)
            .then((response) => {
                console.log("VCard Updated")
                console.dir(response.data.data)
                toast.success("VCard Updated")
            })
            .catch((error) => {
                console.dir(error)
            })
    }
}

watch(
    () => props.phone_number,
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
