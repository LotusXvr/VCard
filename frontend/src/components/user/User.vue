<script setup>
import axios from "axios";
import { useToast } from "vue-toastification";
import { useUserStore } from "../../stores/user.js";
import { ref, computed, watch } from "vue";
import UserDetail from "./UserDetail.vue"; // Assuming you have a UserDetail component
import { useRouter } from "vue-router";

const router = useRouter();
const toast = useToast();
const userStore = useUserStore();

const props = defineProps({
    id: {
      type: Number,
      default: null
    }
})

const newUser = () => {
    return {
      id: null,
      name: '',
      email: '',
      password: '',
      password_confirmation: ''
    }
}

const user = ref(newUser());
const errors = ref(null);
let originalValueStr = ''

const inserting = (id) => !id || (id < 0)
const loadUser = async (id) => {
  originalValueStr = ''
  errors.value = null
  if (inserting(id)) {
    user.value = newUser()
  } else {
    try {
      const response = await axios.get('admins/' + id);
      user.value = response.data.data;
      originalValueStr = JSON.stringify(user.value);
    } catch (error) {
      console.error('Error loading user:', error);
    }
  }
}

const save = async (userToSave) => {
  errors.value = null
  if (inserting(props.id)) {
    try {
      const response = await axios.post('admins', userToSave)
      user.value = response.data.data
      originalValueStr = JSON.stringify(user.value)
      toast.success('User #' + user.value.name + ' was registered successfully.')
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
      const response = await axios.put('admins/' + props.id, userToSave)
      user.value = response.data.data
      originalValueStr = JSON.stringify(user.value)
      toast.success('User #' + user.value.id + ' was updated successfully.')
      if (user.value.id == userStore.userId) {
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
  () => props.id,
  (newValue) => {
      loadUser(newValue)
    },
  {immediate: true}
)
</script>

<template>
  <UserDetail
    :user="user"
    :errors="errors"
    :inserting="inserting(id)"
    @save="save"
    @cancel="cancel"
  ></UserDetail>
</template>
