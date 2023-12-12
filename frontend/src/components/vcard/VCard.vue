<script setup>
import axios from 'axios'
import { useToast } from 'vue-toastification'
import { useUserStore } from '../../stores/user'
import { ref, watch, onMounted } from 'vue'
import VCardDetail from './VCardDetail.vue'
import { useRouter } from 'vue-router'

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
    name: '',
    email: '',
    photo_url: null,
    password: '',
    password_confirmation: '',
    confirmation_code: ''
  }
}

const vcard = ref(newVCard())
const errors = ref(null)

let originalValueStr = ''

const inserting = (phone_number) => !phone_number || phone_number < 0

const loadVCard = async (phone_number) => {
  originalValueStr
  errors.value = null
  if (inserting(phone_number)) {
    vcard.value = newVCard()
  } else {
    try {
      const response = await axios.get('vcards/' + phone_number)
      vcard.value = response.data.data
      console.log(response.data.data)
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
      toast.success('VCard #' + vcard.value.phone_number + ' was registered successfully.')
      if (userStore.login != true) {
        await userStore.login({
          username: vcard.value.phone_number.toString(),
          password: vcardToSave.password
        })
        router.push({ name: 'Home' })
      } else {
        router.back()
      }
    } catch (error) {
      console.log(error.response.data.errors)
      if (error.response.status == 422) {
        toast.error(error.response.data.message)
      } else {
        toast.error(error.response.data.message)
      }
    }
  } else {
    try {
      const response = await axios.put('vcards/' + props.phone_number, vcardToSave)
      vcard.value = response.data.data
      originalValueStr = JSON.stringify(vcard.value)
      toast.success('VCard #' + vcard.value.phone_number + ' was updated successfully.')
      if (vcard.value.phone_number == userStore.userId) {
        await userStore.loadUser()
      }
      router.back()
    } catch (error) {
      if (error.response.status == 422) {
        errors.value = error.response.data.errors
        // toast.error('VCard #' + props.phone_number + ' was not updated due to validation errors!')
        // console.log(error.response.data.errors['phone_number'][0])

        // show first error message only ( not only phone_number but all fields)
        for (const [key, value] of Object.entries(error.response.data.errors)) {
          toast.error(value[0])
          break
        }
      } else {
        toast.error(
          'VCard #' + props.phone_number + ' was not updated due to unknown server error!'
        )
      }
    }
  }
}

const cancel = () => {
  originalValueStr = JSON.stringify(vcard.value)
  router.back()
}

watch(
  () => props.phone_number,
  (newValue) => {
    loadVCard(newValue)
  },
  { immediate: true }
)

onMounted(() => {
  loadVCard(userStore.userPhoneNumber)
  console.log('123' + userStore.userPhoneNumber)
})

// let nextCallBack = null
// const leaveConfirmed = () => {
//     if (nextCallBack) {
//         nextCallBack()
//     }
// }

// onBeforeRouteLeave((to, from, next) => {
//     nextCallBack = null
//     let newValueStr = JSON.stringify(vcard.value)
//     if (originalValueStr != newValueStr) {
//         // Some value has changed - only leave after confirmation
//         nextCallBack = next
//         confirmationLeaveDialog.value.show()
//     } else {
//         // No value has changed, so we can leave the component without confirming
//         next()
//     }
// })
</script>

<template>
  <!-- <confirmation-dialog ref="confirmationLeaveDialog" confirmationBtn="Discard changes and leave"
        msg="Do you really want to leave? You have unsaved changes!" @confirmed="leaveConfirmed">
    </confirmation-dialog> -->
  <VCardDetail
    :vcard="vcard"
    :errors="errors"
    :inserting="inserting(phone_number)"
    @save="save"
    @cancel="cancel"
  >
  </VCardDetail>
</template>
