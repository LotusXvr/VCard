<script setup>
import { ref, onMounted } from "vue"

const newUser = ref({
    name: "",
    email: "",
    password: "",
})
//const vcardInput = ref(null)

const emit = defineEmits(["addUser"])

const createUser = () => {
    // Create a new vCard
    emit("addUser", {
        password: newUser.value.password,
        name: newUser.value.name,
        email: newUser.value.email,
    })
    // Clear the input
    newUser.value = {
        password: "",
        name: "",
        email: "",
    }
}

onMounted(() => {
    // Focus the input when the component is mounted
})
</script>

<template>
    <div>
        <h2>Create a New User</h2>
        <form @submit.prevent="createUser">
            <div class="form-group">
                <label for="name">Name:</label>
                <input
                    v-model="newUser.name"
                    type="text"
                    id="UserName"
                    class="form-control"
                    required
                />
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input
                    v-model="newUser.email"
                    type="email"
                    id="UserEmail"
                    class="form-control"
                    required
                />
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input
                    v-model="newUser.password"
                    type="password"
                    id="UserPassword"
                    class="form-control"
                    required
                />
            </div>

            <button type="submit" class="btn btn-primary">Create User</button>
        </form>
    </div>
</template>
