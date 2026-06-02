<template>
    <div class="max-w-lg mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <RouterLink to="/orders" class="text-gray-400 hover:text-gray-600">← My Orders</RouterLink>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h1 class="text-xl font-bold text-gray-800 mb-1">Request a Return</h1>
            <p class="text-gray-500 text-sm mb-6">Order: <strong>{{ orderUuid }}</strong></p>

            <div v-if="submitted" class="text-center py-8">
                <p class="text-5xl mb-4">📬</p>
                <h2 class="text-lg font-bold text-gray-800 mb-2">Return Request Submitted</h2>
                <p class="text-gray-500 text-sm">We'll review your request and contact you within 24-48 hours.</p>
                <RouterLink to="/orders" class="inline-block mt-5 bg-orange-500 text-white px-6 py-2.5 rounded-xl text-sm font-medium hover:bg-orange-600">Back to Orders</RouterLink>
            </div>

            <form v-else @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-2">Reason *</label>
                    <div class="space-y-2">
                        <label v-for="opt in reasons" :key="opt.value"
                            :class="['flex items-start gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all', form.reason === opt.value ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-gray-300']">
                            <input type="radio" v-model="form.reason" :value="opt.value" class="mt-0.5 hidden" />
                            <div>
                                <p class="font-medium text-sm text-gray-800">{{ opt.label }}</p>
                                <p class="text-xs text-gray-500">{{ opt.desc }}</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1">Additional Details</label>
                    <textarea v-model="form.description" rows="3" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" placeholder="Describe the issue in detail..."></textarea>
                </div>

                <div v-if="error" class="bg-red-50 border border-red-200 rounded-xl p-3 text-red-600 text-sm">{{ error }}</div>

                <button type="submit" :disabled="!form.reason || saving"
                    class="w-full bg-orange-500 hover:bg-orange-600 disabled:opacity-50 text-white py-3 rounded-xl font-semibold text-sm">
                    {{ saving ? 'Submitting...' : 'Submit Return Request' }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/api'

const route     = useRoute()
const orderUuid = route.params.uuid
const saving    = ref(false)
const submitted = ref(false)
const error     = ref('')
const form      = ref({ reason: '', description: '' })

const reasons = [
    { value: 'damaged',      label: '📦 Item Damaged',        desc: 'Product arrived broken or damaged' },
    { value: 'wrong_item',   label: '❌ Wrong Item',           desc: 'Received a different product than ordered' },
    { value: 'not_received', label: '🚫 Not Received',         desc: 'Order marked delivered but not received' },
    { value: 'changed_mind', label: '💭 Changed My Mind',      desc: 'No longer need the product' },
    { value: 'other',        label: '❓ Other',                desc: 'Other reason not listed above' },
]

async function submit() {
    saving.value = true
    error.value  = ''
    try {
        await api.post(`/orders/${orderUuid}/returns`, form.value)
        submitted.value = true
    } catch (e) {
        error.value = e.response?.data?.message || 'Failed to submit. Please try again.'
    } finally {
        saving.value = false
    }
}
</script>
