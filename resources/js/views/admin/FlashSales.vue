<template>
    <div class="p-6 max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Flash Sales</h1>
                <p class="text-sm text-gray-400 mt-0.5">Manage time-limited discount sales on products.</p>
            </div>
            <button
                @click="openModal()"
                class="bg-orange-500 hover:bg-orange-600 text-white font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Flash Sale
            </button>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div v-if="loading" class="text-center py-16 text-gray-400 text-sm">Loading flash sales...</div>

            <div v-else-if="sales.length === 0" class="text-center py-16">
                <p class="text-4xl mb-2">⚡</p>
                <p class="text-gray-400 text-sm">No flash sales yet. Create one to get started.</p>
            </div>

            <table v-else class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left text-gray-500 font-medium px-6 py-3">Product</th>
                        <th class="text-left text-gray-500 font-medium px-6 py-3">Discount</th>
                        <th class="text-left text-gray-500 font-medium px-6 py-3">Starts At</th>
                        <th class="text-left text-gray-500 font-medium px-6 py-3">Ends At</th>
                        <th class="text-left text-gray-500 font-medium px-6 py-3">Status</th>
                        <th class="text-right text-gray-500 font-medium px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="sale in sales" :key="sale.id" class="border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="sale.product?.thumbnail"
                                    :src="sale.product.thumbnail"
                                    :alt="sale.product?.name"
                                    class="w-10 h-10 object-cover rounded-lg border border-gray-100"
                                />
                                <div v-else class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center text-gray-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <span class="font-medium text-gray-800 max-w-[180px] truncate">{{ sale.product?.name ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-orange-600 font-bold text-base">{{ sale.discount_percent }}%</span>
                            <span class="text-gray-400 text-xs ml-1">OFF</span>
                        </td>
                        <td class="px-6 py-4 text-gray-600 whitespace-nowrap">{{ formatDate(sale.starts_at) }}</td>
                        <td class="px-6 py-4 text-gray-600 whitespace-nowrap">{{ formatDate(sale.ends_at) }}</td>
                        <td class="px-6 py-4">
                            <span :class="statusClass(sale)">{{ statusLabel(sale) }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    @click="openModal(sale)"
                                    class="text-sm text-blue-600 hover:text-blue-800 font-medium px-3 py-1.5 rounded-lg hover:bg-blue-50 transition-colors"
                                >
                                    Edit
                                </button>
                                <button
                                    @click="deleteSale(sale)"
                                    class="text-sm text-red-500 hover:text-red-700 font-medium px-3 py-1.5 rounded-lg hover:bg-red-50 transition-colors"
                                >
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeModal"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10">
                    <h2 class="text-lg font-bold text-gray-900 mb-5">{{ editingId ? 'Edit Flash Sale' : 'New Flash Sale' }}</h2>

                    <div class="space-y-4">
                        <!-- Product -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Product <span class="text-red-400">*</span></label>
                            <select
                                v-model="form.product_id"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 bg-white"
                            >
                                <option value="">Select a product</option>
                                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>

                        <!-- Discount % -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Discount % <span class="text-red-400">*</span></label>
                            <input
                                v-model.number="form.discount_percent"
                                type="number"
                                min="1"
                                max="99"
                                placeholder="e.g. 25"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                            />
                        </div>

                        <!-- Starts At -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Starts At <span class="text-red-400">*</span></label>
                            <input
                                v-model="form.starts_at"
                                type="datetime-local"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                            />
                        </div>

                        <!-- Ends At -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Ends At <span class="text-red-400">*</span></label>
                            <input
                                v-model="form.ends_at"
                                type="datetime-local"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                            />
                        </div>

                        <!-- Active -->
                        <div class="flex items-center gap-3">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="w-4 h-4 rounded accent-orange-500"
                            />
                            <label for="is_active" class="text-sm font-medium text-gray-700 cursor-pointer select-none">Active</label>
                        </div>
                    </div>

                    <p v-if="formError" class="text-red-500 text-sm mt-3">{{ formError }}</p>

                    <div class="flex gap-3 mt-6">
                        <button
                            @click="closeModal"
                            class="flex-1 border border-gray-200 text-gray-600 font-semibold py-2.5 rounded-xl hover:bg-gray-50 transition-colors text-sm"
                        >
                            Cancel
                        </button>
                        <button
                            @click="saveSale"
                            :disabled="saving"
                            class="flex-1 bg-orange-500 hover:bg-orange-600 disabled:opacity-50 text-white font-semibold py-2.5 rounded-xl transition-colors text-sm"
                        >
                            {{ saving ? 'Saving...' : (editingId ? 'Update' : 'Create') }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api'
import { formatDate, formatPrice } from '@/composables/useFormatters'

const sales     = ref([])
const products  = ref([])
const loading   = ref(true)
const showModal = ref(false)
const saving    = ref(false)
const editingId = ref(null)
const formError = ref('')

const defaultForm = () => ({
    product_id:       '',
    discount_percent: '',
    starts_at:        '',
    ends_at:          '',
    is_active:        true,
})

const form = ref(defaultForm())

function statusLabel(sale) {
    const now = new Date()
    if (!sale.is_active) return 'Inactive'
    if (new Date(sale.ends_at) < now) return 'Expired'
    if (new Date(sale.starts_at) > now) return 'Scheduled'
    return 'Active'
}

function statusClass(sale) {
    const label = statusLabel(sale)
    return [
        'px-2.5 py-1 rounded-full text-xs font-semibold',
        label === 'Active'    ? 'bg-green-100 text-green-700' :
        label === 'Inactive'  ? 'bg-gray-100 text-gray-500' :
        label === 'Expired'   ? 'bg-red-100 text-red-500' :
        'bg-blue-100 text-blue-600',
    ]
}

async function fetchSales() {
    loading.value = true
    try {
        const { data } = await api.get('/admin/flash-sales')
        sales.value = data
    } catch {
        sales.value = []
    } finally {
        loading.value = false
    }
}

async function fetchProducts() {
    try {
        const { data } = await api.get('/admin/products')
        products.value = Array.isArray(data) ? data : (data.data ?? [])
    } catch {
        products.value = []
    }
}

function toDatetimeLocal(str) {
    if (!str) return ''
    return str.slice(0, 16)
}

function openModal(sale = null) {
    formError.value = ''
    if (sale) {
        editingId.value = sale.id
        form.value = {
            product_id:       sale.product_id,
            discount_percent: sale.discount_percent,
            starts_at:        toDatetimeLocal(sale.starts_at),
            ends_at:          toDatetimeLocal(sale.ends_at),
            is_active:        !!sale.is_active,
        }
    } else {
        editingId.value = null
        form.value = defaultForm()
    }
    showModal.value = true
}

function closeModal() {
    showModal.value = false
}

async function saveSale() {
    formError.value = ''
    if (!form.value.product_id || !form.value.discount_percent || !form.value.starts_at || !form.value.ends_at) {
        formError.value = 'Please fill in all required fields.'
        return
    }
    saving.value = true
    try {
        if (editingId.value) {
            await api.put(`/admin/flash-sales/${editingId.value}`, form.value)
        } else {
            await api.post('/admin/flash-sales', form.value)
        }
        closeModal()
        await fetchSales()
    } catch (err) {
        formError.value = err?.response?.data?.message || 'Failed to save. Please try again.'
    } finally {
        saving.value = false
    }
}

async function deleteSale(sale) {
    if (!confirm(`Delete flash sale for "${sale.product?.name}"?`)) return
    try {
        await api.delete(`/admin/flash-sales/${sale.id}`)
        await fetchSales()
    } catch {
        alert('Failed to delete.')
    }
}

onMounted(() => {
    fetchSales()
    fetchProducts()
})
</script>
