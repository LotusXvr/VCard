<script setup>
import { RouterLink, RouterView } from "vue-router"
import { ref, onMounted } from "vue"
import axios from "axios"
import { useToast } from "vue-toastification"
import { useUserStore } from "./stores/user"
import { useRouter } from "vue-router"

const userStore = useUserStore()
const toast = useToast()
const router = useRouter()

//color: #17f672 Verde Logo
//color: #0bbad6 Azul Logo
const phoneNumber = ref(900000015)

const logout = async () => {
    if (await userStore.logout()) {
        toast.success("User has logged out of the application.")
        router.push({ name: "Home" })
    } else {
        toast.error("There was a problem logging out of the application!")
    }
}

onMounted(() => {
    userStore.restoreToken()
})
</script>

<template>
    <nav class="navbar navbar-expand-md navbar-light bg-dark sticky-top flex-md-nowrap p-0 shadow">
        <div class="container-fluid">
            <router-link
                class="col-md-3 col-lg-2 me-0 d-flex align-items-center justify-content-center"
                :to="{ name: 'Home' }"
            >
                <img
                    src="@/assets/vcard.png"
                    alt=""
                    class="d-inline-block align-text-top"
                    style="max-height: 30px; max-width: 100px"
                />
            </router-link>
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

            <div class="collapse navbar-collapse justify-content-end" >
                <ul class="navbar-nav" >
                    <li class="nav-item" v-show="!userStore.user">
                        <a class="nav-link" href="#"
                            ><i class="bi bi-person-check-fill"></i>
                            Register
                        </a>
                    </li>
                    <li class="nav-item" v-show="!userStore.user">
                        <router-link
                            class="nav-link"
                            :class="{ active: $route.name === 'Login' }"
                            :to="{ name: 'Login' }"
                        >
                            <i class="bi bi-box-arrow-in-right"></i>
                            Login
                        </router-link>
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
                                :src="userStore.userPhotoUrl"
                                class="rounded-circle z-depth-0 avatar-img"
                                alt="avatar image"
                            />
                            <span class="avatar-text">{{ userStore.userName }}</span>
                        </a>
                        <ul
                            class="dropdown-menu dropdown-menu-dark dropdown-menu-end"
                            aria-labelledby="navbarDropdownMenuLink"
                            v-show="userStore.user"
                        >
                            <li>
                                <router-link
                                    class="dropdown-item"
                                    :class="{
                                        active: $route.name == 'User' && $route.params.id == 1,
                                    }"
                                    :to="{ name: 'User', params: { id: 1 } }"
                                >
                                    <i class="bi bi-person-square"></i>
                                    Profile
                                </router-link>
                            </li>
                            <li>
                                <router-link
                                    class="dropdown-item"
                                    :class="{ active: $route.name === 'ChangePassword' }"
                                    :to="{ name: 'ChangePassword' }"
                                >
                                    <i class="bi bi-key-fill"></i>
                                    Change password
                                </router-link>
                            </li>
                            <li>
                                <hr class="dropdown-divider" />
                            </li>
                            <li>
                                <a class="dropdown-item" @click="logout" href="#"
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
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse" >
                <div class="position-sticky pt-3" v-show="userStore.user">
                    <ul class="nav flex-column">
                        <li class="nav-item" >
                            <router-link
                                class="nav-link w-100 me-3"
                                :class="{ active: $route.name === 'Dashboard' }"
                                :to="{
                                    name: 'Dashboard',
                                }"
                            >
                                <i class="bi bi-house"></i>
                                Dashboard
                            </router-link>
                        </li>
                        <li class="nav-item d-flex justify-content-between align-items-center pe-3">
                            <router-link
                                class="nav-link w-100 me-3"
                                :class="{ active: $route.name === 'VCards' }"
                                :to="{ name: 'VCards' }"
                            >
                                <i class="bi bi-list-check"></i>
                                VCards
                            </router-link>
                        </li>
                        <li class="nav-item d-flex justify-content-between align-items-center pe-3">
                            <router-link
                                class="nav-link w-100 me-3"
                                :class="{ active: $route.name === 'Users' }"
                                :to="{ name: 'Users' }"
                            >
                                <i class="bi bi-list-check"></i>
                                Users
                            </router-link>
                            <router-link
                                class="link-secondary"
                                aria-label="Add a new task"
                                :to="{ name: 'NewUser' }"
                            >
                                <i class="bi bi-xs bi-plus-circle"></i>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link
                                class="nav-link w-100 me-3"
                                :class="{ active: $route.name === 'Transaction' }"
                                :to="{
                                    name: 'Transaction',
                                    params: { id: phoneNumber },
                                }"
                            >
                                <i class="bi bi-people"></i>
                                Enviar dinheiro
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="bi bi-files"></i>
                                Pagar serviço
                            </a>
                        </li>
                    </ul>

                    <h6
                        class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted"
                    >
                        <span style="color: #17f672">My VCard</span>
                        <a class="link-secondary" href="#" aria-label="Add a new project">
                            <i class="bi bi-xs bi-plus-circle"></i>
                        </a>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <router-link
                                class="nav-link w-100 me-3"
                                :class="{ active: $route.name === 'VCard' }"
                                :to="{ name: 'VCard'}"
                            >
                                <i class="bi bi-credit-card"></i>
                                Details
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link
                                class="nav-link w-100 me-3"
                                :class="{ active: $route.name === 'Transactions' }"
                                :to="{
                                    name: 'Transactions',
                                }"
                            >
                                <i class="bi bi-bank"></i>
                                Transactions
                            </router-link>
                        </li>
                    </ul>

                    <div class="collapse navbar-collapse justify-content-end">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link" href="#"
                                    ><i class="bi bi-person-check-fill"></i>
                                    Register
                                </a>
                            </li>
                            <li class="nav-item">
                                <router-link
                                    class="nav-link"
                                    :class="{ active: $route.name === 'Login' }"
                                    :to="{ name: 'Login' }"
                                >
                                    <i class="bi bi-box-arrow-in-right"></i>
                                    Login
                                </router-link>
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
                                        :src="userStore.userPhotoUrl"
                                        class="rounded-circle z-depth-0 avatar-img"
                                        alt="avatar image"
                                    />
                                    <span class="avatar-text">{{ userStore.userName }}</span>
                                </a>
                                <ul
                                    class="dropdown-menu dropdown-menu-dark dropdown-menu-end"
                                    aria-labelledby="navbarDropdownMenuLink"
                                >
                                    <li>
                                        <router-link
                                            class="dropdown-item"
                                            :class="{
                                                active:
                                                    $route.name == 'User' && $route.params.id == 1,
                                            }"
                                            :to="{ name: 'User', params: { id: 1 } }"
                                        >
                                            <i class="bi bi-person-square"></i>
                                            Profile
                                        </router-link>
                                    </li>
                                    <li>
                                        <router-link
                                            class="dropdown-item"
                                            :class="{ active: $route.name === 'ChangePassword' }"
                                            :to="{ name: 'ChangePassword' }"
                                        >
                                            <i class="bi bi-key-fill"></i>
                                            Change password
                                        </router-link>
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

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <router-view />
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

.collapse.navbar-collapse.justify-content-end a {
    color: #0bbad6;
}
#sidebarMenu.collapse i {
    color: #17f672;
}
#sidebarMenu.collapse li.nav-item a {
    color: #0bbad6;
}
</style>
