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
            name: "home",
            component: HomeView,
        },
        // {
        //   path: '/about',
        //   name: 'about',
        //   // route level code-splitting
        //   // this generates a separate chunk (About.[hash].js) for this route
        //   // which is lazy-loaded when the route is visited.
        //   component: () => import('../views/AboutView.vue')
        // }
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
            path: "/dashboard/:id",
            name: "Dashboard",
            component: Dashboard,
            props: (route) => ({ phone_number: parseInt(route.params.id) }),
        },
        {
            path: "/vcards",
            name: "VCards",
            component: VCards,
            props: { onlyCurrentVCards: false, vcardsTitle: "VCards" },
        },
        {
            path: "/transaction/:id",
            name: "Transaction",
            component: Transaction,
            props: (route) => ({ phone_number: parseInt(route.params.id) }),
        },
        {
            path: "/vcard/new",
            name: "NewVCard",
            component: VCard,
            props: { id: -1 },
        },
        {
            path: "/vcards/:id",
            name: "VCard",
            component: VCard,
            props: (route) => ({ phone_number: parseInt(route.params.id) }),
        },
        {
            path: "/vcard/:id/transactions",
            name: "Transactions",
            component: Transactions,
            props: (route) => ({ phone_number: parseInt(route.params.id) }),
        },
        {
            path: "/users",
            name: "Users",
            component: Users,
        },
        {
            path: "/users/:id",
            name: "User",
            component: User,
            //props: true
            // Replaced with the following line to ensure that id is a number
            props: (route) => ({ id: parseInt(route.params.id) }),
        },
    ],
})

export default router
