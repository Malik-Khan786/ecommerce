<template>
    <div class="bg-teal-50 border border-teal-200 rounded-xl p-4 mt-3">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-8 h-8 bg-teal-600 rounded-lg flex items-center justify-center">
                <span class="text-white text-xs font-bold">EP</span>
            </div>
            <p class="font-semibold text-teal-800 text-sm">EasyPaisa</p>
        </div>

        <div v-if="!redirecting">
            <p class="text-sm text-gray-600 mb-3">
                You will be redirected to the EasyPaisa payment page to complete your payment of
                <strong class="text-teal-700">Rs. {{ formatPrice(amount) }}</strong>.
            </p>

            <div class="bg-teal-100 rounded-lg p-3 text-xs text-teal-700 space-y-1">
                <p>✅ Secure payment powered by EasyPaisa</p>
                <p>✅ You will return to this site after payment</p>
                <p>✅ Your order will be confirmed automatically</p>
            </div>

            <div v-if="error" class="mt-2 text-xs text-red-500 bg-red-50 rounded-lg px-3 py-2">{{ error }}</div>
        </div>

        <div v-else class="text-center py-4">
            <svg class="w-8 h-8 animate-spin text-teal-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            <p class="text-sm text-teal-700">Redirecting to EasyPaisa...</p>
        </div>

        <!-- Hidden form that gets submitted to EasyPaisa -->
        <form ref="epForm" method="POST" :action="epData.endpoint" style="display:none">
            <input type="hidden" name="storeId"        :value="epData.storeId" />
            <input type="hidden" name="amount"         :value="epData.amount" />
            <input type="hidden" name="postBackURL"    :value="epData.returnUrl" />
            <input type="hidden" name="orderRefNumber" :value="epData.orderRefNumber" />
            <input type="hidden" name="txnDateTime"    :value="epData.txnDateTime" />
            <input type="hidden" name="expiryDateTime" :value="epData.expiryDateTime" />
            <input type="hidden" name="autoRedirect"   value="1" />
            <input type="hidden" name="hash"           :value="epData.hash" />
        </form>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { formatPrice } from '@/composables/useFormatters'

const props = defineProps({
    orderUuid: { type: String, required: true },
    amount:    { type: Number, required: true },
})

const emit = defineEmits(['payment-error'])

const redirecting = ref(false)
const error       = ref('')
const epForm      = ref(null)
const epData      = reactive({
    endpoint: '', storeId: '', amount: '', returnUrl: '',
    orderRefNumber: '', txnDateTime: '', expiryDateTime: '', hash: '',
})

async function pay(api) {
    error.value = ''
    redirecting.value = true

    try {
        const { data } = await api.post('/payments/easypaisa', {
            order_uuid: props.orderUuid,
        })

        // Populate hidden form and submit to EasyPaisa
        Object.assign(epData, data)

        // Wait for Vue to render form with values
        await new Promise(r => setTimeout(r, 100))
        epForm.value?.submit()
        return true
    } catch (e) {
        redirecting.value = false
        error.value = e.response?.data?.message || 'Could not connect to EasyPaisa. Please try again.'
        emit('payment-error', { message: error.value })
        return false
    }
}

defineExpose({ pay })
</script>
