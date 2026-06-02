<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-bold text-gray-800">Email Leads</h1>
            <button
                @click="sendNewsletter"
                :disabled="sendingNewsletter"
                class="bg-orange-500 hover:bg-orange-600 disabled:opacity-60 text-white px-4 py-2 rounded-xl text-sm font-medium flex items-center gap-2"
            >
                <span aria-hidden="true">📧</span>
                {{ sendingNewsletter ? 'Sending...' : 'Send Newsletter Now' }}
            </button>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl p-5 shadow-sm text-center">
                <p class="text-3xl font-bold text-orange-500">{{ stats.total || 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Total Leads</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm text-center">
                <p class="text-3xl font-bold text-green-500">{{ stats.contacted || 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Contacted</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm text-center">
                <p class="text-3xl font-bold text-red-400">{{ stats.not_contacted || 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Not Contacted</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm text-center">
                <p class="text-3xl font-bold text-blue-500">{{ stats.today || 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Today</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl p-4 shadow-sm mb-4 flex gap-3 flex-wrap">
            <input v-model="search" @input="fetchLeads" type="text" placeholder="Search by email or name..."
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-64 focus:outline-none focus:border-orange-400" />
            <select v-model="contactedFilter" @change="fetchLeads" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none">
                <option value="">All</option>
                <option value="0">Not Contacted</option>
                <option value="1">Contacted</option>
            </select>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b text-gray-500 text-left">
                    <tr>
                        <th class="px-4 py-3">Name / Email</th>
                        <th class="px-4 py-3">Product Viewed</th>
                        <th class="px-4 py-3">Device</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Captured</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading">
                        <td colspan="6" class="text-center py-10 text-gray-400">Loading...</td>
                    </tr>
                    <tr v-for="lead in leads" :key="lead.id" class="border-b last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-orange-100 text-orange-600 font-bold text-sm flex items-center justify-center shrink-0">
                                    {{ lead.name?.charAt(0)?.toUpperCase() || lead.email?.charAt(0)?.toUpperCase() }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">{{ lead.name || '—' }}</p>
                                    <a :href="`mailto:${lead.email}`" class="text-blue-500 hover:underline text-xs">{{ lead.email }}</a>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div v-if="lead.product" class="flex items-center gap-2">
                                <img :src="lead.product.thumbnail" :alt="lead.product.name" @error="$event.target.style.display='none'"
                                    class="w-8 h-8 object-cover rounded bg-gray-100 shrink-0" />
                                <span class="text-xs text-gray-600 line-clamp-2">{{ lead.product.name }}</span>
                            </div>
                            <span v-else class="text-gray-400 text-xs">—</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            {{ lead.browser }} / {{ lead.device }}
                        </td>
                        <td class="px-4 py-3">
                            <span :class="lead.is_contacted ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
                                class="px-2 py-0.5 rounded-full text-xs font-medium">
                                {{ lead.is_contacted ? '✓ Contacted' : 'Pending' }}
                            </span>
                            <p v-if="lead.notes" class="text-xs text-gray-400 mt-0.5">{{ lead.notes }}</p>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">{{ formatDate(lead.created_at) }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <a :href="`mailto:${lead.email}?subject=Huzaifa Mobile - Special Offer For You`"
                                    class="text-xs bg-blue-500 text-white px-2 py-1 rounded-lg hover:bg-blue-600">
                                    Email
                                </a>
                                <button v-if="!lead.is_contacted" @click="openNotesModal(lead)"
                                    class="text-xs bg-green-500 text-white px-2 py-1 rounded-lg hover:bg-green-600">
                                    Mark Contacted
                                </button>
                                <button @click="deleteLead(lead)"
                                    class="text-xs text-red-400 hover:text-red-600">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!loading && !leads.length">
                        <td colspan="6" class="text-center py-10 text-gray-400">
                            No leads yet. They'll appear here after users submit their email on product pages.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="pagination?.last_page > 1" class="flex justify-center gap-2 p-4">
                <button v-for="p in pagination.last_page" :key="p" @click="page = p; fetchLeads()"
                    :class="['px-3 py-1 rounded text-sm', p === page ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200']">
                    {{ p }}
                </button>
            </div>
        </div>

        <!-- Mark contacted modal -->
        <div v-if="notesModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl w-full max-w-sm p-6">
                <h3 class="font-bold mb-1">Mark as Contacted</h3>
                <p class="text-sm text-gray-500 mb-4">{{ notesModal.email }}</p>
                <textarea v-model="notesText" rows="3" placeholder="Add a note (optional)..."
                    class="w-full border rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:border-orange-400"></textarea>
                <div class="flex gap-3">
                    <button @click="confirmContacted" :disabled="saving"
                        class="flex-1 bg-green-500 text-white py-2 rounded-xl text-sm font-medium hover:bg-green-600 disabled:opacity-60">
                        {{ saving ? 'Saving...' : 'Confirm' }}
                    </button>
                    <button @click="notesModal = null" class="flex-1 border text-gray-600 py-2 rounded-xl text-sm">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import api from '@/api'

const toast              = useToast()
const leads              = ref([])
const stats              = ref({})
const pagination         = ref(null)
const loading            = ref(true)
const saving             = ref(false)
const sendingNewsletter  = ref(false)
const search             = ref('')
const contactedFilter    = ref('')
const page               = ref(1)
const notesModal         = ref(null)
const notesText          = ref('')

function formatDate(d) {
    return new Date(d).toLocaleString('en-PK', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

async function fetchLeads() {
    loading.value = true
    try {
        const params = { search: search.value, page: page.value }
        if (contactedFilter.value !== '') params.contacted = contactedFilter.value
        const { data } = await api.get('/admin/leads', { params })
        leads.value      = data.data
        pagination.value = data
    } finally {
        loading.value = false
    }
}

async function fetchStats() {
    const { data } = await api.get('/admin/leads/stats')
    stats.value = data
}

function openNotesModal(lead) {
    notesModal.value = lead
    notesText.value  = ''
}

async function confirmContacted() {
    saving.value = true
    try {
        await api.put(`/admin/leads/${notesModal.value.id}/contacted`, { notes: notesText.value })
        toast.success('Marked as contacted.')
        notesModal.value = null
        fetchLeads()
        fetchStats()
    } catch {
        toast.error('Failed.')
    } finally {
        saving.value = false
    }
}

async function sendNewsletter() {
    if (!confirm('Send the weekly newsletter to all leads now?')) return
    sendingNewsletter.value = true
    try {
        const { data } = await api.post('/admin/newsletter/send')
        toast.success(data.message || 'Newsletter queued successfully.')
    } catch {
        toast.error('Failed to send newsletter.')
    } finally {
        sendingNewsletter.value = false
    }
}

async function deleteLead(lead) {
    if (!confirm(`Delete lead for ${lead.email}?`)) return
    try {
        await api.delete(`/admin/leads/${lead.id}`)
        toast.success('Deleted.')
        fetchLeads()
        fetchStats()
    } catch {
        toast.error('Failed.')
    }
}

onMounted(() => {
    fetchLeads()
    fetchStats()
})
</script>
