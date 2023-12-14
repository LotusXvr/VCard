//import './assets/main.css'
import "bootstrap/dist/css/bootstrap.min.css"
import "bootstrap-icons/font/bootstrap-icons.css"
import "bootstrap"
import Toast from "vue-toastification"
import "vue-toastification/dist/index.css"

import { createApp } from "vue"
import { createPinia } from "pinia"

import FieldErrorMessage from './components/global/FieldErrorMessage.vue'

import App from "./App.vue"
import router from "./router"
import axios from "axios"
import { io } from "socket.io-client"

const app = createApp(App)

const apiDomain = import.meta.env.VITE_API_DOMAIN
const wsConnection = import.meta.env.VITE_WS_CONNECTION

const userAgent = navigator.userAgent

// app.provide('serverBaseUrl', serverBaseUrl)
// app.provide('socket', io('http://localhost:8080'))
app.provide("apiDomain", apiDomain)
app.provide("serverUrl", `${apiDomain}/api`)
app.provide("socket", io(wsConnection))
axios.defaults.baseURL = apiDomain + "/api"
axios.defaults.headers.common["Content-type"] = "application/json"

app.use(createPinia())
app.use(router)
app.use(Toast, {
    position: "top-center",
    timeout: 3000,
    closeOnClick: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: true,
    hideProgressBar: true,
    closeButton: "button",
    icon: true,
    rtl: false,
})

app.component("FieldErrorMessage", FieldErrorMessage)
app.mount("#app")
