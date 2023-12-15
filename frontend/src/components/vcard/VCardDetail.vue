<script setup>
import { ref, computed, watch, inject, onMounted } from 'vue'
import avatarNoneUrl from '@/assets/avatar-none.png'
import { useToast } from 'vue-toastification'
import { useUserStore } from '../../stores/user'
import { RouterLink } from 'vue-router'

const serverBaseUrl = inject('apiDomain')
const userStore = useUserStore()

const toast = useToast()

const props = defineProps({
  vcard: {
    type: Object,
    required: true
  },
  inserting: {
    type: Boolean,
    default: false
  },
  errors: {
    type: Object,
    required: false
  },
  details: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['save', 'cancel'])

const editingVCard = ref(props.vcard)

const inputPhotoFile = ref(null)
const editingImageAsBase64 = ref(null)
const deletePhotoOnTheServer = ref(false)

watch(
  () => props.vcard,
  (newVCard) => {
    editingVCard.value = newVCard
  },
  { immediate: true }
)

const photoFullUrl = computed(() => {
  if (deletePhotoOnTheServer.value) {
    return avatarNoneUrl
  }
  if (editingImageAsBase64.value) {
    return editingImageAsBase64.value
  } else {
    return editingVCard.value.photo_url
      ? serverBaseUrl + '/storage/fotos/' + editingVCard.value.photo_url
      : avatarNoneUrl
  }
})

const vcardTitle = computed(() => {
  if (!editingVCard.value) {
    return ''
  }

  return props.inserting ? 'Register a new Vcard' : 'Vcard #' + editingVCard.value.phone_number
})

const save = () => {
  const vcardToSave = editingVCard.value
  vcardToSave.deletePhotoOnServer = deletePhotoOnTheServer.value
  vcardToSave.base64ImagePhoto = editingImageAsBase64.value

  if (props.inserting && editingVCard.value.password !== editingVCard.value.password_confirmation) {
    toast.error('Passwords do not match')
  } else {
    emit('save', vcardToSave)
  }
}
const cancel = () => {
  emit('cancel', editingVCard.value)
}

const changePhotoFile = () => {
  try {
    const file = inputPhotoFile.value.files[0]
    if (!file) {
      editingImageAsBase64.value = null
    } else {
      const reader = new FileReader()
      reader.addEventListener(
        'load',
        () => {
          // convert image file to base64 string
          editingImageAsBase64.value = reader.result
          deletePhotoOnTheServer.value = false
        },
        false
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
  inputPhotoFile.value.value = ''
  changePhotoFile()
}

const cleanPhoto = () => {
  deletePhotoOnTheServer.value = true
}
</script>

<template>
  <form class="row g-3 needs-validation" novalidate @submit.prevent="save" v-if="!props.details">
    <h3 class="mt-5 mb-3">{{ vcardTitle }}</h3>
    <hr />

    <div class="d-flex flex-wrap justify-content-between" v-if="userStore.userType !== 'A'">
      <div class="col-md-6 mx-5">
        <!-- Phone Number -->
        <div class="mb-3" v-if="userStore.userType !== 'V'">
          <label for="phone_number" class="form-label">Phone Number:</label>
          <input
            v-model.lazy="editingVCard.phone_number"
            type="text"
            id="VCardPhoneNumber"
            class="form-control"
            :class="{ 'is-invalid': errors ? errors['phone_number'] : false }"
            required
          />
          <field-error-message :errors="errors" fieldName="phone_number"></field-error-message>
        </div>

        <!-- Name -->
        <div class="mb-3">
          <label for="name" class="form-label">Name:</label>
          <input
            v-model="editingVCard.name"
            type="text"
            id="VCardName"
            class="form-control"
            :class="{ 'is-invalid': errors ? errors['name'] : false }"
            required
          />
          <field-error-message :errors="errors" fieldName="name"></field-error-message>
        </div>

        <!-- Email -->
        <div class="mb-3">
          <label for="email" class="form-label">Email:</label>
          <input
            v-model="editingVCard.email"
            type="email"
            id="VCardEmail"
            class="form-control"
            :class="{ 'is-invalid': errors ? errors['email'] : false }"
            required
          />
          <field-error-message :errors="errors" fieldName="email"></field-error-message>
        </div>

        <!-- Password and Confirm Password -->
        <div class="mb-3" v-if="inserting">
          <div class="form-group">
            <label for="password" class="form-label">Password:</label>
            <input
              v-model="editingVCard.password"
              type="password"
              id="VCardPassword"
              class="form-control"
              required
            />
            <field-error-message :errors="errors" fieldName="password"></field-error-message>
          </div>

          <div class="form-group mt-3">
            <label for="confirmPassword" class="form-label">Confirm Password:</label>
            <input
              v-model="editingVCard.password_confirmation"
              type="password"
              id="VCardConfirmPassword"
              class="form-control"
              :class="{ 'is-invalid': errors ? errors['password_confirmation'] : false }"
              required
            />
            <div class="invalid-feedback">Passwords do not match.</div>
          </div>
        </div>

        <!-- Confirmation Code -->
        <div class="mb-3" v-if="inserting">
          <div class="form-group">
            <label for="confirmation_code" class="form-label">Confirmation Code:</label>
            <input
              v-model="editingVCard.confirmation_code"
              type="text"
              id="VCard_confirmation_code"
              class="form-control"
              required
            />
            <field-error-message
              :errors="errors"
              fieldName="confirmation_code"
            ></field-error-message>
          </div>
        </div>
      </div>

      <!-- Photo Section -->
      <div class="col-md-4 mx-5">
        <div class="d-flex flex-column align-items-center">
          <label class="form-label">Photo</label>
          <div class="form-control text-center">
            <img :src="photoFullUrl" class="w-50" />
          </div>
          <div class="mt-3 d-flex justify-content-between flex-wrap">
            <label for="inputPhoto" class="btn btn-dark flex-grow-1 mx-1">Upload</label>
            <button
              class="btn btn-secondary flex-grow-1 mx-1"
              @click.prevent="resetToOriginalPhoto"
              v-if="editingVCard.photo_url"
            >
              Reset
            </button>
            <button
              class="btn btn-danger flex-grow-1 mx-1"
              @click.prevent="cleanPhoto"
              v-show="editingVCard.photo_url || editingImageAsBase64"
            >
              Delete
            </button>
          </div>
          <div>
            <field-error-message
              :errors="errors"
              fieldName="base64ImagePhoto"
            ></field-error-message>
          </div>
        </div>
      </div>
    </div>

    <!-- Max Debit Section -->
    <div class="mb-3" v-if="userStore.userType === 'A'">
      <label for="max_debit" class="form-label">Max Debit:</label>
      <input
        v-model="editingVCard.max_debit"
        type="text"
        id="VCardDebit"
        class="form-control"
        :class="{ 'is-invalid': errors ? errors['max_debit'] : false }"
        required
      />
      <field-error-message :errors="errors" fieldName="max_debit"></field-error-message>
    </div>

    <hr />

    <div class="d-flex justify-content-between">
      <div class="mt-2" v-if="!inserting && userStore.userPhoneNumber == editingVCard.phone_number">
        <RouterLink
          :to="{ name: 'ConfirmationCode', params: { phone_number: editingVCard.phone_number } }"
          class="btn btn-success px-5 mx-2"
        >
          Change confirmation code
        </RouterLink>
      </div>

      <div class="mt-2">
        <button type="button" class="btn btn-primary px-5 mx-2" @click="save">Save</button>
        <button type="button" class="btn btn-light px-5 mx-2" @click="cancel">Cancel</button>
      </div>
    </div>

    <RouterLink
      v-if="!inserting && userStore.userPhoneNumber == editingVCard.phone_number"
      :to="{ name: 'DismissVCard' }"
      class="btn btn-danger px-5 mx-2 mt-2"
    >
      Dismiss VCard
    </RouterLink>
  </form>

  <!-- Input Photo -->
  <input
    type="file"
    style="visibility: hidden"
    id="inputPhoto"
    ref="inputPhotoFile"
    @change="changePhotoFile"
  />

  <div v-if="props.details" style="font-size: 15px">
    <h3 class="mt-5 mb-3">{{ vcardTitle }}</h3>
    <hr />

    <div class="d-flex flex-wrap justify-content-between">
      <div class="w-75 pe-4">
        <div class="mb-3">
          <p style="font-size: 20px"><b>Balance: </b>{{ editingVCard.balance }}€</p>
        </div>

        <div class="mb-3">
          <p><b>Phone Number: </b>{{ editingVCard.phone_number }}</p>
        </div>

        <div class="mb-3">
          <p><b>Name: </b>{{ editingVCard.name }}</p>
        </div>

        <div class="form-group">
          <p><b>Email: </b>{{ editingVCard.email }}</p>
        </div>
      </div>

      <div class="w-25">
        <div class="d-flex flex-column">
          <label class="form-label">Photo</label>
          <div class="form-control text-center">
            <img :src="photoFullUrl" class="w-50" />
          </div>
        </div>
      </div>
    </div>

    <hr />
  </div>
</template>

<style scoped>
.total_hours {
  width: 26rem;
}
</style>
