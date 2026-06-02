<template>
    <div>
        <h1 class="text-xl font-bold text-gray-800 mb-6">Dashboard</h1>

        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div v-for="stat in statCards" :key="stat.label" class="bg-white rounded-xl p-5 shadow-sm">
                <p class="text-3xl mb-1">{{ stat.icon }}</p>
                <p class="text-2xl font-bold text-gray-800">{{ stat.value }}</p>
                <p class="text-sm text-gray-500">{{ stat.label }}</p>
            </div>
        </div>

        <!-- Alert strip for low stock / pending orders -->
        <div v-if="!loading" class="flex gap-3 flex-wrap mb-6">
            <RouterLink v-if="data?.stats?.pending_orders > 0" to="/admin/orders?status=pending"
                class="flex items-center gap-2 bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-xl px-4 py-2 text-sm hover:bg-yellow-100 transition-colors">
                🔔 <strong>{{ data.stats.pending_orders }}</strong> pending order{{ data.stats.pending_orders > 1 ? 's' : '' }} awaiting confirmation
            </RouterLink>
            <RouterLink v-if="data?.stats?.low_stock > 0" to="/admin/products"
                class="flex items-center gap-2 bg-orange-50 border border-orange-200 text-orange-700 rounded-xl px-4 py-2 text-sm hover:bg-orange-100 transition-colors">
                ⚠️ <strong>{{ data.stats.low_stock }}</strong> product{{ data.stats.low_stock > 1 ? 's' : '' }} running low on stock
            </RouterLink>
            <RouterLink v-if="data?.stats?.out_of_stock > 0" to="/admin/products"
                class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-2 text-sm hover:bg-red-100 transition-colors">
                🚫 <strong>{{ data.stats.out_of_stock }}</strong> product{{ data.stats.out_of_stock > 1 ? 's' : '' }} out of stock
            </RouterLink>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Orders -->
            <div class="bg-white rounded-xl p-5 shadow-sm">
                <h2 class="font-semibold mb-4">Recent Orders</h2>
                <div v-if="loading" class="space-y-2">
                    <div v-for="i in 5" :key="i" class="h-12 bg-gray-100 rounded animate-pulse"></div>
                </div>
                <table v-else class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="pb-2">Order</th>
                            <th class="pb-2">Customer</th>
                            <th class="pb-2">Total</th>
                            <th class="pb-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="order in data?.recentOrders" :key="order.id" class="border-b last:border-0">
                            <td class="py-2 font-mono text-xs">{{ order.order_number }}</td>
                            <td class="py-2 text-gray-600">{{ order.user?.name || 'Guest' }}</td>
                            <td class="py-2 font-medium">Rs. {{ formatPrice(order.total) }}</td>
                            <td class="py-2">
                                <span :class="statusClass(order.status)" class="px-2 py-0.5 rounded-full text-xs">{{ order.status }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <RouterLink to="/admin/orders" class="text-orange-500 text-xs mt-3 block hover:underline">View all orders →</RouterLink>
            </div>

            <!-- Top Products -->
            <div class="bg-white rounded-xl p-5 shadow-sm">
                <h2 class="font-semibold mb-4">Top Selling Products</h2>
                <div class="space-y-3">
                    <div v-for="product in data?.topProducts" :key="product.id" class="flex items-center gap-3">
                        <img :src="product.thumbnail" class="w-10 h-10 object-cover rounded-lg" />
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium truncate">{{ product.name }}</p>
                            <p class="text-xs text-gray-400">{{ product.order_items_count }} sold</p>
                        </div>
                        <p class="text-sm font-bold text-orange-500">Rs. {{ formatPrice(product.sale_price ?? product.price) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/api'
import { formatPrice, statusClass } from '@/composables/useFormatters'

const data    = ref(null)
const loading = ref(true)

const statCards = computed(() => {
    const s = data.value?.stats || {}
    return [
        { icon: '📦', label: 'Total Orders', value: s.total_orders || 0 },
        { icon: '💰', label: 'Revenue', value: 'Rs. ' + formatPrice(s.total_revenue || 0) },
        { icon: '🛍️', label: 'Products', value: s.total_products || 0 },
        { icon: '👥', label: 'Customers', value: s.total_customers || 0 },
    ]
})

onMounted(async () => {
    try {
        const { data: res } = await api.get('/admin/dashboard')
        data.value = res
    } finally {
        loading.value = false
    }
})
</script>
