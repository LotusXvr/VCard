<script setup>
import { ref, computed } from "vue"
import VCardDetail from "./VCardDetail.vue"
import axios from "axios"
// import config from "../utils/config"
import { useToast } from "vue-toastification"

const toast = useToast()


const props = defineProps({
    id: {
        type: Number,
        default: null,
    },
})

const newVCard = () => {
    return {
        phone_number: "",
        name: "",
        email: "",
        photo_url: null,
        password: "",
        confirmation_code: "",
        blocked: 0,
        balance: 0,
        max_debit: 5000,
    }
}

const vcard = ref(newVCard())
const errors = ref({})

const operation = computed(() => (!props.id || props.id < 0 ? "insert" : "update"))

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
                    toast.error("Validation Error")
                }
            })
    } else {
        axios
            .put("vcards/" + props.id, vcard.value)
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
</script>

<template>
    <VCardDetail
        :vcard="vcard"
        @requestUpdateVCard="detailRequestedUpdateVCard"
        @hide="closeEdit"
        @save="save"
    ></VCardDetail>
</template>
