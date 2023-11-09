//import './assets/main.css'
import "bootstrap/dist/css/bootstrap.min.css"
import "bootstrap-icons/font/bootstrap-icons.css"
import "bootstrap"

import { createApp } from "vue"
// import { createPinia } from 'pinia'

import App from "./App.vue"
import router from "./router"
import axios from "axios"

const app = createApp(App)

// app.use(createPinia())
app.use(router)

let serverBaseUrl
const userAgent = navigator.userAgent

if (/Win/i.test(userAgent)) {
    serverBaseUrl = "http://backend.test" // Windows
} else if (/Mac/i.test(userAgent)) {
    serverBaseUrl = "http://localhost" // Mac
} else {
    // Default value for other platforms
    serverBaseUrl = "http://backend.test"
}

app.provide("serverBaseUrl", serverBaseUrl)
// Default Axios configuration
axios.defaults.baseURL = serverBaseUrl + "/api"
axios.defaults.headers.common["Content-type"] = "application/json"

app.mount("#app")
