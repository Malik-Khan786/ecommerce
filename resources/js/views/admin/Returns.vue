<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-bold text-gray-800">Return Requests</h1>
            <select v-model="statusFilter" @change="fetchReturns" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="completed">Completed</option>
            </select>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b text-gray-500 text-left">
                    <tr>
                        <th class="px-4 py-3">Order</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Reason</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading"><td colspan="6" class="text-center py-8 text-gray-400">Loading...</td></tr>
                    <tr v-for="r in returns" :key="r.id" class="border-b last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ r.order?.order_number }}</td>
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ r.user?.name }}</p>
                            <p class="text-xs text-gray-400">{{ r.user?.email }}</p>
                        </td>
                        <td class="px-4 py-3 capitalize text-gray-600">{{ r.reason?.replace('_', ' ') }}</td>
                        <td class="px-4 py-3">
                            <span :class="returnStatusClass(r.status)" class="px-2 py-0.5 rounded-full text-xs font-medium">
                                {{ r.status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ formatDate(r.created_at) }}</td>
                        <td class="px-4 py-3">
                            <button @click="openModal(r)" class="text-orange-500 hover:text-orange-700 text-xs">Review</button>
                        </td>
                    </tr>
                    <tr v-if="!loading && !returns.length">
                        <td colspan="6" class="text-center py-10 text-gray-400">No return requests.</td>
                    </tr>
                </tbody>
            </table>
            <div v-if="pagination?.last_page > 1" class="p-4 flex justify-center">
                <Pagination :current-page="pagination.current_page" :last-page="pagination.last_page" @change="p => { page = p; fetchReturns() }" />
            </div>
        </div>

        <!-- Review modal -->
        <div v-if="selected" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between mb-4">
                    <h2 class="font-bold text-lg">Return Request</h2>
                    <button @click="selected = null" class="text-gray-400 hover:text-gray-600 text-xl">×</button>
                </div>

                <div class="space-y-2 text-sm mb-4 bg-gray-50 rounded-xl p-4">
                    <p><span class="text-gray-500">Order:</span> <strong>{{ selected.order?.order_number }}</strong></p>
                    <p><span class="text-gray-500">Customer:</span> {{ selected.user?.name }} ({{ selected.user?.email }})</p>
                    <p><span class="text-gray-500">Reason:</span> {{ selected.reason?.replace('_', ' ') }}</p>
                    <p v-if="selected.description"><span class="text-gray-500">Details:</span> {{ selected.description }}</p>
                </div>

                <div v-if="selected.status === 'pending'" class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-700 block mb-1">Decision *</label>
                        <div class="flex gap-3">
                            <button @click="decision = 'approved'" :class="['flex-1 py-2.5 rounded-xl text-sm font-medium border-2 transition-all', decision === 'approved' ? 'border-green-500 bg-green-50 text-green-700' : 'border-gray-200 text-gray-600']">
                                ✅ Approve
                            </button>
                            <button @click="decision = 'rejected'" :class="['flex-1 py-2.5 rounded-xl text-sm font-medium border-2 transition-all', decision === 'rejected' ? 'border-red-400 bg-red-50 text-red-600' : 'border-gray-200 text-gray-600']">
                                ❌ Reject
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 block mb-1">Admin Notes</label>
                        <textarea v-model="adminNotes" rows="3" class="w-full border rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-orange-400" placeholder="Message to customer..."></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button @click="submitDecision" :disabled="!decision || saving" class="flex-1 bg-orange-500 text-white py-2.5 rounded-xl text-sm font-medium disabled:opacity-60">
                            {{ saving ? 'Saving...' : 'Submit Decision' }}
                        </button>
                        <button @click="selected = null" class="flex-1 border text-gray-600 py-2.5 rounded-xl text-sm">Cancel</button>
                    </div>
                </div>

                <div v-else class="bg-gray-50 rounded-xl p-4 text-sm">
                    <p><span class="text-gray-500">Status:</span> <strong class="capitalize">{{ selected.status }}</strong></p>
                    <p v-if="selected.admin_notes" class="mt-2"><span class="text-gray-500">Notes:</span> {{ selected.admin_notes }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { formatDate } from '@/composables/useFormatters'
import Pagination from '@/components/ui/Pagination.vue'
import api from '@/api'

const toast        = useToast()
const returns      = ref([])
const pagination   = ref(null)
const loading      = ref(true)
const statusFilter = ref('')
const page         = ref(1)
const selected     = ref(null)
const decision     = ref('')
const adminNotes   = ref('')
const saving       = ref(false)

function returnStatusClass(s) {
    return { pending: 'bg-yellow-100 text-yellow-700', approved: 'bg-green-100 text-green-700', rejected: 'bg-red-100 text-red-600', completed: 'bg-blue-100 text-blue-700' }[s] || 'bg-gray-100 text-gray-600'
}

async function fetchReturns() {
    loading.value = true
    try {
        const { data } = await api.get('/admin/returns', { params: { status: statusFilter.value || undefined, page: page.value } })
        returns.value    = data.data
        pagination.value = data
    } finally { loading.value = false }
}

function openModal(r) {
    selected.value   = r
    decision.value   = ''
    adminNotes.value = ''
}

async function submitDecision() {
    saving.value = true
    try {
        await api.put(`/admin/returns/${selected.value.id}`, { status: decision.value, admin_notes: adminNotes.value })
        toast.success('Decision submitted.')
        selected.value = null
        fetchReturns()
    } catch { toast.error('Failed.') }
    finally { saving.value = false }
}

onMounted(fetchReturns)
</script>
