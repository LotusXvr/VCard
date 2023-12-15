<script setup>
import axios from 'axios'
import { ref, onMounted, inject } from 'vue'
import UserTable from './UserTable.vue'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import { Bootstrap5Pagination } from 'laravel-vue-pagination'

const router = useRouter()
const toast = useToast()
const socket = inject('socket')

const users = ref([])
const paginationData = ref({})
const name = ref('')
const orderBy = ref('id')
const orderFormat = ref('asc')
const loadUsers = (page = 1) => {
  axios
    .get('admins', {
      params: {
        page: page,
        name: name.value,
        orderBy: orderBy.value,
        orderFormat: orderFormat.value
      }
    })
    .then((response) => {
      users.value = response.data.data
      paginationData.value = response.data
      console.log(response.data)
    })
    .catch((error) => {
      toast.error(error.response.data.message)
    })
}

const addUser = () => {
  router.push({ name: 'NewUser' }) // Certifique-se de ter uma rota chamada 'newUser'
}

const editUser = (user) => {
  router.push({ name: 'User', params: { id: user.id } }) // Certifique-se de ter uma rota chamada 'User'
}

const deleteUser = (user) => {
  const userID = user.id
  axios
    .delete('admins/' + user.id)
    .then(() => {
      toast.success('User deleted successfully')
      socket.emit('deletedUser', userID)
      loadUsers()
    })
    .catch((error) => {
      toast.error(error.response.data.message)
    })
}

onMounted(() => {
  loadUsers()
})

const clearFilters = () => {
  name.value = ''
  orderBy.value = 'id'
  orderFormat.value = 'asc'
  loadUsers()
}
</script>

<template>
  <div class="d-flex justify-content-between">
    <div class="mx-2">
      <h3 class="mt-4">{{ usersTitle }}</h3>
    </div>
  </div>
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
            <option value="id">id</option>
            <option value="name">Name</option>
            <option value="email">Email</option>
          </select>
        </div>
      </div>
      <div class="col-md-3">
        <div class="mb-3">
          <label for="orderBy" class="form-label">Order format:</label>
          <select class="form-select" v-model="orderFormat">
            <option value="asc">Ascending</option>
            <option value="desc">Descendig</option>
          </select>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-3">
        <!-- Button to apply filters -->
        <div class="mb-3">
          <button @click="loadUsers" class="btn btn-primary">Apply Filters</button>
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
  <div class="mb-3 d-flex justify-content-between flex-wrap">
    <div class="mx-2 mt-2 flex-grow-1 filter-div"></div>
    <div class="mx-2 mt-2">
      <router-link class="nav-link w-100 me-3" :to="{ name: 'NewUser' }">
        <button type="button" class="btn btn-success px-4 btn-addtask" @click="addUser">
          <i class="bi bi-xs bi-plus-circle"></i>&nbsp; Add User
        </button>
      </router-link>
    </div>
  </div>
  <div v-if="users.length > 0">
    <UserTable :users="users" :showUserId="true" @edit="editUser" @delete="deleteUser"></UserTable>
    <Bootstrap5Pagination
      :data="paginationData"
      @pagination-change-page="loadUsers"
      :limit="2"
    ></Bootstrap5Pagination>
  </div>
  <div v-else>Loading Users...</div>
</template>
