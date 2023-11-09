<script setup>
import { ref } from "vue"
import VCardDetail from "./VCardDetail.vue"
// import axios from "axios"
// import config from "../utils/config"

const vcardEdit = ref(null)

const props = defineProps({
    vcard: Object,
    readonly: Boolean,
})

// const newVCard = () => {
//       return {
//         phone_number: '',
//         name: '',
//         email: '',
//         photo_url: null,
//         password: '',
//         confirmation_code: '',
//         blocked: 0,
//         balance: 0,
//         max_debit: 5000,
//       }
//     }

const emit = defineEmits(["requestRemoveVCardFromList", "requestUpdateVCard"])

const clickToDeleteVCard = (vcard) => {
    emit("requestRemoveVCardFromList", vcard)
}

const editVCard = (vcard) => {
    vcardEdit.value = vcard
}

const closeEdit = () => {
    vcardEdit.value = null
}

const detailRequestedUpdateVCard = (vcard) => {
    vcardEdit.value = null
    emit("requestUpdateVCard", vcard)
}
</script>

<template>
    <li class="list-group-item" :class="{ 'bg-light': readonly }">
        <span>{{ props.vcard.name + " || " + props.vcard.email + " || " + props.vcard.balance + "€" }}</span>
        <div class="float-end" v-show="!readonly">
            <button class="btn btn-danger btn-xs" @click="clickToDeleteVCard(vcard)">
                <i class="bi-trash" aria-hidden="true"></i>
            </button>

            <button class="btn btn-info btn-xs" @click="editVCard(vcard)" v-if="!vcardEdit">
                <i class="bi-pencil" aria-hidden="true"></i>
            </button>
            <button class="btn btn-warning btn-xs" @click="closeEdit" v-else>
                <i class="bi-arrow-up" aria-hidden="true"></i>
            </button>
        </div>
        <div v-if="vcardEdit">
            <hr />
            <VCardDetail
                :vcard="vcardEdit"
                @requestUpdateVCard="detailRequestedUpdateVCard"
                @hide="closeEdit"
            ></VCardDetail>
        </div>
    </li>
</template>

<style scoped>
.completed {
    text-decoration: line-through;
}

button.btn {
    margin-left: 5px;
}
</style>
