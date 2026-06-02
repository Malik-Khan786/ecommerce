<template>
    <div class="bg-white border border-gray-200 rounded-xl p-5 mt-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800 text-sm">Card Payment</h3>
            <span class="text-xs text-gray-400 flex items-center gap-1">
                🔒 Powered by Stripe
            </span>
        </div>

        <!-- TODO: Initialize Stripe.js with your publishable key -->
        <!-- import { loadStripe } from '@stripe/stripe-js' -->
        <!-- const stripe = await loadStripe(import.meta.env.VITE_STRIPE_KEY) -->

        <div class="space-y-3">
            <div>
                <label for="stripe-card-number" class="text-xs text-gray-500 block mb-1">Card Number</label>
                <input
                    id="stripe-card-number"
                    v-model="cardNumber"
                    type="text"
                    inputmode="numeric"
                    maxlength="19"
                    placeholder="1234 5678 9012 3456"
                    @input="formatCardNumber"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-orange-400 font-mono tracking-wider"
                />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="stripe-expiry" class="text-xs text-gray-500 block mb-1">Expiry Date</label>
                    <input
                        id="stripe-expiry"
                        v-model="expiry"
                        type="text"
                        inputmode="numeric"
                        maxlength="5"
                        placeholder="MM/YY"
                        @input="formatExpiry"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-orange-400 font-mono"
                    />
                </div>
                <div>
                    <label for="stripe-cvv" class="text-xs text-gray-500 block mb-1">CVV</label>
                    <input
                        id="stripe-cvv"
                        v-model="cvv"
                        type="password"
                        inputmode="numeric"
                        maxlength="4"
                        placeholder="•••"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-orange-400 font-mono"
                    />
                </div>
            </div>
        </div>

        <div v-if="errorMsg" class="mt-3 text-xs text-red-600 bg-red-50 border border-red-100 rounded-lg px-3 py-2">
            {{ errorMsg }}
        </div>

        <button
            type="button"
            @click="handlePay"
            :disabled="processing || !isFormFilled"
            class="mt-4 w-full bg-orange-500 hover:bg-orange-600 disabled:opacity-60 text-white py-3 rounded-xl font-semibold text-sm transition-colors"
        >
            {{ processing ? 'Processing...' : `Pay Rs. ${formattedAmount}` }}
        </button>

        <p class="text-center text-xs text-gray-400 mt-3">
            Your payment is encrypted and secure. We never store card details.
        </p>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { formatPrice } from '@/composables/useFormatters'

const props = defineProps({
    amount: { type: Number, required: true },
    orderUuid: { type: String, default: null },
})

const emit = defineEmits(['payment-success', 'payment-error'])

const cardNumber = ref('')
const expiry     = ref('')
const cvv        = ref('')
const processing = ref(false)
const errorMsg   = ref('')

const formattedAmount = computed(() => formatPrice(props.amount))

const isFormFilled = computed(() =>
    cardNumber.value.replace(/\s/g, '').length >= 13 &&
    expiry.value.length === 5 &&
    cvv.value.length >= 3
)

function formatCardNumber(e) {
    let v = e.target.value.replace(/\D/g, '').substring(0, 16)
    cardNumber.value = v.replace(/(.{4})/g, '$1 ').trim()
}

function formatExpiry(e) {
    let v = e.target.value.replace(/\D/g, '').substring(0, 4)
    if (v.length >= 3) v = v.substring(0, 2) + '/' + v.substring(2)
    expiry.value = v
}

async function handlePay() {
    errorMsg.value  = ''
    processing.value = true

    try {
        // TODO: Replace this placeholder with real Stripe.js integration:
        //
        // 1. Call your backend to create a PaymentIntent:
        //    const { data } = await api.post('/payments/create-intent', { order_uuid: props.orderUuid })
        //    const { client_secret } = data
        //
        // 2. Confirm the card payment with Stripe.js:
        //    const stripe = await loadStripe(import.meta.env.VITE_STRIPE_KEY)
        //    const { error, paymentIntent } = await stripe.confirmCardPayment(client_secret, {
        //      payment_method: {
        //        card: cardElement,   // use stripe.elements() card element
        //        billing_details: { name: billingName }
        //      }
        //    })
        //    if (error) throw new Error(error.message)
        //    emit('payment-success', paymentIntent)

        // Placeholder — simulate a short delay then emit success for UI testing
        await new Promise(resolve => setTimeout(resolve, 1200))
        emit('payment-success', { status: 'placeholder', amount: props.amount })
    } catch (e) {
        errorMsg.value = e.message || 'Payment failed. Please try again.'
        emit('payment-error', e)
    } finally {
        processing.value = false
    }
}
</script>
