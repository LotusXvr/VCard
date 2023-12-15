<script setup>
import { ref, computed, watch } from "vue"
import { useToast } from "vue-toastification"

const toast = useToast()

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    inserting: {
        type: Boolean,
        default: false,
    },
    errors: {
        type: Object,
        required: false,
    },
})

const emit = defineEmits(["save", "cancel"])

const editingUser = ref(props.user)

watch(
    () => props.user,
    (newUser) => {
        editingUser.value = newUser
    },
    { immediate: true },
)

const userTitle = computed(() => {
    if (!editingUser.value) {
        return ""
    }
    return props.inserting ? "Register a new user" : "User #" + editingUser.value.id
})

const save = () => {
    const userToSave = editingUser.value

    if (props.inserting && editingUser.value.password !== editingUser.value.password_confirmation) {
        toast.error("Passwords do not match")
    } else {
        emit("save", userToSave)
    }
}

const cancel = () => {
    emit("cancel", editingUser.value)
}
</script>

<template>
    <form class="row g-3 needs-validation" novalidate @submit.prevent="save">
        <h3 class="mt-5 mb-3">{{ userTitle }}</h3>
        <hr />
        <div class="d-flex flex-wrap justify-content-between">
            <div class="w-75 pe-4">
                <div class="mb-3">
                    <label for="inputName" class="form-label">Name</label>
                    <input
                        type="text"
                        class="form-control"
                        :class="{ 'is-invalid': errors ? errors['name'] : false }"
                        id="inputName"
                        v-model="editingUser.name"
                        required
                    />
                    <field-error-message :errors="errors" fieldName="name"></field-error-message>
                </div>

                <div class="mb-3 px-1">
                    <label for="inputEmail" class="form-label">Email</label>
                    <input
                        type="email"
                        class="form-control"
                        :class="{ 'is-invalid': errors ? errors['email'] : false }"
                        id="inputEmail"
                        required
                        v-model="editingUser.email"
                    />
                    <field-error-message :errors="errors" fieldName="email"></field-error-message>
                </div>

                <div class="mb-3" v-if="inserting">
                    <label for="inputPassword" class="form-label">Password</label>
                    <input
                        type="password"
                        class="form-control"
                        :class="{ 'is-invalid': errors ? errors['password'] : false }"
                        id="inputPassword"
                        v-model="editingUser.password"
                    />
                    <field-error-message
                        :errors="errors"
                        fieldName="password"
                    ></field-error-message>
                </div>
                <div class="mb-3" v-if="inserting">
                    <label for="inputPasswordConfirmation" class="form-label"
                        >Password Confirmation</label
                    >
                    <input
                        type="password"
                        class="form-control"
                        :class="{ 'is-invalid': errors ? errors['password_confirmation'] : false }"
                        id="inputPasswordConfirmation"
                        v-model="editingUser.password_confirmation"
                    />
                    <field-error-message
                        :errors="errors"
                        fieldName="password_confirmation"
                    ></field-error-message>
                </div>
            </div>
        </div>
        <hr />
        <div class="mt-2 d-flex justify-content-end">
            <button type="button" class="btn btn-primary px-5 mx-2" @click="save">Save</button>
            <button type="button" class="btn btn-light px-5 mx-2" @click="cancel">Cancel</button>
        </div>
    </form>
</template>

<style scoped>
.total_hours {
    width: 26rem;
}
</style>
