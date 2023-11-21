<script setup>
import { RouterLink, RouterView } from "vue-router"
import { ref } from "vue"
//color: #17f672 Verde Logo
//color: #0bbad6 Azul Logo
const staticPhoneNumber = ref(900000011)
</script>

<template>
    <nav class="navbar navbar-expand-md navbar-light bg-dark sticky-top flex-md-nowrap p-0 shadow">
        <div class="container-fluid">
            <a class="col-md-3 col-lg-2 me-0 d-flex align-items-center justify-content-center" href="#">
                <img
                    src="@/assets/vcard.png"
                    alt=""
                    class="d-inline-block align-text-top"
                    style="max-height: 30px; max-width: 100px"
                />
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
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <router-link
                                class="nav-link w-100 me-3"
                                :class="{ active: $route.name === 'Dashboard' }"
                                :to="{ name: 'Dashboard', params: { id: staticPhoneNumber } }"
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
                            <a @click="showUsers" class="nav-link w-100 me-3" href="#">
                                <i class="bi bi-list-check"></i>
                                Users
                            </a>
                            <a class="link-secondary" href="#" aria-label="Add a new task">
                                <i class="bi bi-xs bi-plus-circle"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <router-link
                                class="nav-link w-100 me-3"
                                :class="{ active: $route.name === 'Transaction' }"
                                :to="{ name: 'Transaction', params: { id: staticPhoneNumber } }"
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
                        <span style="color: #17f672;">My VCard</span>
                        <a class="link-secondary" href="#" aria-label="Add a new project">
                            <i class="bi bi-xs bi-plus-circle"></i>
                        </a>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <router-link
                                class="nav-link w-100 me-3"
                                :class="{ active: $route.name === 'VCard' }"
                                :to="{ name: 'VCard', params: { id: staticPhoneNumber } }"
                            >
                                <i class="bi bi-credit-card"></i>
                                Details
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link
                                class="nav-link w-100 me-3"
                                :class="{ active: $route.name === 'Transactions' }"
                                :to="{ name: 'Transactions', params: { id: staticPhoneNumber } }"
                            >
                                <i class="bi bi-bank"></i>
                                Transactions
                            </router-link>
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
                                    class="nav-link dropdown-toggle "
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
#sidebarMenu.collapse li.nav-item a{
    color: #0bbad6;
}
</style>
