<template>
    <div class="max-w-xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">My Profile</h1>

        <!-- Shopping Summary -->
        <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
            <h2 class="font-semibold mb-4">Shopping Summary</h2>
            <div v-if="summaryLoading" class="text-sm text-gray-400">Loading summary...</div>
            <div v-else class="grid grid-cols-2 gap-4">
                <div class="bg-orange-50 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-orange-500">{{ summary.totalOrders }}</p>
                    <p class="text-xs text-gray-500 mt-1">Total Orders</p>
                </div>
                <div class="bg-green-50 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-green-600">Rs. {{ formatPrice(summary.totalSpent) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Total Spent</p>
                </div>
                <div class="bg-blue-50 rounded-xl p-4 text-center">
                    <p class="text-sm font-bold text-blue-600 truncate">{{ summary.topCategory || '—' }}</p>
                    <p class="text-xs text-gray-500 mt-1">Top Category</p>
                </div>
                <div class="bg-purple-50 rounded-xl p-4 text-center">
                    <p class="text-sm font-bold text-purple-600">{{ summary.memberSince }}</p>
                    <p class="text-xs text-gray-500 mt-1">Member Since</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
            <h2 class="font-semibold mb-4">Personal Information</h2>
            <form @submit.prevent="updateProfile" class="space-y-4">
                <div>
                    <label for="profile-name" class="text-sm text-gray-600 block mb-1">Full Name</label>
                    <input id="profile-name" v-model="form.name" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" />
                </div>
                <div>
                    <label for="profile-email" class="text-sm text-gray-600 block mb-1">Email</label>
                    <input id="profile-email" :value="auth.user?.email" disabled class="w-full border border-gray-200 bg-gray-50 rounded-xl px-4 py-2.5 text-sm text-gray-500" />
                </div>
                <div>
                    <label for="profile-phone" class="text-sm text-gray-600 block mb-1">Phone</label>
                    <input id="profile-phone" v-model="form.phone" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" />
                </div>
                <div v-if="profileMsg" :class="['text-sm px-3 py-2 rounded-lg', profileSuccess ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600']">{{ profileMsg }}</div>
                <button type="submit" :disabled="saving" class="bg-orange-500 hover:bg-orange-600 disabled:opacity-60 text-white px-6 py-2.5 rounded-xl text-sm font-medium">
                    {{ saving ? 'Saving...' : 'Save Changes' }}
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
            <h2 class="font-semibold mb-4">Change Password</h2>
            <form @submit.prevent="changePassword" class="space-y-4">
                <div>
                    <label for="pw-current" class="text-sm text-gray-600 block mb-1">Current Password</label>
                    <input id="pw-current" v-model="pwForm.current_password" type="password" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" />
                </div>
                <div>
                    <label for="pw-new" class="text-sm text-gray-600 block mb-1">New Password</label>
                    <input id="pw-new" v-model="pwForm.password" type="password" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" />
                </div>
                <div>
                    <label for="pw-confirm" class="text-sm text-gray-600 block mb-1">Confirm New Password</label>
                    <input id="pw-confirm" v-model="pwForm.password_confirmation" type="password" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" />
                </div>
                <div v-if="pwMsg" :class="['text-sm px-3 py-2 rounded-lg', pwSuccess ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600']">{{ pwMsg }}</div>
                <button type="submit" :disabled="changingPw" class="bg-gray-800 hover:bg-gray-700 disabled:opacity-60 text-white px-6 py-2.5 rounded-xl text-sm font-medium">
                    {{ changingPw ? 'Updating...' : 'Update Password' }}
                </button>
            </form>
        </div>

        <!-- Push Notifications -->
        <div v-if="push.isSupported" class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="font-semibold mb-1">Notifications</h2>
            <p class="text-sm text-gray-500 mb-4">Get notified about your orders and exclusive deals.</p>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-700">Enable push notifications</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ push.isSubscribed.value ? 'You will receive push notifications.' : 'Currently disabled.' }}
                    </p>
                </div>
                <button
                    @click="togglePushNotifications"
                    :disabled="pushToggling"
                    :class="[
                        'relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none disabled:opacity-60',
                        push.isSubscribed.value ? 'bg-orange-500' : 'bg-gray-200'
                    ]"
                >
                    <span :class="['inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform', push.isSubscribed.value ? 'translate-x-6' : 'translate-x-1']" />
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { usePushNotifications } from '@/composables/usePushNotifications'
import { formatPrice } from '@/composables/useFormatters'
import api from '@/api'

const auth = useAuthStore()
const push = usePushNotifications()

const form = ref({ name: auth.user?.name || '', phone: auth.user?.phone || '' })
const pwForm = ref({ current_password: '', password: '', password_confirmation: '' })

const saving = ref(false)
const profileMsg = ref('')
const profileSuccess = ref(true)

const changingPw = ref(false)
const pwMsg = ref('')
const pwSuccess = ref(true)

const pushToggling = ref(false)

// Shopping summary state
const summaryLoading = ref(true)
const summary = ref({
    totalOrders: 0,
    totalSpent:  0,
    topCategory: null,
    memberSince: '—',
})

async function loadShoppingSummary() {
    try {
        const { data } = await api.get('/orders', { params: { per_page: 100 } })
        const orders = data.data ?? data

        summary.value.totalOrders = orders.length
        summary.value.totalSpent  = orders.reduce((acc, o) => acc + parseFloat(o.total || 0), 0)

        // Compute most-bought category from order items
        const categoryCounts = {}
        for (const order of orders) {
            for (const item of order.items ?? []) {
                const cat = item.category_name || item.product?.category?.name
                if (cat) {
                    categoryCounts[cat] = (categoryCounts[cat] || 0) + (item.quantity || 1)
                }
            }
        }
        const topCat = Object.entries(categoryCounts).sort((a, b) => b[1] - a[1])[0]
        summary.value.topCategory = topCat ? topCat[0] : null

        // Member since from auth user
        if (auth.user?.created_at) {
            summary.value.memberSince = new Date(auth.user.created_at).toLocaleDateString('en-PK', {
                month: 'short', year: 'numeric'
            })
        }
    } catch {
        // Non-critical — leave defaults
    } finally {
        summaryLoading.value = false
    }
}

async function togglePushNotifications() {
    pushToggling.value = true
    try {
        if (push.isSubscribed.value) {
            await push.unsubscribe()
        } else {
            await push.subscribe()
        }
    } finally {
        pushToggling.value = false
    }
}

async function updateProfile() {
    saving.value = true
    profileMsg.value = ''
    try {
        await api.put('/profile', form.value)
        await auth.fetchProfile()
        profileSuccess.value = true
        profileMsg.value = 'Profile updated successfully.'
    } catch {
        profileSuccess.value = false
        profileMsg.value = 'Failed to update profile.'
    } finally {
        saving.value = false
    }
}

async function changePassword() {
    changingPw.value = true
    pwMsg.value = ''
    try {
        await api.put('/profile/password', pwForm.value)
        pwSuccess.value = true
        pwMsg.value = 'Password updated successfully.'
        pwForm.value = { current_password: '', password: '', password_confirmation: '' }
    } catch (e) {
        pwSuccess.value = false
        pwMsg.value = e.response?.data?.message || 'Failed to update password.'
    } finally {
        changingPw.value = false
    }
}

onMounted(() => {
    loadShoppingSummary()
    push.checkSubscriptionStatus()
})
</script>
