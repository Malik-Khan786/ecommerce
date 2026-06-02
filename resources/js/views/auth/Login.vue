<template>
    <div class="min-h-[70vh] flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-md p-8 w-full max-w-md">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Welcome back!</h1>
                <p class="text-gray-500 text-sm mt-1">Login to your Huzaifa Mobile account</p>
            </div>

            <form @submit.prevent="handleLogin" class="space-y-4">
                <div>
                    <label for="email" class="text-sm font-medium text-gray-700 block mb-1">Email</label>
                    <input id="email" v-model="form.email" type="email" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" placeholder="you@email.com" />
                </div>
                <div>
                    <label for="password" class="text-sm font-medium text-gray-700 block mb-1">Password</label>
                    <input id="password" v-model="form.password" type="password" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" placeholder="••••••••" />
                </div>

                <div v-if="error" class="bg-red-50 border border-red-200 rounded-xl p-3 text-red-600 text-sm">{{ error }}</div>

                <button type="submit" :disabled="loading" class="w-full bg-orange-500 hover:bg-orange-600 disabled:opacity-60 text-white py-3 rounded-xl font-semibold transition-colors">
                    {{ loading ? 'Logging in...' : 'Login' }}
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Don't have an account? <RouterLink to="/register" class="text-orange-500 font-medium hover:underline">Register</RouterLink>
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'

const auth   = useAuthStore()
const cart   = useCartStore()
const router = useRouter()
const route  = useRoute()

const form    = ref({ email: '', password: '' })
const loading = ref(false)
const error   = ref('')

async function handleLogin() {
    loading.value = true
    error.value   = ''
    try {
        await auth.login(form.value)
        await cart.fetchCart()
        const redirect = route.query.redirect || (auth.isAdmin ? '/admin/dashboard' : '/')
        router.push(redirect)
    } catch (e) {
        error.value = e.response?.data?.message || 'Login failed. Please check your credentials.'
    } finally {
        loading.value = false
    }
}
</script>
