import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import VCards from '../components/vcard/VCards.vue'
import Dashboard from "../components/Dashboard.vue"
import VCardCreate from "../components/vcard/VCardCreate.vue"

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
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
        path: "/vcards/create",
        name: "VCardCreate",
        component: VCardCreate,
    },
  ]
})

export default router
