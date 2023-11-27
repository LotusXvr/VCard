<script setup>
import { ref, onMounted, watch, defineProps, defineEmits } from "vue"
import { useRouter } from "vue-router"

const router = useRouter()

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    operationType: {
        type: String,
        default: "insert", // insert / update
    },
})

const emit = defineEmits(["hide", "save"])
 
const editingUser = ref(props.user)

const userTitle = ref(getUserTitle())

watch(
    () => props.user,
    (newUser) => {
        editingUser.value = newUser
        userTitle.value = getUserTitle()
    },
)

function getUserTitle() {
    if (!editingUser.value) {
        return ""
    }
    return props.operationType == "insert"
        ? "New User"
        : "User #" + editingUser.value.userId // Assuming you have a userId property
}

const confirmPassword = ref("") // New variable for Confirm Password

const validatePasswordMatch = () => {
    return confirmPassword.value === editingUser.value.password
}

const save = () => {
    // Instead of updating the data (task) here, we request to update it by emitting an event
    emit("save", editingUser.value)
}

const cancel = () => {
    router.back()
}

onMounted(() => {
    // Initializing with the focus on the input
})
</script>

<template>
    <div>
        <h3 class="mt-5 mb-3">{{ userTitle }}</h3>
        <hr />
        <form class="row g-3 needs-validation" novalidate @submit.prevent="save">
            <div class="form-group">
                <label for="name">Name:</label>
                <input
                    v-model="editingUser.name"
                    type="text"
                    id="name"
                    class="form-control"
                    required
                    :readonly="props.operationType !== 'insert'"
                />
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input
                    v-model="editingUser.email"
                    type="email"
                    id="UserEmail"
                    class="form-control"
                    required
                />
            </div>

            <div v-if="props.operationType === 'insert'">
                <hr />
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input
                        v-model="editingUser.password"
                        type="password"
                        id="UserPassword"
                        class="form-control"
                        required
                    />
                </div>
                <br />
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password:</label>
                    <input
                        v-model="confirmPassword"
                        type="password"
                        id="UserConfirmPassword"
                        class="form-control"
                        :class="{ 'is-invalid': !validatePasswordMatch() }"
                        required
                    />
                    <div class="invalid-feedback">Passwords do not match.</div>
                </div>
            </div>

            <!-- Add other user properties as needed -->

            <div class="mb-3 d-flex justify-content-end" style="margin-top: 10px">
                <button type="button" class="btn btn-primary px-5" @click="save">Save</button>
                <button type="button" class="btn btn-light px-5" @click="cancel">Cancel</button>
            </div>
        </form>
    </div>
</template>
