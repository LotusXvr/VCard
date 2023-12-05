<script setup>
import axios from 'axios';
import { ref, onMounted, defineProps } from 'vue';
import VCardTable from './VCardTable.vue';
import { useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';

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

const router = useRouter();
const toast = useToast();
const vcards = ref([]);
const currentPage = ref(1);
const totalItems = ref(0);

const loadVCards = () => {
    // Change later when authentication is implemented
    axios
        .get('vcards', { params: { page: props.currentPage } })
        .then((response) => {
            const responseData = response.data;
            vcards.value = responseData.data;
            console.log(responseData);
            paginationData.value = {
                currentPage: responseData.current_page,
                lastPage: responseData.last_page,
                nextPageUrl: responseData.next_page_url,
                prevPageUrl: responseData.prev_page_url,
                per_page: responseData.per_page,
            };
            totalItems.value = responseData.total;
        })
        .catch((error) => {
            console.log(error)
        })
}

const paginationData = ref({
    currentPage: 1,
    lastPage: 1,
    nextPageUrl: null,
    prevPageUrl: null,
    per_page: 10,
});

const addVCard = () => {
    router.push({ name: "newVCard" })
}

const editVCard = (vcard) => {
    router.push({ name: "VCardUpdate", params: { phone_number: vcard.phone_number } })
}

const deleteVCard = (vcard) => {
    axios
        .delete("vcards/" + vcard.phone_number)
        .then(() => {
            toast.success("VCard deleted successfully")
            loadVCards()
        })
        .catch((error) => {
            console.log(error)
        })
}

onMounted(() => {
    loadVCards()
})

const getResults = (page) => {
    currentPage.value = page;
    loadVCards();
};

const handleStatusChange = (vcard) => {
    axios
        .patch("vcards/" + vcard.phone_number + "/change-status")
        .then(() => {
            toast.success("VCard status changed successfully")
            loadVCards()
        })
        .catch((error) => {
            console.log(error)
        })
}
</script>

<template>
    <div class="d-flex justify-content-between">
        <div class="mx-2">
            <h3 class="mt-4">{{ vcardsTitle }}</h3>
        </div>
    </div>
    <hr />
    <div v-if="!onlyCurrentVCards" class="mb-3 d-flex justify-content-between flex-wrap">
        <div class="mx-2 mt-2 flex-grow-1 filter-div"></div>
        <div class="mx-2 mt-2">
            <router-link class="nav-link w-100 me-3" :to="{ name: 'NewVCard' }">
                <button type="button" class="btn btn-success px-4 btn-addtask" @click="addVCard">
                    <i class="bi bi-xs bi-plus-circle"></i>&nbsp; Add VCard
                </button>
            </router-link>
        </div>
    </div>
    <VCardTable :vcards="vcards" :showPhoneNumber="true" @edit="editVCard" @delete="deleteVCard"
        @changeStatus="handleStatusChange"></VCardTable>
    <div>
        <ul class="pagination">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <a class="page-link" href="#" @click.prevent="getResults(currentPage - 1)">Previous</a>
            </li>
            <li class="page-item" v-for="page in paginationData.lastPage" :key="page"
                :class="{ active: currentPage === page }">
                <a class="page-link" href="#" @click.prevent="getResults(page)">{{ page }}</a>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === paginationData.lastPage }">
                <a class="page-link" href="#" @click.prevent="getResults(currentPage + 1)">Next</a>
            </li>
        </ul>
    </div>
</template>
