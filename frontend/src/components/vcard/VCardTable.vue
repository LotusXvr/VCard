<script setup>
import axios from "axios"
import { ref, watch } from "vue"

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
const emit = defineEmits(["completeToggled", "edit", "deleted"])

const editingVCards = ref(props.vcards)

watch(
    () => props.vcards,
    (newVCards) => {
        editingVCards.value = newVCards
    },
)

// Alternative to previous watch
// watchEffect(() => {
//   editingTasks.value = props.tasks
// })

const editClick = (vcard) => {
    emit("edit", vcard)
}
const deleteClick = (vcard) => {
    axios
        .delete("vcards/" + vcard.phone_number)
        .then((response) => {
            let deletedVCard = response.data.data
            emit("deleted", deletedVCard)
        })
        .catch((error) => {
            console.log(error)
        })
}
</script>

<template>
    <table class="table">
        <thead>
            <tr>
                <th>Phone Number</th>
                <th>Name</th>
                <th>Balance</th>
                <th v-if="showEditButton || showDeleteButton"></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="vcard in vcards" :key="vcard.phone_number">
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
</style>
