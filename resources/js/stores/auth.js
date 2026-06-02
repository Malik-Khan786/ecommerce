import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api'

export const useAuthStore = defineStore('auth', () => {
    const user  = ref(JSON.parse(localStorage.getItem('user') || 'null'))
    const token = ref(localStorage.getItem('token') || null)

    const isAuthenticated = computed(() => !!token.value)
    const isAdmin         = computed(() => user.value?.role === 'admin')

    async function login(credentials) {
        const { data } = await api.post('/login', credentials)
        token.value = data.token
        user.value  = data.user
        localStorage.setItem('token', data.token)
        localStorage.setItem('user', JSON.stringify(data.user))
    }

    async function register(payload) {
        const { data } = await api.post('/register', payload)
        token.value = data.token
        user.value  = data.user
        localStorage.setItem('token', data.token)
        localStorage.setItem('user', JSON.stringify(data.user))
    }

    async function logout() {
        try { await api.post('/logout') } catch {}
        token.value = null
        user.value  = null
        localStorage.removeItem('token')
        localStorage.removeItem('user')
    }

    async function fetchProfile() {
        const { data } = await api.get('/profile')
        user.value = data
        localStorage.setItem('user', JSON.stringify(data))
    }

    return { user, token, isAuthenticated, isAdmin, login, register, logout, fetchProfile }
})
