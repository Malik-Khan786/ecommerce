<template>
    <div class="mt-3">
        <!-- Already subscribed -->
        <div v-if="subscribed" class="flex items-center gap-2 text-green-600 text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            We'll notify you when this is back in stock!
        </div>

        <!-- Notify button (not yet subscribed, form not open) -->
        <button
            v-else-if="!showForm"
            @click="showForm = true"
            class="flex items-center gap-2 border border-gray-300 hover:border-orange-400 hover:text-orange-500 text-gray-600 text-sm font-medium px-4 py-2.5 rounded-xl transition-colors"
        >
            🔔 Notify Me When Available
        </button>

        <!-- Inline email form -->
        <div v-else class="flex flex-col sm:flex-row gap-2">
            <input
                v-model="email"
                type="email"
                placeholder="your@email.com"
                class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                @keydown.enter="subscribe"
            />
            <div class="flex gap-2">
                <button
                    @click="subscribe"
                    :disabled="subscribing || !email.trim()"
                    class="bg-orange-500 hover:bg-orange-600 disabled:opacity-50 text-white font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm whitespace-nowrap"
                >
                    {{ subscribing ? 'Subscribing...' : 'Subscribe' }}
                </button>
                <button
                    @click="showForm = false; error = ''"
                    class="border border-gray-200 text-gray-500 hover:bg-gray-50 px-3 py-2.5 rounded-xl transition-colors text-sm"
                    title="Cancel"
                >
                    ✕
                </button>
            </div>
        </div>

        <p v-if="error" class="text-red-500 text-xs mt-1.5">{{ error }}</p>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api'

const props = defineProps({
    productId: {
        type: Number,
        required: true,
    },
})

const STORAGE_KEY = (id) => `stock_alert_${id}`

const subscribed  = ref(false)
const showForm    = ref(false)
const email       = ref('')
const subscribing = ref(false)
const error       = ref('')

function checkLocalStorage() {
    try {
        subscribed.value = localStorage.getItem(STORAGE_KEY(props.productId)) === 'true'
    } catch {
        subscribed.value = false
    }
}

async function subscribe() {
    error.value = ''
    if (!email.value.trim()) return
    subscribing.value = true
    try {
        await api.post(`/stock-alerts/${props.productId}`, { email: email.value.trim() })
        subscribed.value = true
        showForm.value   = false
        try {
            localStorage.setItem(STORAGE_KEY(props.productId), 'true')
        } catch { /* localStorage unavailable */ }
    } catch (err) {
        error.value = err?.response?.data?.message || 'Failed to subscribe. Please try again.'
    } finally {
        subscribing.value = false
    }
}

onMounted(checkLocalStorage)
</script>
