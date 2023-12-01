import { createRouter, createWebHistory } from "vue-router"
import HomeView from "../views/HomeView.vue"
import VCards from "../components/vcard/VCards.vue"
import Dashboard from "../components/Dashboard.vue"
import Transaction from "../components/transaction/Transaction.vue"
import VCard from "../components/vcard/VCard.vue"
import Transactions from "../components/transaction/Transactions.vue"
import Login from "../components/auth/Login.vue"
import Users from "../components/user/Users.vue"
import User from "../components/user/User.vue"
import ChangePassword from "../components/auth/ChangePassword.vue"

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: "/",
            name: "Home",
            component: HomeView,
        },
        {
            path: "/login",
            name: "Login",
            component: Login,
        },
        {
            path: "/password",
            name: "ChangePassword",
            component: ChangePassword,
        },
        {
            path: "/dashboard",
            name: "Dashboard",
            component: Dashboard,
        },
        {
            path: "/vcards",
            name: "VCards",
            component: VCards,
            props: { onlyCurrentVCards: false, vcardsTitle: "VCards" },
        },
        {
            path: "/vcard/new",
            name: "NewVCard",
            component: VCard,
            props: { id: -1 },
        },
        {
            path: "/vcard/:phone_number",
            name: "VCard",
            component: VCard,
            props: (route) => ({ phone_number: parseInt(route.params.phone_number) }),
        },
        {
            path: "/vcards/:phone_number",
            name: "VCardUpdate",
            component: VCard,
            props: (route) => ({ phone_number: parseInt(route.params.phone_number) }),
        },
        {
            path: "/transaction/:id",
            name: "Transaction",
            component: Transaction,
            props: (route) => ({ phone_number: parseInt(route.params.id) }),
        },
        {
            path: "/vcard/transactions",
            name: "Transactions",
            component: Transactions,
        },
        {
            path: "/admins", // Altere de '/users' para '/administrators'
            name: "Users",
            component: Users,
        },
        {
            path: "/admins/:id", // Altere de '/users/:id' para '/administrators/:id'
            name: "User",
            component: User,
            props: (route) => ({ id: parseInt(route.params.id) }),
        },
        {
            path: "/admins/new", // Altere de '/users/new' para '/administrators/new'
            name: "NewUser",
            component: User,
            props: { id: -1 },
        },
    ],
})

export default router
