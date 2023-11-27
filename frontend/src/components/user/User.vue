<script setup>
import { ref, computed, watch } from "vue"
import UserDetail from "./UserDetail.vue" // Assuming you have a UserDetail component
import axios from "axios"
import { useToast } from "vue-toastification"
import { useRouter } from "vue-router"

const router = useRouter()
const toast = useToast()

const newUser = () => {
    return {
        username: "",
        email: "",
        // Add other user properties as needed
    }
}

const props = defineProps({
    userId: {
        type: Number,
        default: null,
    },
})

const user = ref(newUser())
const errors = ref({})

const operation = computed(() =>
    !props.userId || props.userId < 0 ? "insert" : "update",
)

const loadUser = (userId) => {
    if (!userId || userId < 0) {
        user.value = newUser()
    } else {
        axios
            .get("users/" + userId)
            .then((response) => {
                user.value = response.data.data
            })
            .catch((error) => {
                console.log(error)
            })
    }
}

const save = () => {
    if (operation.value == "insert") {
        axios
            .post("users", user.value)
            .then((response) => {
                console.log("User Created")
                console.dir(response.data.data)
                toast.success(
                    "User with ID " +
                        response.data.data.userId +
                        " created successfully",
                    router.back(),
                )
            })
            .catch((error) => {
                console.dir(error)

                if (error.response.status == 422) {
                    errors.value = error.response.data.errors
                    toast.error("Validation error")
                }
            })
    } else {
        axios
            .put("users/" + props.userId, user.value)
            .then((response) => {
                console.log("User Updated")
                console.dir(response.data.data)
                toast.success("User Updated")
                router.back()
            })
            .catch((error) => {
                errors.value = error.response.data.errors
                toast.error("Error updating User")
            })
    }
}

watch(
    () => props.userId,
    (newValue) => {
        loadUser(newValue)
    },
    { immediate: true },
)
</script>

<template>
    <UserDetail
        :user="user"
        :operationType="operation"
        @hide="closeEdit"
        @save="save"
    ></UserDetail>
</template>
