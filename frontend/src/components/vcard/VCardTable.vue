<script setup>
import { ref, watch, inject } from "vue"

import avatarNoneUrl from "@/assets/avatar-none.png"

const apiDomain = inject("apiDomain")

const props = defineProps({
    vcards: {
        type: Array,
        default: () => [],
    },
    showEditButton: {
        type: Boolean,
        default: true,
    },
    showDeleteButton: {
        type: Boolean,
        default: true,
    },
})

console.log(props.vcards)
const emit = defineEmits(["completeToggled", "edit", "delete"])

const editingVCards = ref(props.vcards)

watch(
    () => props.vcards,
    (newVCards) => {
        editingVCards.value = newVCards
    },
)

const photoFullUrl = (vcard) => {
    return vcard.photo_url ? apiDomain + "/storage/fotos/" + vcard.photo_url : avatarNoneUrl
}

const editClick = (vcard) => {
    emit("edit", vcard)
}
const deleteClick = (vcard) => {
    emit("delete", vcard)
}
</script>

<template>
    <table class="table">
        <thead>
            <tr>
                <th></th>
                <th>Phone Number</th>
                <th>Name</th>
                <th>Balance</th>
                <th v-if="showEditButton || showDeleteButton"></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="vcard in editingVCards" :key="vcard.phone_number">
                <td class="align-middle">
                    <img :src="photoFullUrl(vcard)" class="rounded-circle img_photo" />
                </td>
                <td>{{ vcard.phone_number }}</td>
                <td>
                    {{ vcard.name }}
                </td>
                <td>
                    <span>{{ vcard.balance }}</span>
                </td>
                <td class="text-end" v-if="showEditButton || showDeleteButton">
                    <div class="d-flex justify-content-end">
                        <button
                            class="btn btn-xs btn-light"
                            @click="editClick(vcard)"
                            v-if="showEditButton"
                        >
                            <i class="bi bi-xs bi-pencil"></i>
                        </button>

                        <button
                            class="btn btn-xs btn-light"
                            @click="deleteClick(vcard)"
                            v-if="showDeleteButton"
                        >
                            <i class="bi bi-xs bi-x-square-fill"></i>
                        </button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</template>
<style scoped>
button {
    margin-left: 3px;
    margin-right: 3px;
}

.img_photo {
    width: 3.2rem;
    height: 3.2rem;
}
</style>
