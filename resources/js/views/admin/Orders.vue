<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-bold text-gray-800">Orders</h1>
            <select v-model="statusFilter" @change="fetchOrders" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none">
                <option value="">All Statuses</option>
                <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
            </select>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Order #</th>
                        <th class="px-4 py-3 text-left">Customer</th>
                        <th class="px-4 py-3 text-left">Items</th>
                        <th class="px-4 py-3 text-left">Total</th>
                        <th class="px-4 py-3 text-left">Payment</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Receipt</th>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading">
                        <td colspan="9" class="text-center py-8 text-gray-400">Loading...</td>
                    </tr>
                    <tr v-for="order in orders" :key="order.id" class="border-b last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ order.order_number }}</td>
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ order.shipping_name }}</p>
                            <p class="text-xs text-gray-400">{{ order.shipping_phone }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ order.items?.length }}</td>
                        <td class="px-4 py-3 font-bold">Rs. {{ formatPrice(order.total) }}</td>
                        <td class="px-4 py-3 uppercase text-xs">{{ order.payment_method }}</td>
                        <td class="px-4 py-3">
                            <select
                                :value="order.status"
                                @change="updateStatus(order, $event.target.value)"
                                :class="statusClass(order.status)"
                                class="text-xs px-2 py-1 rounded-lg border-0 font-medium cursor-pointer"
                            >
                                <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                            </select>
                        </td>
                        <td class="px-4 py-3">
                            <span v-if="order.ack_status === 'received'" class="flex items-center gap-1 text-xs text-green-600 font-medium">
                                ✅ Confirmed
                            </span>
                            <span v-else-if="order.ack_status === 'issue'" class="flex items-center gap-1 text-xs text-red-500 font-medium">
                                ⚠️ Has Issue
                            </span>
                            <span v-else-if="order.status === 'delivered'" class="text-xs text-yellow-600">⏳ Awaiting</span>
                            <span v-else class="text-xs text-gray-300">—</span>
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ formatDate(order.created_at) }}</td>
                        <td class="px-4 py-3">
                            <button @click="viewOrder(order)" class="text-orange-500 hover:text-orange-700 text-xs">View</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination?.last_page > 1" class="flex justify-center gap-2 mt-4">
            <button v-for="p in pagination.last_page" :key="p" @click="page = p; fetchOrders()"
                :class="['px-3 py-1 rounded text-sm', p === page ? 'bg-orange-500 text-white' : 'bg-white border text-gray-600']">{{ p }}</button>
        </div>

        <!-- WhatsApp notification prompt -->
        <Teleport to="body">
            <Transition name="slide-up">
                <div v-if="whatsappPrompt" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[9999] bg-white rounded-2xl shadow-2xl border border-gray-100 p-4 w-full max-w-sm mx-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 fill-green-600" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-800 text-sm">Notify Customer on WhatsApp?</p>
                            <p class="text-xs text-gray-500 mt-0.5 truncate">{{ whatsappPrompt.order.order_number }} → {{ whatsappPrompt.statusLabel }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ whatsappPrompt.order.shipping_phone }}</p>
                        </div>
                        <button @click="whatsappPrompt = null" class="text-gray-300 hover:text-gray-500 text-lg leading-none shrink-0">×</button>
                    </div>
                    <div class="flex gap-2 mt-3">
                        <button @click="confirmWhatsApp" class="flex-1 bg-green-500 hover:bg-green-600 text-white text-sm py-2 rounded-xl font-medium flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            Send on WhatsApp
                        </button>
                        <button @click="whatsappPrompt = null" class="px-4 text-sm text-gray-500 border rounded-xl hover:bg-gray-50">Skip</button>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Order Detail Modal -->
        <div v-if="selectedOrder" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6">
                <div class="flex justify-between mb-4">
                    <h2 class="font-bold text-lg">{{ selectedOrder.order_number }}</h2>
                    <button @click="selectedOrder = null" class="text-gray-400 hover:text-gray-600 text-xl">×</button>
                </div>
                <div class="space-y-2 text-sm mb-4">
                    <p><span class="text-gray-500">Customer:</span> {{ selectedOrder.shipping_name }}</p>
                    <p><span class="text-gray-500">Phone:</span> {{ selectedOrder.shipping_phone }}</p>
                    <p><span class="text-gray-500">Address:</span> {{ selectedOrder.shipping_address }}, {{ selectedOrder.shipping_city }}</p>
                    <p><span class="text-gray-500">Payment:</span> {{ selectedOrder.payment_method.toUpperCase() }}</p>
                </div>

                <!-- Acknowledgement status -->
                <div v-if="selectedOrder.ack_status" class="mb-4 rounded-xl p-3 text-sm"
                    :class="selectedOrder.ack_status === 'received' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                    <p class="font-semibold mb-1" :class="selectedOrder.ack_status === 'received' ? 'text-green-700' : 'text-red-600'">
                        {{ selectedOrder.ack_status === 'received' ? '✅ Customer confirmed receipt' : '⚠️ Customer reported an issue' }}
                    </p>
                    <p v-if="selectedOrder.ack_message" class="text-gray-600 text-xs">{{ selectedOrder.ack_message }}</p>
                    <p v-if="selectedOrder.ack_at" class="text-gray-400 text-xs mt-1">{{ formatDate(selectedOrder.ack_at) }}</p>
                </div>
                <div v-else-if="selectedOrder.status === 'delivered'" class="mb-4 bg-yellow-50 border border-yellow-200 rounded-xl p-3 text-sm text-yellow-700">
                    ⏳ Awaiting customer acknowledgement
                </div>
                <div class="border-t pt-4">
                    <p class="font-medium mb-2">Items:</p>
                    <div v-for="item in selectedOrder.items" :key="item.id" class="flex justify-between text-sm py-1">
                        <span>{{ item.product_name }} × {{ item.quantity }}</span>
                        <span>Rs. {{ formatPrice(item.subtotal) }}</span>
                    </div>
                    <div class="border-t mt-2 pt-2 flex justify-between font-bold">
                        <span>Total</span>
                        <span class="text-orange-500">Rs. {{ formatPrice(selectedOrder.total) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import api from '@/api'

const toast           = useToast()
const orders          = ref([])
const pagination      = ref(null)
const loading         = ref(true)
const page            = ref(1)
const statusFilter    = ref('')
const selectedOrder   = ref(null)
const whatsappPrompt  = ref(null)

const statuses = [
    { value: 'pending',    label: 'Pending' },
    { value: 'confirmed',  label: 'Confirmed' },
    { value: 'processing', label: 'Processing' },
    { value: 'shipped',    label: 'Shipped' },
    { value: 'delivered',  label: 'Delivered' },
    { value: 'cancelled',  label: 'Cancelled' },
]

function formatPrice(val) { return Number(val || 0).toLocaleString('en-PK') }
function formatDate(d) { return new Date(d).toLocaleDateString('en-PK', { month: 'short', day: 'numeric', year: 'numeric' }) }

function statusClass(s) {
    const map = { pending: 'bg-yellow-100 text-yellow-700', confirmed: 'bg-blue-100 text-blue-700', processing: 'bg-indigo-100 text-indigo-700', shipped: 'bg-purple-100 text-purple-700', delivered: 'bg-green-100 text-green-700', cancelled: 'bg-red-100 text-red-700' }
    return map[s] || 'bg-gray-100 text-gray-600'
}

async function fetchOrders() {
    loading.value = true
    try {
        const { data } = await api.get('/admin/orders', { params: { status: statusFilter.value || undefined, page: page.value } })
        orders.value    = data.data
        pagination.value = data
    } finally {
        loading.value = false
    }
}

const statusMessages = {
    confirmed:  '✅ Your order has been confirmed and will be prepared shortly.',
    processing: '🔧 Your order is being packed carefully.',
    shipped:    '🚚 Great news! Your order is on the way. Our delivery partner will contact you soon.',
    delivered:  '🎉 Your order has been delivered! We hope you love your purchase.',
    cancelled:  '❌ Your order has been cancelled. Contact us if you have any questions.',
}

function buildWhatsAppUrl(order, status) {
    const phone = order.shipping_phone?.replace(/\D/g, '')
    if (!phone) return null
    const intlPhone   = phone.startsWith('0') ? '92' + phone.slice(1) : phone
    const statusLabel = statuses.find(s => s.value === status)?.label || status
    const msg = [
        `📦 *Order Update — ${order.order_number}*`,
        ``,
        `Hi ${order.shipping_name},`,
        `Your order status has been updated to: *${statusLabel}*`,
        ``,
        statusMessages[status] || '',
        ``,
        `💰 Order Total: Rs. ${formatPrice(order.total)}`,
        ``,
        `Thank you for shopping with Huzaifa Mobile! 🛍️`,
        `For any queries call: 0309-0046009`,
    ].join('\n')
    return `https://wa.me/${intlPhone}?text=${encodeURIComponent(msg)}`
}

function confirmWhatsApp() {
    if (!whatsappPrompt.value) return
    const { order, status } = whatsappPrompt.value
    const url = buildWhatsAppUrl(order, status)
    if (url) window.open(url, '_blank')
    whatsappPrompt.value = null
}

async function updateStatus(order, status) {
    try {
        await api.put(`/admin/orders/${order.uuid}/status`, { status })
        order.status = status
        toast.success('Status updated.')

        // Show WhatsApp prompt if customer has a phone number
        if (order.shipping_phone) {
            whatsappPrompt.value = {
                order,
                status,
                statusLabel: statuses.find(s => s.value === status)?.label || status,
            }
            // Auto-dismiss after 15 seconds if admin ignores it
            setTimeout(() => {
                if (whatsappPrompt.value?.order?.uuid === order.uuid) {
                    whatsappPrompt.value = null
                }
            }, 15000)
        }
    } catch {
        toast.error('Failed.')
    }
}

function viewOrder(order) {
    selectedOrder.value = order
}

onMounted(fetchOrders)
</script>

<style scoped>
.slide-up-enter-active, .slide-up-leave-active { transition: all 0.3s ease; }
.slide-up-enter-from, .slide-up-leave-to       { opacity: 0; transform: translate(-50%, 20px); }
</style>
