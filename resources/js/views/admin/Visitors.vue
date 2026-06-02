<template>
    <div>
        <h1 class="text-xl font-bold text-gray-800 mb-6">Visitor Log</h1>

        <!-- Summary cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl p-5 shadow-sm text-center">
                <p class="text-3xl font-bold text-orange-500">{{ summary.stats?.total_views || 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Total Views</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm text-center">
                <p class="text-3xl font-bold text-green-500">{{ summary.stats?.logged_in_views || 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Logged-in Views</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm text-center">
                <p class="text-3xl font-bold text-blue-500">{{ summary.stats?.guest_views || 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Guest Views</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm text-center">
                <p class="text-3xl font-bold text-purple-500">{{ summary.stats?.unique_emails || 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Unique Emails</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Top viewed products -->
            <div class="bg-white rounded-xl p-5 shadow-sm">
                <h2 class="font-semibold mb-4">Most Viewed Products</h2>
                <div v-for="item in summary.topProducts" :key="item.product_id" class="flex items-center gap-3 py-2 border-b last:border-0">
                    <img :src="item.product?.thumbnail" :alt="item.product?.name" @error="$event.target.style.display='none'" class="w-10 h-10 object-cover rounded-lg bg-gray-100" />
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ item.product?.name }}</p>
                        <p class="text-xs text-gray-400">{{ item.unique_visitors }} unique visitor(s)</p>
                    </div>
                    <span class="text-sm font-bold text-orange-500">{{ item.views }} views</span>
                </div>
                <p v-if="!summary.topProducts?.length" class="text-sm text-gray-400 text-center py-4">No data yet</p>
            </div>

            <!-- Top visitors -->
            <div class="bg-white rounded-xl p-5 shadow-sm">
                <h2 class="font-semibold mb-4">Most Active Visitors</h2>
                <div v-for="v in summary.topVisitors" :key="v.email" class="flex items-center gap-3 py-2 border-b last:border-0">
                    <div class="w-9 h-9 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 font-bold text-sm shrink-0">
                        {{ v.name?.charAt(0)?.toUpperCase() || v.email?.charAt(0)?.toUpperCase() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ v.name || '—' }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ v.email }}</p>
                        <p class="text-xs text-gray-300">Last seen: {{ formatDate(v.last_seen) }}</p>
                    </div>
                    <span class="text-sm font-bold text-blue-500">{{ v.views }} views</span>
                </div>
                <p v-if="!summary.topVisitors?.length" class="text-sm text-gray-400 text-center py-4">No logged-in visitors yet</p>
            </div>
        </div>

        <!-- Full log table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b flex items-center gap-3">
                <h2 class="font-semibold flex-1">All View Logs</h2>
                <input v-model="search" @input="fetchLogs" type="text" placeholder="Search by email, name or IP..."
                    class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm w-64 focus:outline-none focus:border-orange-400" />
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b text-gray-500 text-left">
                        <tr>
                            <th class="px-4 py-3">Visitor</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Product</th>
                            <th class="px-4 py-3">Browser / Device</th>
                            <th class="px-4 py-3">IP Address</th>
                            <th class="px-4 py-3">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading">
                            <td colspan="6" class="text-center py-8 text-gray-400">Loading...</td>
                        </tr>
                        <tr v-for="log in logs" :key="log.id" class="border-b last:border-0 hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                                        :class="log.user_id ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                        {{ log.name?.charAt(0)?.toUpperCase() || '?' }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">{{ log.name || 'Guest' }}</p>
                                        <span v-if="log.user_id" class="text-xs bg-green-100 text-green-700 px-1.5 py-0.5 rounded-full">Logged in</span>
                                        <span v-else class="text-xs bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded-full">Guest</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span v-if="log.email" class="text-blue-600">
                                    <a :href="`mailto:${log.email}`" class="hover:underline">{{ log.email }}</a>
                                </span>
                                <span v-else class="text-gray-400 text-xs">—</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <img :src="log.product?.thumbnail" :alt="log.product?.name" @error="$event.target.style.display='none'" class="w-8 h-8 object-cover rounded bg-gray-100 shrink-0" />
                                    <span class="text-gray-700 line-clamp-1">{{ log.product?.name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                <p>{{ log.browser }} / {{ log.device }}</p>
                                <p class="text-gray-400">{{ log.platform }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ log.ip_address }}</td>
                            <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">{{ formatDate(log.viewed_at) }}</td>
                        </tr>
                        <tr v-if="!loading && !logs.length">
                            <td colspan="6" class="text-center py-10 text-gray-400">No views logged yet. Visit a product page to start tracking.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination?.last_page > 1" class="flex justify-center gap-2 p-4">
                <button v-for="p in pagination.last_page" :key="p" @click="page = p; fetchLogs()"
                    :class="['px-3 py-1 rounded text-sm', p === page ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200']">
                    {{ p }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api'

const logs       = ref([])
const summary    = ref({})
const pagination = ref(null)
const loading    = ref(true)
const search     = ref('')
const page       = ref(1)

function formatDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleString('en-PK', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

async function fetchLogs() {
    loading.value = true
    try {
        const { data } = await api.get('/admin/visitors', { params: { search: search.value, page: page.value } })
        logs.value       = data.data
        pagination.value = data
    } finally {
        loading.value = false
    }
}

async function fetchSummary() {
    const { data } = await api.get('/admin/visitors/summary')
    summary.value = data
}

onMounted(() => {
    fetchLogs()
    fetchSummary()
})
</script>
