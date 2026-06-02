<template>
    <div class="max-w-2xl mx-auto">
        <!-- Search form -->
        <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Track Your Order</h1>
            <p class="text-gray-500 text-sm mb-4">Enter your order UUID from the confirmation email</p>
            <form @submit.prevent="trackOrder" class="flex gap-2">
                <input v-model="orderInput" placeholder="e.g. 824e0898-6850-491c-bf67-..." class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" />
                <button type="submit" :disabled="loading" class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium disabled:opacity-60">
                    {{ loading ? '...' : 'Track' }}
                </button>
            </form>
            <p v-if="error" class="text-red-500 text-sm mt-2">{{ error }}</p>
        </div>

        <!-- Result -->
        <div v-if="order">
            <!-- Order summary -->
            <div class="bg-white rounded-2xl shadow-sm p-6 mb-4">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="font-bold text-gray-800 text-lg">{{ order.order_number }}</p>
                        <p class="text-sm text-gray-500">{{ order.shipping_name }} · {{ order.shipping_city }}</p>
                    </div>
                    <span :class="statusClass(order.status)" class="px-3 py-1.5 rounded-full text-sm font-semibold">
                        {{ statusLabel(order.status) }}
                    </span>
                </div>

                <!-- Timeline -->
                <div class="relative">
                    <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                    <div v-for="(track, i) in order.tracking" :key="i" class="relative flex gap-4 pb-6 last:pb-0">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 z-10"
                            :class="i === 0 ? 'bg-orange-500 text-white' : 'bg-white border-2 border-gray-300 text-gray-400'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="flex-1 pt-1">
                            <p class="font-semibold text-sm text-gray-800">{{ track.title }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ track.description }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ formatDateTime(track.created_at) }}</p>
                        </div>
                    </div>

                    <!-- Pending future steps (greyed out) -->
                    <div v-for="step in pendingSteps" :key="step" class="relative flex gap-4 pb-6 last:pb-0 opacity-40">
                        <div class="w-8 h-8 rounded-full border-2 border-dashed border-gray-300 flex items-center justify-center shrink-0 z-10 bg-white">
                            <div class="w-2 h-2 rounded-full bg-gray-300"></div>
                        </div>
                        <div class="flex-1 pt-1">
                            <p class="font-semibold text-sm text-gray-500">{{ step }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="font-semibold mb-3">Order Items</h3>
                <div v-for="item in order.items" :key="item.id" class="flex items-center gap-3 py-2 border-b last:border-0">
                    <img :src="item.product_thumbnail || FALLBACK_IMG" @error="onImgError" :alt="item.product_name" class="w-12 h-12 object-cover rounded-lg bg-gray-100" />
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800">{{ item.product_name }}</p>
                        <p class="text-xs text-gray-400">Qty: {{ item.quantity }}</p>
                    </div>
                    <span class="text-sm font-bold">Rs. {{ formatPrice(item.subtotal) }}</span>
                </div>
                <div class="border-t pt-3 mt-1 flex justify-between font-bold">
                    <span>Total</span>
                    <span class="text-orange-500">Rs. {{ formatPrice(order.total) }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/api'
import { formatPrice, formatDateTime, statusClass, statusLabel } from '@/composables/useFormatters'
import { FALLBACK_IMG, onImgError } from '@/composables/useImgFallback'

const route      = useRoute()
const orderInput = ref(route.query.uuid || '')
const order      = ref(null)
const loading    = ref(false)
const error      = ref('')

const allSteps = ['Order Placed', 'Order Confirmed', 'Being Prepared', 'Order Shipped', 'Delivered']

const pendingSteps = computed(() => {
    if (!order.value) return []
    const done = new Set(order.value.tracking.map(t => t.title))
    return allSteps.filter(s => !done.has(s))
})

async function trackOrder() {
    if (!orderInput.value.trim()) return
    loading.value = true
    error.value   = ''
    order.value   = null
    try {
        const { data } = await api.get(`/orders/track/${orderInput.value.trim()}`)
        order.value = data
    } catch {
        error.value = 'Order not found. Please check the order ID.'
    } finally {
        loading.value = false
    }
}

if (orderInput.value) trackOrder()
</script>
