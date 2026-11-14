import { createRouter, createWebHistory } from 'vue-router'
import LoginPage from './views/LoginPage.vue'
import PosPage from './views/PosPage.vue'
import api from './api'

const routes = [
  { path: '/', component: LoginPage },
  { path: '/pos', component: PosPage }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async (to) => {
  if (to.path !== '/pos') return true
  try {
    await api.get('/me')
    return true
  } catch {
    return '/'
  }
})

export default router
