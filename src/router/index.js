import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView
  },
  {
    path: '/about',
    name: 'about',

    component: () => import(/* webpackChunkName: "about" */ '../views/AboutView.vue')
  },
  {
    path: '/customer',
    name: 'customer',

    component: () => import(/* webpackChunkName: "about" */ '../views/Customer.vue')
  },
  {
    path: '/employee',
    name: 'employee',

    component: () => import(/* webpackChunkName: "about" */ '../views/Employee.vue')
  },
  {
    path: '/add_customer',
    name: 'add_customer',

    component: () => import(/* webpackChunkName: "about" */ '../views/Add_Customer.vue')
  },
  {
    path: '/add_employee',
    name: 'add_employee',

    component: () => import(/* webpackChunkName: "about" */ '../views/Add_Employee.vue')
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

export default router
