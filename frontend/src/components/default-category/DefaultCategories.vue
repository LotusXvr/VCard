<script setup>
import { ref, onMounted } from "vue"
import axios from "axios"
import { useUserStore } from "../../stores/user"
import { useRouter } from 'vue-router'
import DefaultCategoryTable from "./DefaultCategoryTable.vue"

const userStore = useUserStore()
const router = useRouter()
const categories = ref([])

const loadCategories = () => {
    axios
        .get("/default-category")
        .then((response) => {
            categories.value = response.data
            console.log(response.data)
        })
        .catch((error) => {
            console.log(error)
        })
}
const addCategory = () => {
    router.push({ name: "NewDefaultCategory" })
}   

const editCategory = (category) => {
    router.push({ name: "DefaultCategory", params: { id: category.id } })
}

const deleteCategory = (category) => {
    axios
        .delete("default-category/" + category.id)
        .then(() => {
            loadCategories()
        })
        .catch((error) => {
            console.log(error)
        })
}

onMounted(() => {
    loadCategories()
})

</script>

<template>
    <div class="d-flex justify-content-between">
        <div class="mx-2">
            <h3 class="mt-4">Default Categories</h3>
        </div>
    </div>
    <hr />
    <div class="mb-3 d-flex justify-content-between flex-wrap">
        <div class="mx-2 mt-2 flex-grow-1 filter-div"></div>
        <div class="mx-2 mt-2">
            <router-link class="nav-link w-100 me-3" :to="{ name: 'NewDefaultCategory' }">
                <button type="button" class="btn btn-success px-4 btn-addtask" @click="addCategory">
                    <i class="bi bi-xs bi-plus-circle"></i>&nbsp; Add Category
                </button>
            </router-link>
        </div>
    </div>
    <div v-if="categories.length > 0 ">
        <DefaultCategoryTable
            :categories="categories"
            :showUserId="true"
            @edit="editCategory"
            @delete="deleteCategory"
        ></DefaultCategoryTable>
    </div>
    <div v-else>No Categories yet</div>
</template>