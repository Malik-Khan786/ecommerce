<template>
    <div>
        <h1 class="text-xl font-bold text-gray-800 mb-6">Analytics & Reports</h1>

        <!-- Stat cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <div v-for="card in statCards" :key="card.label" class="bg-white rounded-xl p-5 shadow-sm">
                <p class="text-3xl mb-1">{{ card.icon }}</p>
                <p class="text-2xl font-bold text-gray-800">{{ card.value }}</p>
                <p class="text-sm text-gray-500 mt-0.5">{{ card.label }}</p>
            </div>
        </div>

        <!-- Abandoned Carts card -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-orange-400">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-semibold text-gray-600">Abandoned Carts</p>
                    <span class="text-2xl">🛒</span>
                </div>
                <p class="text-3xl font-bold text-orange-500">{{ abandonedCount }}</p>
                <p class="text-xs text-gray-400 mt-1">Users with carts older than 2h, no recent order</p>
                <div v-if="abandonedLoading" class="mt-2 h-3 bg-gray-100 rounded animate-pulse"></div>
                <div v-else-if="abandonedUsers.length" class="mt-3 space-y-1">
                    <div v-for="u in abandonedUsers.slice(0,3)" :key="u.id" class="flex items-center justify-between text-xs">
                        <span class="text-gray-600 truncate">{{ u.name }}</span>
                        <span class="text-orange-500 font-medium shrink-0 ml-2">{{ u.carts?.length || 0 }} items</span>
                    </div>
                    <p v-if="abandonedCount > 3" class="text-xs text-gray-400">+ {{ abandonedCount - 3 }} more</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Monthly revenue chart -->
            <div class="lg:col-span-2 bg-white rounded-xl p-5 shadow-sm">
                <h2 class="font-semibold mb-4">Monthly Revenue ({{ currentYear }})</h2>
                <div v-if="loading" class="h-48 bg-gray-100 rounded animate-pulse"></div>
                <div v-else class="flex items-end gap-1 h-48">
                    <div v-for="month in monthlyData" :key="month.month" class="flex-1 flex flex-col items-center gap-1 group">
                        <div class="relative w-full flex justify-center">
                            <div
                                class="w-full bg-orange-400 hover:bg-orange-500 transition-colors rounded-t cursor-pointer relative"
                                :style="{ height: barHeight(month.revenue) + 'px' }"
                            >
                                <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap pointer-events-none z-10">
                                    Rs. {{ formatPrice(month.revenue) }}
                                </div>
                            </div>
                        </div>
                        <span class="text-xs text-gray-400">{{ monthNames[month.month - 1] }}</span>
                    </div>
                    <!-- Fill empty months if less than 12 -->
                    <div v-for="i in (12 - monthlyData.length)" :key="'empty-' + i" class="flex-1"></div>
                </div>
            </div>

            <!-- Order status breakdown -->
            <div class="bg-white rounded-xl p-5 shadow-sm">
                <h2 class="font-semibold mb-4">Order Status</h2>
                <div v-if="loading" class="space-y-3">
                    <div v-for="i in 5" :key="i" class="h-8 bg-gray-100 rounded animate-pulse"></div>
                </div>
                <div v-else class="space-y-3">
                    <div v-for="stat in statusStats" :key="stat.label">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-600">{{ stat.label }}</span>
                            <span class="font-medium">{{ stat.count }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all" :class="stat.color"
                                :style="{ width: totalOrders ? (stat.count / totalOrders * 100) + '%' : '0%' }"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top products -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-5 border-b">
                <h2 class="font-semibold">Top 5 Products by Sales</h2>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b text-gray-500 text-left">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Product</th>
                        <th class="px-4 py-3 text-right">Units Sold</th>
                        <th class="px-4 py-3 text-right">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading"><td colspan="4" class="text-center py-8 text-gray-400">Loading...</td></tr>
                    <tr v-for="(product, i) in topProducts" :key="product.id" class="border-b last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span :class="['w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold', i === 0 ? 'bg-yellow-400 text-white' : i === 1 ? 'bg-gray-300 text-gray-700' : i === 2 ? 'bg-orange-300 text-white' : 'bg-gray-100 text-gray-500']">
                                {{ i + 1 }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <img :src="product.thumbnail" :alt="product.name" @error="$event.target.style.display='none'" class="w-10 h-10 object-cover rounded-lg bg-gray-100 shrink-0" />
                                <span class="font-medium line-clamp-1">{{ product.name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ product.order_items_count }}</td>
                        <td class="px-4 py-3 text-right font-bold text-orange-500">
                            Rs. {{ formatPrice(product.price * product.order_items_count) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { formatPrice } from '@/composables/useFormatters'
import api from '@/api'

const data    = ref(null)
const loading = ref(true)
const currentYear = new Date().getFullYear()
const monthNames  = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']

const abandonedCount   = ref(0)
const abandonedUsers   = ref([])
const abandonedLoading = ref(true)

const monthlyData = computed(() => data.value?.salesByMonth || [])
const topProducts  = computed(() => data.value?.topProducts || [])
const stats        = computed(() => data.value?.stats || {})
const totalOrders  = computed(() => stats.value.total_orders || 1)

const maxRevenue = computed(() => Math.max(...monthlyData.value.map(m => m.revenue), 1))
function barHeight(rev) {
    return Math.max(4, (rev / maxRevenue.value) * 160)
}

const statCards = computed(() => [
    { icon: '💰', label: 'Total Revenue',    value: 'Rs. ' + formatPrice(stats.value.total_revenue || 0) },
    { icon: '📦', label: 'Total Orders',     value: stats.value.total_orders || 0 },
    { icon: '📊', label: 'Avg Order Value',  value: stats.value.total_orders ? 'Rs. ' + formatPrice((stats.value.total_revenue || 0) / stats.value.total_orders) : 'Rs. 0' },
    { icon: '👥', label: 'Total Customers',  value: stats.value.total_customers || 0 },
])

const statusStats = computed(() => [
    { label: 'Pending',    count: data.value?.stats?.pending_orders || 0, color: 'bg-yellow-400' },
    { label: 'Delivered',  count: data.value?.recentOrders?.filter(o => o.status === 'delivered').length || 0, color: 'bg-green-400' },
    { label: 'Shipped',    count: data.value?.recentOrders?.filter(o => o.status === 'shipped').length || 0, color: 'bg-purple-400' },
    { label: 'Processing', count: data.value?.recentOrders?.filter(o => o.status === 'processing').length || 0, color: 'bg-blue-400' },
    { label: 'Cancelled',  count: data.value?.recentOrders?.filter(o => o.status === 'cancelled').length || 0, color: 'bg-red-400' },
])

onMounted(async () => {
    try {
        const { data: res } = await api.get('/admin/dashboard')
        data.value = res
    } finally {
        loading.value = false
    }

    try {
        const { data: res } = await api.get('/admin/abandoned-carts')
        abandonedCount.value = res.count
        abandonedUsers.value = res.users?.data || []
    } catch {
        // silently fail
    } finally {
        abandonedLoading.value = false
    }
})
</script>
