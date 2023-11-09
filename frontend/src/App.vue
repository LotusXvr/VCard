<script setup>
import { ref, onMounted } from "vue"
import axios from "axios"

import createVCard from "./components/vcard/VCardCreate.vue"
import createUser from "./components/user/UserCreate.vue"

import VCardList from "./components/vcard/VCardList.vue"

const vcards = ref([])
const users = ref([])
const showingUsers = ref(false)
const showingVCards = ref(false)
const error = ref(null)
const success = ref(null)

const showUsers = () => {
    // Set the flag to show users and hide vCards
    showingUsers.value = true
    showingVCards.value = false
}
const showVCards = () => {
    // Set the flag to show vCards and hide users
    showingUsers.value = false
    showingVCards.value = true
}

const fetchVCards = async () => {
    // Fetch the vCards from the API
    const response = await axios.get("vcards")
    vcards.value = response.data
}

const fetchUsers = async () => {
    // Fetch the users from the API
    const response = await axios.get("users")
    users.value = response.data
}

const addVCard = async (newVCard) => {
    if (newVCard) {
        try {
            await axios.post(`$vcards`, newVCard)
            fetchVCards()
            success.value = "VCard created successfully" // show success error
            error.value = null
        } catch (e) {
            success.value = null // clear success message
            error.value = e.response.data.errors // Capture and display API validation errors
        }
    }
}

const addUser = async (newUser) => {
    if (newUser) {
        try {
            await axios.post(`users`, newUser)
            fetchUsers()
            success.value = "User created successfully" // show success error
            error.value = null
        } catch (e) {
            success.value = null // clear success message
            error.value = e.response.data.errors // Capture and display API validation errors
        }
    }
}

const deleteVCard = async (vcard) => {
    if (vcard) {
        try {
            await axios.delete(`vcards/${vcard.phone_number}`)
            fetchVCards()
            success.value = "VCard deleted successfully" // show success error
            error.value = null
        } catch (e) {
            success.value = null // clear success message
            error.value = e.response.data.errors // Capture and display API validation errors
        }
    }
}

onMounted(() => {
    // Fetch the vCards and users when the component is mounted
    fetchVCards()
    fetchUsers()
})
</script>

<!-- <template>
    <div>
        

        <createVCard @AddVCard="addVCard"></createVCard>
        <createUser @AddUser="addUser"></createUser>

    
    </div>
</template> -->

<template>
    <nav class="navbar navbar-expand-md navbar-dark bg-dark sticky-top flex-md-nowrap p-0 shadow">
        <div class="container-fluid">
            <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="#">
                <img
                    src="@/assets/logo.svg"
                    alt=""
                    width="30"
                    height="24"
                    class="d-inline-block align-text-top"
                />
                VCard
            </a>
            <button
                id="buttonSidebarExpandId"
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#sidebarMenu"
                aria-controls="sidebarMenu"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

            <div v-if="error">
                <div class="alert alert-danger">
                    <ul>
                        <li v-for="(message, field) in error" :key="field">
                            {{ field }}: {{ message[0] }}
                        </li>
                    </ul>
                </div>
            </div>

            <div v-if="success">
                <div class="alert alert-success">
                    <ul>
                        <li>{{ success }}</li>
                    </ul>
                </div>
            </div>

            <div class="collapse navbar-collapse justify-content-end">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="#"
                            ><i class="bi bi-person-check-fill"></i>
                            Register
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"
                            ><i class="bi bi-box-arrow-in-right"></i>
                            Login
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a
                            class="nav-link dropdown-toggle"
                            href="#"
                            id="navbarDropdownMenuLink"
                            role="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            <img
                                src="@/assets/avatar-exemplo-1.jpg"
                                class="rounded-circle z-depth-0 avatar-img"
                                alt="avatar image"
                            />
                            <span class="avatar-text">User Name</span>
                        </a>
                        <ul
                            class="dropdown-menu dropdown-menu-dark dropdown-menu-end"
                            aria-labelledby="navbarDropdownMenuLink"
                        >
                            <li>
                                <a class="dropdown-item" href="#"
                                    ><i class="bi bi-person-square"></i>Profile</a
                                >
                            </li>
                            <li>
                                <a class="dropdown-item" href="#"
                                    ><i class="bi bi-key-fill"></i>Change password</a
                                >
                            </li>
                            <li>
                                <hr class="dropdown-divider" />
                            </li>
                            <li>
                                <a class="dropdown-item" href="#"
                                    ><i class="bi bi-arrow-right"></i>Logout</a
                                >
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="#">
                                <i class="bi bi-house"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a @click="showVCards" class="nav-link" href="#">
                                <i class="bi bi-list-stars"></i>
                                VCards
                            </a>
                        </li>
                        <li class="nav-item d-flex justify-content-between align-items-center pe-3">
                            <a @click="showUsers" class="nav-link w-100 me-3" href="#">
                                <i class="bi bi-list-check"></i>
                                Users
                            </a>
                            <a class="link-secondary" href="#" aria-label="Add a new task">
                                <i class="bi bi-xs bi-plus-circle"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="bi bi-files"></i>
                                Transações
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="bi bi-people"></i>
                                Enviar dinheiro
                            </a>
                        </li>
                    </ul>

                    <h6
                        class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted"
                    >
                        <span>Histórico</span>
                        <a class="link-secondary" href="#" aria-label="Add a new project">
                            <i class="bi bi-xs bi-plus-circle"></i>
                        </a>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="bi bi-file-ruled"></i>
                                Meu cartão
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#"
                                ><i class="bi bi-file-ruled"></i>
                                Minhas transações
                            </a>
                        </li>
                    </ul>

                    <div class="d-block d-md-none">
                        <h6
                            class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted"
                        >
                            <span>User</span>
                        </h6>
                        <ul class="nav flex-column mb-2">
                            <li class="nav-item">
                                <a class="nav-link" href="#"
                                    ><i class="bi bi-person-check-fill"></i>
                                    Register
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <i class="bi bi-box-arrow-in-right"></i>
                                    Login
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a
                                    class="nav-link dropdown-toggle"
                                    href="#"
                                    id="navbarDropdownMenuLink2"
                                    role="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                >
                                    <img
                                        src="@/assets/avatar-exemplo-1.jpg"
                                        class="rounded-circle z-depth-0 avatar-img"
                                        alt="avatar image"
                                    />
                                    <span class="avatar-text">User Name</span>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink2">
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <i class="bi bi-person-square"></i>Profile</a
                                        >
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <i class="bi bi-key-fill"></i>
                                            Change password
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider" />
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <i class="bi bi-arrow-right"></i>Logout
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <dashboard>
                    <!-- USERS -> CODIGO SEM ESTRUTURAÇÃO DE COMPONENTES -->
                    <div v-if="showingUsers">
                        <h2>Users</h2>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(user, index) in users" :key="index">
                                    <td>{{ user.name }}</td>
                                    <td>{{ user.email }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- VCARDS -> CODIGO !!COM!! ESTRUTURAÇÃO DE COMPONENTES -->
                    <div v-if="showingVCards">
                        <VCardList
                            :vcards="vcards"
                            :readonly="false"
                            @requestRemoveVCardFromList="deleteVCard"
                            @requestUpdateVCard="updateVCard"
                        >
                        </VCardList>
                    </div>
                </dashboard>
            </main>
        </div>
    </div>
</template>

<style>
@import "./assets/dashboard.css";

.avatar-img {
    margin: -1.2rem 0.8rem -2rem 0.8rem;
    width: 3.3rem;
    height: 3.3rem;
}

.avatar-text {
    line-height: 2.2rem;
    margin: 1rem 0.5rem -2rem 0;
    padding-top: 1rem;
}

.dropdown-item {
    font-size: 0.875rem;
}

.btn:focus {
    outline: none;
    box-shadow: none;
}

#sidebarMenu {
    overflow-y: auto;
}
</style>
