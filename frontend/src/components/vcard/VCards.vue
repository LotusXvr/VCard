<script setup>
import axios from "axios"
import { ref, onMounted } from "vue"
import VCardTable from "./VCardTable.vue"

const success = ref(null)
const error = ref(null)

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
            vcards.value = response.data
            console.log(response.data)
        })
        .catch((error) => {
            console.log(error)
        })
}


const editVCard = (vcard) => {
    console.log("Navigate to VCard with ID = " + vcard.phone_number)
}

const deleteVCard = (vcard) => {
    axios
        .delete("vcards/" + vcard.phone_number)
        .then((response) => {
            let deleteVCard = response.data.data
            loadVCards()
        })
        .catch((error) => {
            console.log(error)
        })
}

// const createVCard = async (newVCard) => {
//     console.log("addVCard() called with:", newVCard);
//     try {
//         await axios.post("vcards", newVCard);
//         console.log("VCard created successfully");
//         success.value = "VCard created successfully"; 
//         error.value = null;
//     } catch (e) {
//         console.error("Error creating VCard:", e);
//         success.value = null;
//         error.value = e.response.data.errors;
//     }
// };

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
        <div class="mx-2 mt-2 flex-grow-1 filter-div"></div>
        <div class="mx-2 mt-2">
            <router-link
    class="nav-link w-100 me-3"
    :class="{ active: $route.name === 'VCardCreate' }"
    :to="{ name: 'VCardCreate' }"
>
    <button type="button" class="btn btn-success px-4 btn-addtask">
        <i class="bi bi-xs bi-plus-circle"></i>&nbsp; Add VCard
    </button>
</router-link>


        </div>
    </div>
    <VCardTable
        :vcards="vcards"
        :showPhoneNumber="true"
        @edit="editVCard"
        @delete="deleteVCard"
    ></VCardTable>
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
