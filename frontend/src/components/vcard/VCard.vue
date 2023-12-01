<script setup>
import axios from "axios"
import { useToast } from "vue-toastification"
import { useUserStore } from "../../stores/user"
import { ref, watch, onMounted } from "vue"
import VCardDetail from "./VCardDetail.vue"
import { useRouter } from "vue-router"

const toast = useToast()
const router = useRouter()
const userStore = useUserStore()

const props = defineProps({
  phone_number: {
    type: Number,
    default: null
  }
})

const newVCard = () => {
  return {
    phone_number: null,
    name: "",
    email: "",
    photo_url: null,
    password: "",
    password_confirmation: "",
    confirmation_code: "",
  }
}

const vcard = ref(newVCard())
const errors = ref(null)

let originalValueStr = ''

const inserting = (phone_number) => !phone_number || (phone_number < 0)
const loadVCard = async (phone_number) => {
  originalValueStr = ''
  errors.value = null
  if (inserting(phone_number)) {
    vcard.value = newVCard()
  } else {
    try {
      const response = await axios.get('vcards/' + phone_number)
      vcard.value = response.data.data
      originalValueStr = JSON.stringify(vcard.value)
    } catch (error) {
      console.log(error)
    }
  }
}

const save = async (vcardToSave) => {
  errors.value = null
  if (inserting(props.phone_number)) {
    try {
      const response = await axios.post('vcard', vcardToSave)
      vcard.value = response.data.data
      originalValueStr = JSON.stringify(vcard.value)
      toast.success('User #' + vcard.value.phone_number + ' was registered successfully.')
      // await userStore.login({
      //   username: vcard.value.phone_number,
      //   password: vcardToSave.password
      // })
      router.back()
    } catch (error) {
      if (error.response.status == 422) {
        errors.value = error.response.data.errors
        toast.error('User was not registered due to validation errors!')
      } else {
        toast.error('User was not registered due to unknown server error!')
      }
    }
  } else {
    try {
      const response = await axios.put('vcards/' + props.phone_number, vcardToSave)
      vcard.value = response.data.data
      originalValueStr = JSON.stringify(vcard.value)
      toast.success('User #' + vcard.value.phone_number + ' was updated successfully.')
      if (user.value.phone_number == userStore.userId) {
        await userStore.loadUser()
      }
      router.back()
    } catch (error) {
      if (error.response.status == 422) {
        errors.value = error.response.data.errors
        toast.error('User #' + props.id + ' was not updated due to validation errors!')
      } else {
        toast.error('User #' + props.id + ' was not updated due to unknown server error!')
      }
    }
  }
}

const cancel = () => {
  originalValueStr = JSON.stringify(user.value)
  router.back()
}

watch(
  () => props.phone_number,
  (newValue) => {
    loadVCard(newValue)
  },
  { immediate: true },
)

onMounted(() => {
  loadVCard(userStore.userPhoneNumber)
  console.log("123" + userStore.userPhoneNumber)
})
</script>

<template>
  <VCardDetail :vcard="vcard" :errors="errors" :inserting="inserting(phone_number)" @save="save" @cancel="cancel">
  </VCardDetail>
</template>
