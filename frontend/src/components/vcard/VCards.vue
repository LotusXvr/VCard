<script setup>
import axios from 'axios'
import { ref, onMounted } from 'vue'
import VCardTable from './VCardTable.vue'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import { Bootstrap5Pagination } from 'laravel-vue-pagination'

const router = useRouter()
const toast = useToast()
const vcards = ref([])
const filteredVCards = ref([])
const paginationData = ref({})

const blocked = ref('')
const name = ref('')
const orderBy = ref('phone_number')
const orderFormat = ref('asc')

const loadVCards = (page = 1) => {
  axios
    .get('vcards', {
      params: {
        page: page,
        name: name.value,
        blocked: blocked.value,
        orderBy: orderBy.value,
        orderFormat: orderFormat.value
      }
    })
    .then((response) => {
      const responseData = response.data
      vcards.value = responseData.data
      filteredVCards.value = vcards.value
      paginationData.value = responseData
    })
    .catch((error) => {
      console.log(error)
    })
}

const addVCard = () => {
  router.push({ name: 'newVCard' })
}

const editVCard = (vcard) => {
  router.push({ name: 'VCardUpdate', params: { phone_number: vcard.phone_number } })
}

const deleteVCard = (vcard) => {
  axios
    .delete('vcards/' + vcard.phone_number)
    .then(() => {
      toast.success('VCard deleted successfully')
      loadVCards()
    })
    .catch((error) => {
      toast.error(error.response.data.message)
    })
}

const handleStatusChange = (vcard) => {
  axios
    .patch('vcards/' + vcard.phone_number + '/change-status')
    .then(() => {
      toast.success('VCard status changed successfully')
      loadVCards()
    })
    .catch((error) => {
      console.log(error)
    })
}

const applyFilters = () => {
  loadVCards()
}

const clearFilters = () => {
  blocked.value = ''
  orderBy.value = 'phone_number'
  name.value = ''
  orderFormat.value = 'asc'
  loadVCards()
}

onMounted(() => {
  loadVCards()
})
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
            <option value="phone_number">Phone Number</option>
            <option value="name">Name</option>
            <option value="balance">Balance</option>
          </select>
        </div>
        <div class="mb-3 mt-3">
          <label for="orderBy" class="form-label">Order format:</label>
          <select class="form-select" v-model="orderFormat">
            <option value="asc">Ascending</option>
            <option value="desc">Descendig</option>
          </select>
        </div>
      </div>
      <div class="col-md-3">
        <!-- Filter inputs -->
        <div class="mb-3">
          <label for="blocked" class="form-label">Block status:</label>
          <select class="form-select" v-model="blocked">
            <option value="">All</option>
            <option value="0">Not Blocked</option>
            <option value="1">Blocked</option>
          </select>
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
  <VCardTable :vcards="filteredVCards" :showPhoneNumber="true" @edit="editVCard" @delete="deleteVCard"
    @changeStatus="handleStatusChange"></VCardTable>

  <Bootstrap5Pagination :data="paginationData" @pagination-change-page="loadVCards" :limit="1"></Bootstrap5Pagination>
</template>
