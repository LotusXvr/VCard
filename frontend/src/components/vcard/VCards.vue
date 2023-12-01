<script setup>
import axios from 'axios'
import { ref, onMounted } from 'vue'
import VCardTable from './VCardTable.vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const props = defineProps({
  vcardsTitle: {
    type: String,
    default: 'VCards'
  },
  onlyCurrentVCards: {
    type: Boolean,
    default: false
  }
})

const vcards = ref([])

const loadVCards = () => {
  // Change later when authentication is implemented
  axios
    .get('vcards')
    .then((response) => {
      vcards.value = response.data
      console.log(response.data)
    })
    .catch((error) => {
      console.log(error)
    })
}

const addVCard = () => {
  router.push({ name: 'newVCard' })
}

const editVCard = (vcard) => {
  router.push({ name: 'VCard', params: { id: vcard.phone_number } })
}

const deleteVCard = (vcard) => {
  axios
    .delete('vcards/' + vcard.phone_number)
    .then(() => {
      loadVCards()
    })
    .catch((error) => {
      console.log(error)
    })
}

onMounted(() => {
  loadVCards()
})
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
  <VCardTable :vcards="vcards" :showPhoneNumber="true" @edit="editVCard" @delete="deleteVCard"></VCardTable>
</template>
