<script setup>
import { ref, onMounted, watch, computed } from "vue"
import router from "../../router"

const props = defineProps({
    vcard: {
        type: Object,
        required: true,
    },
    operationType: {
        type: String,
        default: "insert", // insert / update
    },
})

const emit = defineEmits(["hide", "save"])

const editingVCard = ref(props.vcard)

watch(
    () => props.vcard,
    (newVCard) => {
        editingVCard.value = newVCard
    },
)

const vcardTitle = computed(() => {
    if (!editingVCard.value) {
        return ""
    }
    return props.operationType == "insert" ? "New VCard" : "VCard #" + editingVCard.value.id
})

const save = () => {
    // Instead of updating the data (task) here, we request to update it by emiting an event
    emit("save", editingVCard.value)
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
        <h3 class="mt-5 mb-3">{{ vcardTitle }}</h3>
        <hr />
        <form class="row g-3 needs-validation" novalidate @submit.prevent="save">
            <div class="form-group">
                <label for="phone_number">Phone Number:</label>
                <input
                    v-model="editingVCard.phone_number"
                    type="text"
                    id="VCardPhoneNumber"
                    class="form-control"
                    required
                />
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input
                    v-model="editingVCard.password"
                    type="password"
                    id="VCardPassword"
                    class="form-control"
                    required
                />
            </div>

            <div class="form-group">
                <label for="name">Name:</label>
                <input
                    v-model="editingVCard.name"
                    type="text"
                    id="VCardName"
                    class="form-control"
                    required
                />
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input
                    v-model="editingVCard.email"
                    type="email"
                    id="VCardEmail"
                    class="form-control"
                    required
                />
            </div>

            <div class="form-group">
                <label for="confirmation_code">Confirmation Code:</label>
                <input
                    v-model="editingVCard.confirmation_code"
                    type="text"
                    id="VCard_confirmation_code"
                    class="form-control"
                    required
                />
            </div>

            <div class="mb-3 d-flex justify-content-end">
                <button type="button" class="btn btn-primary px-5" @click="save">Save</button>
                <button type="button" class="btn btn-light px-5" @click="cancel">Cancel</button>
            </div>
        </form>
    </div>
</template>
