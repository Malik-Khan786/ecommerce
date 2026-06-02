<template>
    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mt-3">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center">
                <span class="text-white text-xs font-bold">JC</span>
            </div>
            <p class="font-semibold text-green-800 text-sm">JazzCash Mobile Wallet</p>
        </div>

        <div v-if="!paid">
            <label class="text-sm text-gray-600 block mb-1">JazzCash Mobile Number *</label>
            <div class="flex gap-2">
                <input
                    v-model="mobileNumber"
                    type="tel"
                    placeholder="e.g. 03001234567"
                    maxlength="11"
                    class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-green-400"
                />
            </div>
            <p class="text-xs text-gray-400 mt-1">Enter the mobile number linked to your JazzCash account</p>

            <div v-if="error" class="mt-2 text-xs text-red-500 bg-red-50 rounded-lg px-3 py-2">{{ error }}</div>

            <p class="text-xs text-green-700 mt-2 font-medium">
                💡 You will receive an OTP on this number to confirm payment of
                <strong>Rs. {{ formatPrice(amount) }}</strong>
            </p>
        </div>

        <div v-else class="text-center py-2">
            <p class="text-green-700 font-semibold text-sm">✅ JazzCash Payment Successful!</p>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { formatPrice } from '@/composables/useFormatters'

const props = defineProps({
    orderUuid: { type: String, required: true },
    amount:    { type: Number, required: true },
})

const emit = defineEmits(['payment-success', 'payment-error'])

const mobileNumber = ref('')
const error        = ref('')
const paid         = ref(false)

// Exposed so parent can call pay() on form submit
async function pay(api) {
    error.value = ''

    if (!mobileNumber.value || !/^03[0-9]{9}$/.test(mobileNumber.value)) {
        error.value = 'Please enter a valid JazzCash number (e.g. 03001234567)'
        return false
    }

    try {
        const { data } = await api.post('/payments/jazzcash', {
            order_uuid:    props.orderUuid,
            mobile_number: mobileNumber.value,
        })

        if (data.status === 'success') {
            paid.value = true
            emit('payment-success', data)
            return true
        }

        error.value = data.message || 'Payment failed. Please try again.'
        emit('payment-error', data)
        return false
    } catch (e) {
        error.value = e.response?.data?.message || 'Payment failed. Please check your number and try again.'
        emit('payment-error', { message: error.value })
        return false
    }
}

defineExpose({ pay, mobileNumber })
</script>
