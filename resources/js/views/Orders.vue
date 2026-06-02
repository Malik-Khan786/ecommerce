<template>
    <div>
        <h1 class="text-2xl font-bold text-gray-800 mb-6">My Orders</h1>

        <div v-if="loading" class="space-y-3">
            <div v-for="i in 3" :key="i" class="bg-white rounded-xl h-24 animate-pulse"></div>
        </div>

        <div v-else-if="orders.length === 0" class="text-center py-20">
            <p class="text-5xl mb-4">📦</p>
            <p class="text-gray-500 mb-4">No orders yet.</p>
            <RouterLink to="/products" class="bg-orange-500 text-white px-6 py-2 rounded-xl hover:bg-orange-600">Start Shopping</RouterLink>
        </div>

        <div v-else class="space-y-4">
            <div v-for="order in orders" :key="order.id" class="bg-white rounded-xl p-5 shadow-sm">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <p class="font-bold text-gray-800">{{ order.order_number }}</p>
                        <p class="text-xs text-gray-400">{{ formatDate(order.created_at) }}</p>
                    </div>
                    <div class="text-right">
                        <span :class="statusClass(order.status)" class="px-3 py-1 rounded-full text-xs font-medium">
                            {{ order.status.charAt(0).toUpperCase() + order.status.slice(1) }}
                        </span>
                        <p class="text-orange-500 font-bold mt-1">Rs. {{ formatPrice(order.total) }}</p>
                    </div>
                </div>

                <div class="flex gap-2 overflow-x-auto mb-3">
                    <div v-for="item in order.items" :key="item.id" class="shrink-0">
                        <img :src="item.product_thumbnail || FALLBACK_IMG" @error="onImgError" :alt="item.product_name" class="w-12 h-12 object-cover rounded-lg bg-gray-100" />
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-400">{{ order.items?.length }} item(s) · {{ order.payment_method.toUpperCase() }}</span>
                    <RouterLink :to="`/orders/${order.uuid}`" class="text-orange-500 text-sm hover:underline">View Details →</RouterLink>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api'
import { FALLBACK_IMG, onImgError } from '@/composables/useImgFallback'

const orders  = ref([])
const loading = ref(true)

function formatPrice(val) { return Number(val).toLocaleString('en-PK') }
function formatDate(d) { return new Date(d).toLocaleDateString('en-PK', { year: 'numeric', month: 'short', day: 'numeric' }) }

function statusClass(s) {
    const map = { pending: 'bg-yellow-100 text-yellow-700', confirmed: 'bg-blue-100 text-blue-700', processing: 'bg-indigo-100 text-indigo-700', shipped: 'bg-purple-100 text-purple-700', delivered: 'bg-green-100 text-green-700', cancelled: 'bg-red-100 text-red-700' }
    return map[s] || 'bg-gray-100 text-gray-600'
}

onMounted(async () => {
    try {
        const { data } = await api.get('/orders')
        orders.value = data.data
    } finally {
        loading.value = false
    }
})
</script>
