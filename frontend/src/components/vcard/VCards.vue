<script setup>
import axios from "axios"
import { ref, computed, onMounted } from "vue"
import VCardTable from "./VCardTable.vue"

const props = defineProps({
    vcardsTitle: {
        type: String,
        default: "VCards",
    },
    onlyCurrentVCards: {
        type: Boolean,
        default: false,
    },
})

const vcards = ref([])

const loadVCards = () => {
    // Change later when authentication is implemented
    axios
        .get("vcards")
        .then((response) => {
            vcards.value = response.data.data
        })
        .catch((error) => {
            console.log(error)
        })
}

const addVCard = () => {
    console.log("Navigate to New Task")
}

const editVCard = (task) => {
    console.log("Navigate to Edit Task with ID = " + task.id)
}

//   const deletedVCard = (deletedTask) => {
//       let idx = tasks.value.findIndex((t) => t.id === deletedTask.id)
//       if (idx >= 0) {
//         tasks.value.splice(idx, 1)
//       }
//   }



onMounted(() => {
    loadVCards()
})
</script>

<template>
    <div class="d-flex justify-content-between">
        <div class="mx-2">
            <h3 class="mt-4">{{ vcardsTitle }}</h3>
        </div>
        <!-- <div class="mx-2 total-filtro">
            <h5 class="mt-4">Total: {{ totalTasks }}</h5>
        </div> -->
    </div>
    <hr />
    <div v-if="!onlyCurrentVCards" class="mb-3 d-flex justify-content-between flex-wrap">
        <div class="mx-2 mt-2 flex-grow-1 filter-div">
        </div>
        <div class="mx-2 mt-2">
            <button type="button" class="btn btn-success px-4 btn-addtask" @click="addVCard">
                <i class="bi bi-xs bi-plus-circle"></i>&nbsp; Add VCard
            </button>
        </div>
    </div>
    <vcard-table
        :vcards="vcards"
        :showPhoneNumber="true"
        @edit="editVCard"
        @deleted="deletedVCard"
    ></vcard-table>
</template>

<style scoped>
.filter-div {
    min-width: 12rem;
}
.total-filtro {
    margin-top: 0.35rem;
}
.btn-addtask {
    margin-top: 1.85rem;
}
</style>
