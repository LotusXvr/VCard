<script setup>
import { ref, onMounted } from "vue"
import axios from "axios"
import { useRouter } from 'vue-router'
import CategoryTable from "./CategoryTable.vue"
import { useCategoryStore } from "../../stores/category"
import { useToast } from "vue-toastification"

const categoryStore = useCategoryStore()
const router = useRouter()
const categories = ref([])
const toast = useToast()

const loadCategories = async () => {
    categories.value = await categoryStore.loadCategory()
}

const addCategory = () => {
    router.push({ name: "NewCategory" })
}   

const editCategory = (category) => {
    router.push({ name: "Category", params: { id: category.id } })
}

const deleteCategory = (category) => {
    axios
        .delete("category/" + category.id)
        .then(() => {
            loadCategories()
            toast.success("Category #" + category.id + " was deleted successfully.")
        })
        .catch((error) => {
            toast.error(error)
        })
}

onMounted(() => {
    loadCategories()
})

</script>

<template>
    <div class="d-flex justify-content-between">
        <div class="mx-2">
            <h3 class="mt-4">Categories</h3>
        </div>
    </div>
    <hr />
    <div class="mb-3 d-flex justify-content-between flex-wrap">
        <div class="mx-2 mt-2 flex-grow-1 filter-div"></div>
        <div class="mx-2 mt-2">
            <router-link class="nav-link w-100 me-3" :to="{ name: 'NewCategory' }">
                <button type="button" class="btn btn-success px-4 btn-addtask" @click="addCategory">
                    <i class="bi bi-xs bi-plus-circle"></i>&nbsp; Add Category
                </button>
            </router-link>
        </div>
    </div>
    <div v-if="categories.length > 0 ">
        <CategoryTable
            :categories="categories"
            :showUserId="true"
            @edit="editCategory"
            @delete="deleteCategory"
        ></CategoryTable>
    </div>
    <div v-else>No Categories yet</div>
</template>