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
        try {
            const response = await axios.get('category', 
            {
                phone_number :userStore.userPhoneNumber
            })
            categories.value = response.data.data
            return categories.value
        } catch (error) {
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
