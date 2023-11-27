<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  users: {
    type: Array,
    default: () => []
  },
  showEditButton: {
    type: Boolean,
    default: true
  },
  showDeleteButton: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['edit', 'delete'])

const editingUsers = ref(props.users)

watch(
  () => props.users,
  (newUsers) => {
    editingUsers.value = newUsers
  }
)

const editClick = (user) => {
  emit('edit', user)
}

const deleteClick = (user) => {
  emit('delete', user)
}
</script>

<template>
  <table class="table">
    <thead>
      <tr>
        <th>User ID</th>
        <th>Name</th>
        <th>Email</th>
        <th v-if="showEditButton || showDeleteButton"></th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="user in editingUsers" :key="user.id">
        <td>{{ user.id }}</td>
        <td>
          {{ user.name }}
        </td>
        <td>
          <span>{{ user.email }}</span>
        </td>
        <td class="text-end" v-if="showEditButton || showDeleteButton">
          <div class="d-flex justify-content-end">
            <button class="btn btn-xs btn-light" @click="editClick(user)" v-if="showEditButton">
              <i class="bi bi-xs bi-pencil"></i>
            </button>

            <button class="btn btn-xs btn-light" @click="deleteClick(user)" v-if="showDeleteButton">
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
