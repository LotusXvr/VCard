<script setup>
import axios from 'axios'
import { ref, onMounted } from 'vue'
import UserTable from './UserTable.vue'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'

const router = useRouter()
const toast = useToast()

const users = ref([])

const loadUsers = () => {
  // Altere mais tarde quando a autenticação for implementada
  axios
    .get('admins')
    .then((response) => {
      users.value = response.data
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
  axios
    .delete('admins/' + user.id)
    .then(() => {
      toast.success('User deleted successfully')
      loadUsers()
    })
    .catch((error) => {
      toast.error(error.response.data.message)
    })
}

onMounted(() => {
  loadUsers()
})
</script>

<template>
  <div class="d-flex justify-content-between">
    <div class="mx-2">
      <h3 class="mt-4">{{ usersTitle }}</h3>
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
  </div>
  <div v-else>Loading Users...</div>
</template>
