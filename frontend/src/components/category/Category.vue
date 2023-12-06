<script setup>
import { onMounted, ref, watch } from "vue"
import CategoryDetail from "./CategoryDetail.vue"
import { useRouter } from "vue-router"
import { useToast } from "vue-toastification"
import axios from "axios"
import { useCategoryStore } from "../../stores/category"
const categoryStore = useCategoryStore()
import { useUserStore } from "../../stores/user"

const userStore = useUserStore()

const toast = useToast()
const router = useRouter()
const errors = ref(null)
const categoriesRef = ref([])
let originalValueStr = ""

const props = defineProps({
    id: {
        type: Number,
        default: null,
    },
})

const newCategory = () => {
    return {
        payment_type: "",
        vcard: "",
        confirmation_code: "",
        payment_reference: "",
        value: "",
        type: "",
    }
}
const category = ref(newCategory())
const inserting = (id) => {
    switch (id) {
        case -1:
            return "debit"
        case -2:
            return "credit"
        default:
            return "edit"
    }
}
const loadCategory = async (id) => {
    originalValueStr = ""
    errors.value = null
    if (inserting(id) == "debit" || inserting(id) == "credit") {
        category.value = newCategory()
    } else {
        try {
            const response = await axios.get("category/" + id)
            category.value = response.data
            originalValueStr = JSON.stringify(category.value)
        } catch (error) {
            console.error("Error loading category:", error)
        }
    }

}

const save = async (categoryToSave) => {
    errors.value = null
    if (inserting(props.id) == "debit" || inserting(props.id) == "credit") {
        try {
            categoryToSave.vcard = userStore.userPhoneNumber    
            console.log(categoryToSave)
            const response = await axios.post("category", categoryToSave)
            toast.success(response.data.message)
            router.back()
        } catch (error) {
            errors.value = error.response.data.message
            toast.error(errors.value)
        }
    } else {
        try {
            await axios.put("categories/" + props.id, categoryToSave)
            toast.success("Categorie # " + categoryToSave.id + " updated successfully")
            router.back()
        } catch (error) {
            errors.value = error.response.data.message
            toast.error("Category #" + props.id + " - " + error.value)
        }
    }
}

const cancel = () => {
    originalValueStr = JSON.stringify(category.value)
    router.back()
}

watch(
    () => props.id,
    (newValue) => {
        loadCategory(newValue)
    },
    { immediate: true },
)

</script>

<template>
    <category-detail
        :category="category"
        :errors="errors"
        :inserting="inserting(id)"
        @save="save"
        @cancel="cancel"
    ></category-detail>
</template>