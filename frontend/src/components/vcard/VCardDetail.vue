<script setup>
import { ref, computed, watch, inject } from "vue"
import avatarNoneUrl from "@/assets/avatar-none.png"
import { useToast } from "vue-toastification"
import { useUserStore } from "../../stores/user"
import { RouterLink } from "vue-router"

const serverBaseUrl = inject("apiDomain")
const userStore = useUserStore()

const toast = useToast()

const props = defineProps({
    vcard: {
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

const editingVCard = ref(props.vcard)

const inputPhotoFile = ref(null)
const editingImageAsBase64 = ref(null)
const deletePhotoOnTheServer = ref(false)

watch(
    () => props.vcard,
    (newVCard) => {
        editingVCard.value = newVCard
    },
    { immediate: true },
)

const photoFullUrl = computed(() => {
    if (deletePhotoOnTheServer.value) {
        return avatarNoneUrl
    }
    if (editingImageAsBase64.value) {
        return editingImageAsBase64.value
    } else {
        return editingVCard.value.photo_url
            ? serverBaseUrl + "/storage/fotos/" + editingVCard.value.photo_url
            : avatarNoneUrl
    }
})

const vcardTitle = computed(() => {
    if (!editingVCard.value) {
        return ""
    }
    return props.inserting ? "Register a new Vcard" : "Vcard #" + editingVCard.value.phone_number
})

const save = () => {
    const vcardToSave = editingVCard.value
    vcardToSave.deletePhotoOnServer = deletePhotoOnTheServer.value
    vcardToSave.base64ImagePhoto = editingImageAsBase64.value
    // Adicionando a validação de senha
    if (
        props.inserting &&
        editingVCard.value.password !== editingVCard.value.password_confirmation
    ) {
        toast.error("Passwords do not match")
    } else {
        emit("save", vcardToSave)
    }
}
const cancel = () => {
    emit("cancel", editingVCard.value)
}

const changePhotoFile = () => {
    try {
        const file = inputPhotoFile.value.files[0]
        if (!file) {
            editingImageAsBase64.value = null
        } else {
            const reader = new FileReader()
            reader.addEventListener(
                "load",
                () => {
                    // convert image file to base64 string
                    editingImageAsBase64.value = reader.result
                    deletePhotoOnTheServer.value = false
                },
                false,
            )
            if (file) {
                reader.readAsDataURL(file)
            }
        }
    } catch (error) {
        editingImageAsBase64.value = null
    }
}

const resetToOriginalPhoto = () => {
    deletePhotoOnTheServer.value = false
    inputPhotoFile.value.value = ""
    changePhotoFile()
}

const cleanPhoto = () => {
    deletePhotoOnTheServer.value = true
}
</script>

<template>
    <form class="row g-3 needs-validation" novalidate @submit.prevent="save">
        <h3 class="mt-5 mb-3">{{ vcardTitle }}</h3>
        <hr />
        <div class="d-flex flex-wrap justify-content-between">
            <div class="w-75 pe-4">
                <div class="mb-3">
                    <label for="phone_number">Phone Number:</label>
                    <input v-model="editingVCard.phone_number" type="text" id="VCardPhoneNumber"
                        :class="{ 'is-invalid': errors ? errors['phone_number'] : false }" required />
                    <field-error-message :errors="errors" fieldName="phone_number"></field-error-message>
                </div>

                <div class="mb-3 px-1">
                    <label for="name">Name:</label>
                    <input v-model="editingVCard.name" type="text" id="VCardName"
                        :class="{ 'is-invalid': errors ? errors['name'] : false }" required />
                    <field-error-message :errors="errors" fieldName="name"></field-error-message>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input v-model="editingVCard.email" type="email" id="VCardEmail"
                        :class="{ 'is-invalid': errors ? errors['email'] : false }" required />
                    <field-error-message :errors="errors" fieldName="email"></field-error-message>
                </div>

                <div class="mb-3" v-if="inserting">
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input v-model="editingVCard.password" type="password" id="VCardPassword" class="form-control"
                            required />
                    </div>
                    <br />
                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password:</label>
                        <input v-model="editingVCard.password_confirmation" type="password" id="VCardConfirmPassword"
                            class="form-control" :class="{
                                'is-invalid': errors ? errors['password_confirmation'] : false,
                            }" required />
                        <div class="invalid-feedback">Passwords do not match.</div>
                    </div>
                </div>

                <div class="mb-3" v-if="inserting">
                    <div class="form-group">
                        <label for="confirmation_code">Confirmation Code:</label>
                        <input v-model="editingVCard.confirmation_code" type="text" id="VCard_confirmation_code"
                            class="form-control" required />
                    </div>
                </div>
            </div>

            <div class="w-25">
                <div class="d-flex flex-column">
                    <label class="form-label">Photo</label>
                    <div class="form-control text-center">
                        <img :src="photoFullUrl" class="w-50" />
                    </div>
                    <div class="mt-3 d-flex justify-content-between flex-wrap">
                        <label for="inputPhoto" class="btn btn-dark flex-grow-1 mx-1">Carregar</label>
                        <button class="btn btn-secondary flex-grow-1 mx-1" @click.prevent="resetToOriginalPhoto"
                            v-if="editingVCard.photo_url">
                            Repor
                        </button>
                        <button class="btn btn-danger flex-grow-1 mx-1" @click.prevent="cleanPhoto"
                            v-show="editingVCard.photo_url || editingImageAsBase64">
                            Apagar
                        </button>
                    </div>
                    <div>
                        <field-error-message :errors="errors" fieldName="base64ImagePhoto"></field-error-message>
                    </div>
                </div>
            </div>
        </div>
        <hr />
        <div class="d-flex justify-content-between">
            <div class="mt-2" v-if="!inserting">
                <RouterLink :to="{ name: 'ConfirmationCode', params: { phone_number: editingVCard.phone_number } }"
                    class="btn btn-success px-5 mx-2">Change confirmation code</RouterLink>

            </div>
            <div class="mt-2">
                <button type="button" class="btn btn-primary px-5 mx-2" @click="save">Save</button>
                <button type="button" class="btn btn-light px-5 mx-2" @click="cancel">Cancel</button>
            </div>
        </div>
    </form>
    <input type="file" style="visibility: hidden" id="inputPhoto" ref="inputPhotoFile" @change="changePhotoFile" />
</template>

<style scoped>
.total_hours {
    width: 26rem;
}
</style>
