<script setup>
import axios from "axios"
import { ref, onMounted, defineProps, nextTick } from "vue"
import VCardTable from "./VCardTable.vue"
import { useRouter } from "vue-router"
import { useToast } from "vue-toastification"

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

const router = useRouter()
const toast = useToast()
const vcards = ref([])
const currentPage = ref(1)
const totalItems = ref(0)
const filteredVCards = ref([])

const loadVCards = () => {
    // Change later when authentication is implemented
    axios
        .get("vcards", { params: { page: props.currentPage } })
        .then((response) => {
            const responseData = response.data
            vcards.value = responseData.data
            filteredVCards.value = vcards.value
            console.log(responseData)
            paginationData.value = {
                currentPage: responseData.current_page,
                lastPage: responseData.last_page,
                nextPageUrl: responseData.next_page_url,
                prevPageUrl: responseData.prev_page_url,
                per_page: responseData.per_page,
            }
            totalItems.value = responseData.total
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
})

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
    currentPage.value = page
    loadVCards()
}

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

const renderComponent = ref(true)

const forceRerender = async () => {
    // Remove MyComponent from the DOM
    renderComponent.value = false

    // Wait for the change to get flushed to the DOM
    await nextTick()

    // Add the component back in
    renderComponent.value = true
}

const blocked = ref(null)
const name = ref("")
const orderBy = ref("phoneNumber")

const applyFilters = () => {
    filteredVCards.value = vcards.value.filter((vcard) => {
        // Check if name on the vcard has the name given
        const nameOnVCard = vcard.name.toLowerCase()
        const nameToSearch = name.value.toLowerCase()
        const hasName = nameToSearch ? nameOnVCard.includes(nameToSearch) : true

        // Check if vcard is blocked
        const blockedNumber = blocked.value ? 1 : 0
        const isBlocked = blockedNumber ? vcard.blocked === blockedNumber : true

        return isBlocked && hasName
    })

    // Order the filteredVCards based on the selected orderBy parameter
    filteredVCards.value.sort((a, b) => {
        if (orderBy.value === "phoneNumber") {
            return a.phoneNumber.localeCompare(b.phoneNumber)
        } else if (orderBy.value === "name") {
            return a.name.localeCompare(b.name)
        } else if (orderBy.value === "balance") {
            return b.balance - a.balance
        } else {
            return 0 // Default order (no change)
        }
    })

    console.log(filteredVCards.value)
    forceRerender()
}

const clearFilters = () => {
    blocked.value = null
    filteredVCards.value = vcards.value
    forceRerender()
}
</script>

<template>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <!-- Filter inputs -->
                <div class="mb-3">
                    <label for="type" class="form-label">Name:</label>
                    <input type="text" class="form-control" v-model="name" />
                </div>
            </div>
            <div class="col-md-3">
                <!-- Filter inputs -->
                <div class="mb-3">
                    <label for="orderBy" class="form-label">Order by:</label>
                    <select class="form-select" v-model="orderBy">
                        <option value="phoneNumber">Phone Number</option>
                        <option value="name">Name</option>
                        <option value="balance">Balance</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <!-- Filter inputs -->
                <div class="mb-3 form-check">
                    <input
                        class="form-check-input custom-checkbox"
                        type="checkbox"
                        id="flexCheckDefault"
                        v-model="blocked"
                        true-value="true"
                        false-value="false"
                    />
                    <label class="form-check-label" for="flexCheckDefault"> Blocked </label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <!-- Button to apply filters -->
                <div class="mb-3">
                    <button @click="applyFilters" class="btn btn-primary">Apply Filters</button>
                </div>
            </div>

            <div class="col-md-3">
                <!-- Button to clear filters and show all transactions -->
                <div class="mb-3">
                    <button @click="clearFilters" class="btn btn-secondary">Clear Filters</button>
                </div>
            </div>
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
    <VCardTable
        :vcards="filteredVCards"
        :showPhoneNumber="true"
        @edit="editVCard"
        @delete="deleteVCard"
        @changeStatus="handleStatusChange"
    ></VCardTable>
    <div>
        <ul class="pagination">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <a class="page-link" href="#" @click.prevent="getResults(currentPage - 1)"
                    >Previous</a
                >
            </li>
            <li
                class="page-item"
                v-for="page in paginationData.lastPage"
                :key="page"
                :class="{ active: currentPage === page }"
            >
                <a class="page-link" href="#" @click.prevent="getResults(page)">{{ page }}</a>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === paginationData.lastPage }">
                <a class="page-link" href="#" @click.prevent="getResults(currentPage + 1)">Next</a>
            </li>
        </ul>
    </div>
</template>
