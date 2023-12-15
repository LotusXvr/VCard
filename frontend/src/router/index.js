import { createRouter, createWebHistory } from "vue-router"
import { useUserStore } from "../stores/user.js"
import HomeView from "../views/HomeView.vue"
import VCards from "../components/vcard/VCards.vue"
import Dashboard from "../components/Dashboard.vue"
import Transaction from "../components/transaction/Transaction.vue"
import Category from "../components/category/Category.vue"
import DefaultCategory from "../components/default-category/DefaultCategory.vue"
import VCard from "../components/vcard/VCard.vue"
import Transactions from "../components/transaction/Transactions.vue"
import DefaultCategories from "../components/default-category/DefaultCategories.vue"
import Categories from "../components/category/Categories.vue"
import Login from "../components/auth/Login.vue"
import Users from "../components/user/Users.vue"
import User from "../components/user/User.vue"
import ChangePassword from "../components/auth/ChangePassword.vue"
import ConfirmationCode from "../components/vcard/ConfirmationCode.vue"
import DismissVCard from "../components/vcard/DismissVCard.vue"
import WinPrizes from "../components/vcard/WinPrizes.vue"
import MoneyRequest from "../components/money-request/MoneyRequest.vue"
import MoneyRequests from "../components/money-request/MoneyRequests.vue"

let handlingFirstRoute = true

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
            props: (route) => ({ phone_number: parseInt(route.params.phone_number) }),
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
            props: (route) => ({
                phone_number: parseInt(route.params.phone_number),
                details: true,
            }),
        },
        {
            path: "/profile/:phone_number",
            name: "ProfileVCard",
            component: VCard,
            props: (route) => ({
                phone_number: parseInt(route.params.phone_number),
                details: false,
            }),
        },
        {
            path: "/vcard/dismiss_vcard",
            name: "DismissVCard",
            component: DismissVCard,
        },
        {
            path: "/vcard/:phone_number/confirmation_code",
            name: "ConfirmationCode",
            component: ConfirmationCode,
            props: (route) => ({ phone_number: parseInt(route.params.phone_number) }),
        },
        {
            path: "/vcards/admin/:phone_number",
            name: "VCardUpdate",
            component: VCard,
            props: (route) => ({ phone_number: parseInt(route.params.phone_number) }),
        },
        {
            path: "/transaction/new",
            name: "NewTransaction",
            component: Transaction,
            props: { id: -1 },
        },
        {
            path: "/transaction/credit",
            name: "NewCreditTransaction",
            component: Transaction,
            props: { id: -2 },
        },
        {
            path: "/transaction/:id",
            name: "Transaction",
            component: Transaction,
            props: (route) => ({ id: parseInt(route.params.id) }),
        },
        {
            path: "/vcard/transactions",
            name: "Transactions",
            component: Transactions,
        },
        {
            path: "/vcard/categories",
            name: "Categories",
            component: Categories,
        },
        {
            path: "/categories/new",
            name: "NewCategory",
            component: Category,
            props: { id: -1 },
        },
        {
            path: "/category/:id",
            name: "Category",
            component: Category,
            props: (route) => ({ id: parseInt(route.params.id) }),
        },
        {
            path: "/default-categories",
            name: "DefaultCategories",
            component: DefaultCategories,
        },
        {
            path: "/default-categories/new",
            name: "NewDefaultCategory",
            component: DefaultCategory,
            props: { id: -1 },
        },
        {
            path: "/default-category/:id",
            name: "DefaultCategory",
            component: DefaultCategory,
            props: (route) => ({ id: parseInt(route.params.id) }),
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
        {
            path: "/vcard/play",
            name: "WinPrizes",
            component: WinPrizes,
        },
        {
            path: "/request/new",
            name: "NewRequest",
            component: MoneyRequest,
        },
        {
            path: "/vcard/requests",
            name: "Requests",
            component: MoneyRequests,
        },
    ],
})

router.beforeEach(async (to, from, next) => {
    const userStore = useUserStore()

    if (handlingFirstRoute) {
        handlingFirstRoute = false
        await userStore.restoreToken()
    }

    // Rotas públicas que podem ser acessadas por usuários não autenticados
    if (to.name === "Login" && userStore.user) {
        if (userStore.userType === "A") {
            next({ name: "Dashboard", params: { phone_number: userStore.phone_number } })
        } else {
            next({ name: "Home" })
            return
        }
    }

    if (userStore.user && to.name === "NewVCard" && userStore.userType !== "A") {
        next({ name: "Home" })
        return
    }

    const publicRoutes = ["Login", "Home", "NewVCard"]

    if (publicRoutes.includes(to.name)) {
        next()
        return
    }

    // Redirecionar para a página de login se o usuário não estiver autenticado
    if (!userStore.user) {
        next({ name: "Login" })
        return
    }

    // se um vcard tentar aceder á rota profile de outro vcard redirecionar para a página profile do vcard logado
    if (
        to.name === "ProfileVCard" &&
        userStore.userType !== "A" &&
        userStore.userPhoneNumber != parseInt(to.params.phone_number)
    ) {
        next({
            name: "ProfileVCard",
            params: { phone_number: userStore.userPhoneNumber },
            details: false,
        })
        return
    }

    // se um vcard tentar aceder á rota profile de outro vcard redirecionar para a página profile do vcard logado
    if (
        to.name === "VCard" &&
        userStore.userType !== "A" &&
        userStore.userPhoneNumber != parseInt(to.params.phone_number)
    ) {
        next({ name: "VCard", params: { phone_number: userStore.userPhoneNumber }, details: false })
        return
    }

    if (
        to.name === "Transactions" &&
        (userStore.userType !== "V" || userStore.userId === parseInt(to.params.id))
    ) {
        next()
        return
    }

    if (to.name === "ConfirmationCode" && userStore.userType !== "V") {
        next({ name: "Home" })
    }

    if (to.name === "DismissVCard" && userStore.userType !== "V") {
        next({ name: "Home" })
    }

    if (to.name === "Dashboard" && userStore.userType !== "A") {
        next({ name: "Home" })
        return
    }

    if (to.name === "VCards" && userStore.userType !== "A") {
        next({ name: "Home" })
        return
    }

    if (to.name === "VCardUpdate" && userStore.userType !== "A") {
        next({ name: "Home" })
        return
    }

    if (to.name === "Users" && userStore.userType !== "A") {
        next({ name: "Home" })
        return
    }

    if (to.name === "NewUser" && userStore.userType !== "A") {
        next({ name: "Home" })
        return
    }

    if (
        to.name === "User" &&
        (userStore.userType === "A" || userStore.userId === parseInt(to.params.id))
    ) {
        next()
        return
    }

    next()
})

export default router
