import axios from "axios"
import { ref, computed, inject } from "vue"
import { defineStore } from "pinia"
import avatarNoneUrl from "@/assets/avatar-none.png"
import { useToast } from "vue-toastification"
export const useUserStore = defineStore("user", () => {
    const serverBaseUrl = inject("apiDomain")

    const user = ref(null)
    const toast = useToast()
    const userName = computed(() => user.value?.name ?? "Anonymous")
    const userType = computed(() => user.value?.user_type ?? "Anonymous")
    const userPhoneNumber = computed(() => user.value?.username ?? 0)
    const userPhotoUrl = computed(() =>
        user.value?.photo_url
            ? serverBaseUrl + "/storage/fotos/" + user.value.photo_url
            : avatarNoneUrl,
    )

    async function loadUser() {
        try {
            const response = await axios.get("users/me")
            user.value = response.data.data
        } catch (error) {
            clearUser()
            throw error
        }
    }

    function clearUser() {
        delete axios.defaults.headers.common.Authorization
        sessionStorage.removeItem("token")
        user.value = null
    }

    async function login(credentials) {
        try {
            const response = await axios.post("login", credentials)
            axios.defaults.headers.common.Authorization = "Bearer " + response.data.access_token

            sessionStorage.setItem("token", response.data.access_token)

            await loadUser()
            return true
        } catch (error) {

            clearUser()
            toast.error("Login failed - " + error.response.data.message ?? "Unknown error")
            return false
        }
    }
    async function logout() {
        try {
            await axios.post("logout")
            clearUser()
            return true
        } catch (error) {
            return false
        }
    }
    async function restoreToken() {
        let storedToken = sessionStorage.getItem("token")
        if (storedToken) {
            axios.defaults.headers.common.Authorization = "Bearer " + storedToken
            await loadUser()
            return true
        }
        clearUser()
        return false
    }
    return {
        user,
        userName,
        userType,
        userPhotoUrl,
        userPhoneNumber,
        loadUser,
        clearUser,
        login,
        logout,
        restoreToken,
    }
})
