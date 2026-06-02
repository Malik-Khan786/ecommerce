<template>
    <div v-if="loading" class="text-center py-20 text-gray-400">Loading...</div>
    <div v-else-if="order">
        <div class="flex items-center gap-3 mb-6">
            <RouterLink to="/orders" class="text-gray-400 hover:text-gray-600">← My Orders</RouterLink>
            <span class="text-gray-300">/</span>
            <span class="font-bold text-gray-800">{{ order.order_number }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-4">
                <!-- Status -->
                <div class="bg-white rounded-xl p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Order placed on {{ formatDate(order.created_at) }}</p>
                            <p class="text-sm mt-1">Payment: <span class="font-medium">{{ order.payment_method.toUpperCase() }}</span></p>
                        </div>
                        <span :class="statusClass(order.status)" class="px-3 py-1.5 rounded-full text-sm font-medium">
                            {{ order.status.charAt(0).toUpperCase() + order.status.slice(1) }}
                        </span>
                    </div>

                    <!-- Action buttons -->
                    <div class="flex flex-wrap gap-2 mt-4">
                        <button
                            v-if="['pending', 'confirmed'].includes(order.status)"
                            @click="cancelOrder"
                            :disabled="cancelling"
                            class="text-red-500 border border-red-300 hover:bg-red-50 text-sm px-4 py-1.5 rounded-lg transition-colors disabled:opacity-60"
                        >{{ cancelling ? 'Cancelling...' : 'Cancel Order' }}</button>

                        <button
                            v-if="order.status === 'delivered'"
                            @click="reorder"
                            :disabled="reordering"
                            class="bg-orange-500 hover:bg-orange-600 text-white text-sm px-4 py-1.5 rounded-lg transition-colors disabled:opacity-60"
                        >{{ reordering ? 'Adding...' : '🔁 Reorder' }}</button>

                        <RouterLink
                            v-if="order.status === 'delivered'"
                            :to="`/orders/${order.uuid}/return`"
                            class="text-gray-600 border border-gray-300 hover:bg-gray-50 text-sm px-4 py-1.5 rounded-lg transition-colors"
                        >↩ Return Request</RouterLink>

                        <RouterLink
                            :to="`/track/${order.uuid}`"
                            class="text-blue-500 border border-blue-200 hover:bg-blue-50 text-sm px-4 py-1.5 rounded-lg transition-colors"
                        >📍 Track Order</RouterLink>

                        <button @click="downloadInvoice" :disabled="downloadingInvoice"
                            class="text-gray-600 border border-gray-300 hover:bg-gray-50 text-sm px-4 py-1.5 rounded-lg transition-colors disabled:opacity-60">
                            {{ downloadingInvoice ? '...' : '📄 Invoice' }}
                        </button>
                    </div>
                </div>

                <!-- Items -->
                <div class="bg-white rounded-xl p-5 shadow-sm">
                    <h2 class="font-semibold mb-4">Order Items</h2>
                    <div v-for="item in order.items" :key="item.id" class="flex items-center gap-3 py-3 border-b last:border-0">
                        <img :src="item.product_thumbnail || FALLBACK_IMG" @error="onImgError" :alt="item.product_name" class="w-14 h-14 object-cover rounded-lg bg-gray-100" />
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-800">{{ item.product_name }}</p>
                            <p class="text-xs text-gray-400">Qty: {{ item.quantity }} × Rs. {{ formatPrice(item.price) }}</p>
                        </div>
                        <span class="font-bold text-sm">Rs. {{ formatPrice(item.subtotal) }}</span>
                    </div>
                </div>
            </div>

            <!-- Shipping + Total -->
            <div class="space-y-4">
                <div class="bg-white rounded-xl p-5 shadow-sm">
                    <h2 class="font-semibold mb-3">Shipping Address</h2>
                    <p class="text-sm text-gray-700">{{ order.shipping_name }}</p>
                    <p class="text-sm text-gray-500">{{ order.shipping_phone }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ order.shipping_address }}</p>
                    <p class="text-sm text-gray-500">{{ order.shipping_city }}, {{ order.shipping_state }}</p>
                    <p class="text-sm text-gray-500">{{ order.shipping_country }}</p>
                    <div v-if="order.estimated_delivery_date" class="mt-3 pt-3 border-t flex items-center gap-2 text-sm text-orange-700">
                        <span aria-hidden="true">📅</span>
                        <span>Est. Delivery: <strong>{{ formatDate(order.estimated_delivery_date) }}</strong></span>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm">
                    <h2 class="font-semibold mb-3">Order Total</h2>
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span>Rs. {{ formatPrice(order.subtotal) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Shipping</span><span>Rs. {{ formatPrice(order.shipping_cost) }}</span></div>
                        <div class="flex justify-between font-bold border-t pt-2 mt-2">
                            <span>Total</span>
                            <span class="text-orange-500">Rs. {{ formatPrice(order.total) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import { useCartStore } from '@/stores/cart'
import api from '@/api'
import { FALLBACK_IMG, onImgError } from '@/composables/useImgFallback'
import { formatPrice, formatDate } from '@/composables/useFormatters'

const route     = useRoute()
const router    = useRouter()
const toast     = useToast()
const cart      = useCartStore()
const order     = ref(null)
const loading   = ref(true)
const cancelling        = ref(false)
const reordering        = ref(false)
const downloadingInvoice = ref(false)

async function downloadInvoice() {
    downloadingInvoice.value = true
    try {
        const response = await import('@/api').then(m => m.default.get(
            `/orders/${order.value.uuid}/invoice`,
            { responseType: 'blob' }
        ))
        const url  = URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }))
        const link = document.createElement('a')
        link.href     = url
        link.download = `invoice-${order.value.order_number}.pdf`
        link.click()
        URL.revokeObjectURL(url)
    } catch {
        const { useToast } = await import('vue-toastification')
        useToast().error('Failed to download invoice.')
    } finally {
        downloadingInvoice.value = false
    }
}

function statusClass(s) {
    const map = { pending: 'bg-yellow-100 text-yellow-700', confirmed: 'bg-blue-100 text-blue-700', processing: 'bg-indigo-100 text-indigo-700', shipped: 'bg-purple-100 text-purple-700', delivered: 'bg-green-100 text-green-700', cancelled: 'bg-red-100 text-red-700' }
    return map[s] || 'bg-gray-100 text-gray-600'
}

async function reorder() {
    reordering.value = true
    try {
        let added = 0
        for (const item of order.value.items) {
            if (item.product_id) {
                await cart.addItem(item.product_id, item.quantity)
                added++
            }
        }
        toast.success(`${added} item(s) added to cart!`)
        router.push({ name: 'cart' })
    } catch {
        toast.error('Some items could not be added.')
    } finally {
        reordering.value = false
    }
}

async function cancelOrder() {
    cancelling.value = true
    try {
        const { data } = await api.put(`/orders/${order.value.uuid}/cancel`)
        order.value = data
        toast.success('Order cancelled.')
    } catch (e) {
        toast.error(e.response?.data?.message || 'Cannot cancel order.')
    } finally {
        cancelling.value = false
    }
}

onMounted(async () => {
    try {
        const { data } = await api.get(`/orders/${route.params.uuid}`)
        order.value = data
    } finally {
        loading.value = false
    }
})
</script>
