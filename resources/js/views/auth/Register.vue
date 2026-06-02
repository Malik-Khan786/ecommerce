<template>
    <div class="min-h-[70vh] flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-md p-8 w-full max-w-md">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Create Account</h1>
                <p class="text-gray-500 text-sm mt-1">Join Huzaifa Mobile for the best deals</p>
            </div>

            <form @submit.prevent="handleRegister" class="space-y-4">
                <div>
                    <label for="name" class="text-sm font-medium text-gray-700 block mb-1">Full Name</label>
                    <input id="name" v-model="form.name" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" placeholder="Muhammad Ali" />
                </div>
                <div>
                    <label for="email" class="text-sm font-medium text-gray-700 block mb-1">Email</label>
                    <input id="email" v-model="form.email" type="email" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" placeholder="you@email.com" />
                </div>
                <div>
                    <label for="phone" class="text-sm font-medium text-gray-700 block mb-1">Phone</label>
                    <input id="phone" v-model="form.phone" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" placeholder="03001234567" />
                </div>
                <div>
                    <label for="password" class="text-sm font-medium text-gray-700 block mb-1">Password</label>
                    <input id="password" v-model="form.password" type="password" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" placeholder="Min 8 characters" />
                </div>
                <div>
                    <label for="password_confirmation" class="text-sm font-medium text-gray-700 block mb-1">Confirm Password</label>
                    <input id="password_confirmation" v-model="form.password_confirmation" type="password" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" />
                </div>

                <div v-if="errors.length" class="bg-red-50 border border-red-200 rounded-xl p-3">
                    <p v-for="err in errors" :key="err" class="text-red-600 text-xs">{{ err }}</p>
                </div>

                <button type="submit" :disabled="loading" class="w-full bg-orange-500 hover:bg-orange-600 disabled:opacity-60 text-white py-3 rounded-xl font-semibold transition-colors">
                    {{ loading ? 'Creating Account...' : 'Create Account' }}
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Already have an account? <RouterLink to="/login" class="text-orange-500 font-medium hover:underline">Login</RouterLink>
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'vue-toastification'

const auth   = useAuthStore()
const router = useRouter()
const toast  = useToast()

const form    = ref({ name: '', email: '', phone: '', password: '', password_confirmation: '' })
const loading = ref(false)
const errors  = ref([])

async function handleRegister() {
    loading.value = true
    errors.value  = []
    try {
        await auth.register(form.value)
        toast.success('Account created successfully!')
        router.push('/')
    } catch (e) {
        if (e.response?.data?.errors) {
            errors.value = Object.values(e.response.data.errors).flat()
        } else {
            errors.value = [e.response?.data?.message || 'Registration failed.']
        }
    } finally {
        loading.value = false
    }
}
</script>
