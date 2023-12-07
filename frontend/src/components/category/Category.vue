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
let originalValueStr = ""

const props = defineProps({
    id: {
        type: Number,
        default: null,
    },
})

const newCategory = () => {
    return {
        id: null,
        vcard: userStore.userPhoneNumber,
        name: "",
        type: "",
    }
}
const category = ref(newCategory())
const inserting = (id) => !id || id < 0

const loadCategory = async (id) => {
    originalValueStr = ""
    errors.value = null
    if (inserting(id)) {
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
    if (inserting(props.id)) {
        try {
            const response = await axios.post("category", categoryToSave)
            category.value = response.data
            console.log("category.value", category.value)
            originalValueStr = JSON.stringify(category.value)
            toast.success("Category #" + category.value.name + " was registered successfully.")
            router.back()
        } catch (error) {
            if (error.response.status === 422){
                error.response.data.message == null ? toast.error(error.response.data.errors.name[0]) : toast.error(error.response.data.message);
            }else {
                toast.error("An error occurred while processing the request.");
            }
        }
    } else {
        try {
            const response = await axios.put("category/" + props.id, categoryToSave)
            category.value = response.data
            originalValueStr = JSON.stringify(category.value)
            toast.success("Category #" + category.value.id + " was updated successfully.")
            router.back()
        } catch (error) {
            if (error.response.status == 422) {
                errors.value = error.response.data.errors
                toast.error("Category #" + props.id + " was not updated due to validation errors!")
            } else {
                toast.error("Category #" + props.id + " was not updated due to unknown server error!")
            }
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
    <categoryDetail
        :category="category"
        :errors="errors"
        :inserting="inserting(id)"
        @save="save"
        @cancel="cancel"
    ></categoryDetail>
</template>