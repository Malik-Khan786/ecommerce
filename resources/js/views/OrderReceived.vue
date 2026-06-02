<template>
    <div class="min-h-[80vh] flex items-center justify-center px-4">
        <div class="bg-white rounded-2xl shadow-lg w-full max-w-md p-8">

            <!-- Loading -->
            <div v-if="loading" class="text-center text-gray-400 py-8">
                <svg class="w-8 h-8 animate-spin mx-auto mb-3 text-orange-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                Loading your order...
            </div>

            <!-- Invalid token -->
            <div v-else-if="!order" class="text-center py-8">
                <p class="text-5xl mb-4">❌</p>
                <h2 class="text-xl font-bold text-gray-800 mb-2">Invalid Link</h2>
                <p class="text-gray-500 text-sm">This acknowledgement link is invalid or has already been used.</p>
                <RouterLink to="/" class="inline-block mt-5 bg-orange-500 text-white px-6 py-2 rounded-xl hover:bg-orange-600 text-sm">Go to Homepage</RouterLink>
            </div>

            <!-- Already acknowledged -->
            <div v-else-if="order.ack_status" class="text-center py-6">
                <p class="text-5xl mb-4">{{ order.ack_status === 'received' ? '✅' : '⚠️' }}</p>
                <h2 class="text-xl font-bold text-gray-800 mb-2">
                    {{ order.ack_status === 'received' ? 'Already Confirmed' : 'Issue Already Reported' }}
                </h2>
                <p class="text-gray-500 text-sm mb-1">Order: <strong>{{ order.order_number }}</strong></p>
                <p class="text-gray-400 text-sm">You have already submitted your feedback for this order.</p>
                <RouterLink to="/" class="inline-block mt-5 bg-orange-500 text-white px-6 py-2 rounded-xl hover:bg-orange-600 text-sm">Continue Shopping</RouterLink>
            </div>

            <!-- Submission form -->
            <div v-else-if="!submitted">
                <!-- Order summary -->
                <div class="text-center mb-6">
                    <p class="text-4xl mb-3">📦</p>
                    <h2 class="text-xl font-bold text-gray-800">Order Received?</h2>
                    <p class="text-gray-500 text-sm mt-1">Order <strong>{{ order.order_number }}</strong></p>
                </div>

                <div class="bg-gray-50 rounded-xl p-4 mb-6">
                    <p class="text-xs text-gray-500 mb-2 font-medium uppercase tracking-wider">Your Items</p>
                    <div v-for="item in order.items" :key="item.name" class="flex justify-between text-sm py-1">
                        <span class="text-gray-700">{{ item.name }} <span class="text-gray-400">×{{ item.quantity }}</span></span>
                        <span class="font-medium">Rs. {{ formatPrice(item.subtotal) }}</span>
                    </div>
                    <div class="border-t mt-2 pt-2 flex justify-between font-bold text-sm">
                        <span>Total</span>
                        <span class="text-orange-500">Rs. {{ formatPrice(order.total) }}</span>
                    </div>
                </div>

                <!-- Pre-select from email button click -->
                <div class="space-y-3">
                    <!-- Received option -->
                    <button
                        @click="selectStatus('received')"
                        :class="['w-full flex items-center gap-3 p-4 rounded-xl border-2 transition-all text-left', selectedStatus === 'received' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-green-300']">
                        <span class="text-2xl">✅</span>
                        <div>
                            <p class="font-semibold text-gray-800">Yes, I received my order</p>
                            <p class="text-xs text-gray-500">Everything arrived safely</p>
                        </div>
                    </button>

                    <!-- Issue option -->
                    <button
                        @click="selectStatus('issue')"
                        :class="['w-full flex items-center gap-3 p-4 rounded-xl border-2 transition-all text-left', selectedStatus === 'issue' ? 'border-red-400 bg-red-50' : 'border-gray-200 hover:border-red-300']">
                        <span class="text-2xl">⚠️</span>
                        <div>
                            <p class="font-semibold text-gray-800">I have an issue</p>
                            <p class="text-xs text-gray-500">Missing item, damaged, or wrong product</p>
                        </div>
                    </button>
                </div>

                <!-- Issue message box -->
                <div v-if="selectedStatus === 'issue'" class="mt-4">
                    <label class="text-sm font-medium text-gray-700 block mb-1">Describe the issue *</label>
                    <textarea v-model="message" rows="3" required
                        placeholder="e.g. Item was damaged, wrong size received..."
                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400"></textarea>
                </div>

                <div v-if="error" class="mt-3 text-red-500 text-sm bg-red-50 rounded-lg px-3 py-2">{{ error }}</div>

                <button
                    v-if="selectedStatus"
                    @click="submit"
                    :disabled="saving || (selectedStatus === 'issue' && !message.trim())"
                    class="w-full mt-5 py-3 rounded-xl font-semibold text-white transition-colors disabled:opacity-50"
                    :class="selectedStatus === 'received' ? 'bg-green-500 hover:bg-green-600' : 'bg-orange-500 hover:bg-orange-600'">
                    {{ saving ? 'Submitting...' : selectedStatus === 'received' ? 'Confirm Receipt' : 'Submit Issue' }}
                </button>
            </div>

            <!-- Success state -->
            <div v-else class="text-center py-6">
                <p class="text-5xl mb-4">{{ selectedStatus === 'received' ? '🎉' : '📬' }}</p>
                <h2 class="text-xl font-bold text-gray-800 mb-2">
                    {{ selectedStatus === 'received' ? 'Thank You!' : 'Issue Reported' }}
                </h2>
                <p class="text-gray-500 text-sm mb-1" v-if="selectedStatus === 'received'">
                    We're glad your order arrived safely. Hope to see you again!
                </p>
                <p class="text-gray-500 text-sm mb-1" v-else>
                    We've received your report and will contact you on <strong>{{ order.shipping_phone }}</strong> shortly.
                </p>
                <RouterLink to="/" class="inline-block mt-6 bg-orange-500 text-white px-6 py-2.5 rounded-xl hover:bg-orange-600 text-sm font-medium">
                    Continue Shopping
                </RouterLink>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/api'

const route          = useRoute()
const order          = ref(null)
const loading        = ref(true)
const submitted      = ref(false)
const saving         = ref(false)
const error          = ref('')
const selectedStatus = ref('')
const message        = ref('')

function formatPrice(val) {
    return Number(val || 0).toLocaleString('en-PK')
}

function selectStatus(status) {
    selectedStatus.value = status
    error.value = ''
}

async function submit() {
    if (selectedStatus.value === 'issue' && !message.value.trim()) return
    saving.value = true
    error.value  = ''
    try {
        await api.post(`/ack/${route.params.token}`, {
            ack_status:  selectedStatus.value,
            ack_message: message.value || null,
        })
        submitted.value = true
    } catch (e) {
        error.value = e.response?.data?.message || 'Something went wrong. Please try again.'
    } finally {
        saving.value = false
    }
}

onMounted(async () => {
    try {
        const { data } = await api.get(`/ack/${route.params.token}`)
        order.value = data
        // Pre-select status if coming from email button click
        const preStatus = route.query.status
        if (preStatus && ['received', 'issue'].includes(preStatus)) {
            selectedStatus.value = preStatus
        }
    } catch {
        order.value = null
    } finally {
        loading.value = false
    }
})
</script>
