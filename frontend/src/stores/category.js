import axios from 'axios'
import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { useUserStore } from "./user.js"
import { useToast } from "vue-toastification"

export const useCategoryStore = defineStore('categories', () => {
    const userStore = useUserStore()

    const categories = ref([])

    
    const totalCategories = computed(() => {
        return categories.value.length
    })
    

    function clearCategories() {
        categories.value = []
    }

    async function loadCategory() {
        console.log("Cheguei")
        try {
            const response = await axios.get("vcard/" + userStore.userPhoneNumber + "/category")
            categories.value = response.data
            return categories.value
        } catch (error) {
            console.log("Cheguei"+error)
            clearCategories()
            throw error
        }
    }
    
    return {
        categories,
        totalCategories,
        loadCategory,
        clearCategories
    }
})
