<script setup>
import { computed } from "vue"

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
})

const emit = defineEmits(["edit", "delete"])

// Separate categories into debit (D) and credit (C)
const categoriesD = computed(() => props.categories.filter(category => category.type === 'D').sort((a, b) => a.name.localeCompare(b.name)))
const categoriesC = computed(() => props.categories.filter(category => category.type === 'C').sort((a, b) => a.name.localeCompare(b.name)))

const editClick = (category) => {
    emit("edit", category)
}

const deleteClick = (category) => {
    emit("delete", category)
}

</script>

<template>
    <div class="d-flex">
      <div class="flex-fill me-4">
        <h2>Debit Categories</h2>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Name</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="category in categoriesD" :key="category.id">
              <td>{{ category.name }}</td>
              <td class="text-end">
                <button class="btn btn-xs btn-light me-1" @click="editClick(category)">
                  <i class="bi bi-xs bi-pencil"></i>
                </button>
                <button class="btn btn-xs btn-light" @click="deleteClick(category)">
                  <i class="bi bi-xs bi-x-square-fill"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
  
      <div class="flex-fill">
        <h2>Credit Categories</h2>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Name</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="category in categoriesC" :key="category.id">
              <td>{{ category.name }}</td>
              <td class="text-end">
                <button class="btn btn-xs btn-light me-1" @click="editClick(category)">
                  <i class="bi bi-xs bi-pencil"></i>
                </button>
                <button class="btn btn-xs btn-light" @click="deleteClick(category)">
                  <i class="bi bi-xs bi-x-square-fill"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </template>  
  