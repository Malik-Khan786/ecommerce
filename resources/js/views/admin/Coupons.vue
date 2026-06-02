<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-bold text-gray-800">Coupons</h1>
            <button @click="openModal()" class="bg-orange-500 text-white px-4 py-2 rounded-xl text-sm hover:bg-orange-600">+ Add Coupon</button>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b text-gray-500 text-left">
                    <tr>
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Value</th>
                        <th class="px-4 py-3">Min Order</th>
                        <th class="px-4 py-3">Used</th>
                        <th class="px-4 py-3">Expires</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading"><td colspan="8" class="text-center py-8 text-gray-400">Loading...</td></tr>
                    <tr v-for="c in coupons" :key="c.id" class="border-b last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono font-bold text-orange-600">{{ c.code }}</td>
                        <td class="px-4 py-3">
                            <span :class="c.type === 'percent' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700'" class="px-2 py-0.5 rounded-full text-xs font-medium">
                                {{ c.type === 'percent' ? '%' : 'Rs.' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-medium">{{ c.type === 'percent' ? c.value + '%' : 'Rs. ' + formatPrice(c.value) }}</td>
                        <td class="px-4 py-3 text-gray-500">Rs. {{ formatPrice(c.min_order_amount) }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ c.used_count }}{{ c.usage_limit ? ' / ' + c.usage_limit : '' }}</td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ c.expires_at ? formatDate(c.expires_at) : 'Never' }}</td>
                        <td class="px-4 py-3">
                            <span :class="c.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'" class="px-2 py-0.5 rounded-full text-xs font-medium">
                                {{ c.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <button @click="openModal(c)" class="text-blue-500 hover:text-blue-700 text-xs">Edit</button>
                                <button @click="deleteCoupon(c)" class="text-red-400 hover:text-red-600 text-xs">Delete</button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!loading && !coupons.length">
                        <td colspan="8" class="text-center py-10 text-gray-400">No coupons yet.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl w-full max-w-md p-6">
                <div class="flex justify-between mb-5">
                    <h2 class="font-bold text-lg">{{ editing ? 'Edit Coupon' : 'Add Coupon' }}</h2>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 text-xl">×</button>
                </div>
                <form @submit.prevent="save" class="space-y-3">
                    <div v-if="!editing">
                        <label class="text-xs text-gray-600 block mb-1">Code *</label>
                        <input v-model="form.code" required class="w-full border rounded-lg px-3 py-2 text-sm uppercase" placeholder="e.g. SAVE10" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-600 block mb-1">Description</label>
                        <input v-model="form.description" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="10% off for new customers" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-600 block mb-1">Type *</label>
                            <select v-model="form.type" class="w-full border rounded-lg px-3 py-2 text-sm">
                                <option value="percent">Percent (%)</option>
                                <option value="fixed">Fixed (Rs.)</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-600 block mb-1">Value *</label>
                            <input v-model="form.value" type="number" required min="1" class="w-full border rounded-lg px-3 py-2 text-sm" :placeholder="form.type === 'percent' ? '10' : '500'" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-600 block mb-1">Min Order (Rs.)</label>
                            <input v-model="form.min_order_amount" type="number" min="0" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="0" />
                        </div>
                        <div>
                            <label class="text-xs text-gray-600 block mb-1">Usage Limit</label>
                            <input v-model="form.usage_limit" type="number" min="1" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Unlimited" />
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600 block mb-1">Expiry Date</label>
                        <input v-model="form.expires_at" type="date" class="w-full border rounded-lg px-3 py-2 text-sm" />
                    </div>
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" v-model="form.is_active" /> Active
                    </label>
                    <div v-if="formError" class="text-red-500 text-xs bg-red-50 rounded p-2">{{ formError }}</div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" :disabled="saving" class="flex-1 bg-orange-500 text-white py-2.5 rounded-xl text-sm font-medium disabled:opacity-60">
                            {{ saving ? 'Saving...' : 'Save' }}
                        </button>
                        <button type="button" @click="showModal = false" class="flex-1 border text-gray-600 py-2.5 rounded-xl text-sm">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { formatPrice, formatDate } from '@/composables/useFormatters'
import api from '@/api'

const toast     = useToast()
const coupons   = ref([])
const loading   = ref(true)
const showModal = ref(false)
const editing   = ref(null)
const saving    = ref(false)
const formError = ref('')

const defaultForm = () => ({ code: '', description: '', type: 'percent', value: '', min_order_amount: 0, usage_limit: '', expires_at: '', is_active: true })
const form = ref(defaultForm())

async function fetchCoupons() {
    loading.value = true
    try {
        const { data } = await api.get('/admin/coupons')
        coupons.value = data
    } finally {
        loading.value = false
    }
}

function openModal(coupon = null) {
    editing.value   = coupon
    formError.value = ''
    form.value      = coupon ? { ...coupon, expires_at: coupon.expires_at ? coupon.expires_at.split('T')[0] : '' } : defaultForm()
    showModal.value = true
}

async function save() {
    saving.value    = true
    formError.value = ''
    try {
        if (editing.value) {
            await api.put(`/admin/coupons/${editing.value.id}`, form.value)
        } else {
            await api.post('/admin/coupons', form.value)
        }
        toast.success(editing.value ? 'Updated.' : 'Coupon created.')
        showModal.value = false
        fetchCoupons()
    } catch (e) {
        formError.value = e.response?.data?.message || Object.values(e.response?.data?.errors || {}).flat().join(', ') || 'Error.'
    } finally {
        saving.value = false
    }
}

async function deleteCoupon(c) {
    if (!confirm(`Delete coupon "${c.code}"?`)) return
    try {
        await api.delete(`/admin/coupons/${c.id}`)
        toast.success('Deleted.')
        fetchCoupons()
    } catch { toast.error('Failed.') }
}

onMounted(fetchCoupons)
</script>
