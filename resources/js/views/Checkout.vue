<template>
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Checkout</h1>

        <div v-if="cart.items.length === 0" class="text-center py-20">
            <p class="text-gray-500 mb-4">Your cart is empty.</p>
            <RouterLink to="/products" class="bg-orange-500 text-white px-6 py-2 rounded-xl">Shop Now</RouterLink>
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Form -->
            <div class="bg-white rounded-xl p-6 shadow-sm">
                <h2 class="font-semibold mb-4">Shipping Information</h2>
                <form @submit.prevent="placeOrder" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="shipping-name" class="text-sm text-gray-600 block mb-1">Full Name *</label>
                            <input id="shipping-name" v-model="form.shipping_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-400" />
                        </div>
                        <div>
                            <label for="shipping-phone" class="text-sm text-gray-600 block mb-1">Phone *</label>
                            <input id="shipping-phone" v-model="form.shipping_phone" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-400" />
                        </div>
                    </div>
                    <div>
                        <label for="shipping-address" class="text-sm text-gray-600 block mb-1">Address *</label>
                        <input id="shipping-address" v-model="form.shipping_address" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label for="shipping-city" class="text-sm text-gray-600 block mb-1">City *</label>
                            <input id="shipping-city" v-model="form.shipping_city" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label for="shipping-state" class="text-sm text-gray-600 block mb-1">State</label>
                            <input id="shipping-state" v-model="form.shipping_state" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label for="shipping-postal" class="text-sm text-gray-600 block mb-1">Postal Code</label>
                            <input id="shipping-postal" v-model="form.shipping_postal_code" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
                        </div>
                    </div>

                    <div>
                        <!-- The radio group is labelled by the heading text; individual options are
                             wrapped in <label> elements that wrap their own <input>, satisfying
                             the label-control association rule without needing explicit for/id. -->
                        <p id="payment-method-label" class="text-sm text-gray-600 mb-2">Payment Method *</p>
                        <div role="radiogroup" aria-labelledby="payment-method-label" class="grid grid-cols-2 gap-2">
                            <label v-for="method in paymentMethods" :key="method.value"
                                :class="['flex items-center gap-2 border-2 rounded-lg px-3 py-2 cursor-pointer text-sm transition-colors', form.payment_method === method.value ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-gray-300']">
                                <input type="radio" v-model="form.payment_method" :value="method.value" class="hidden" />
                                <span aria-hidden="true">{{ method.icon }}</span>
                                {{ method.label }}
                            </label>
                        </div>

                        <!-- Estimated delivery -->
                        <div class="mt-3 flex items-center gap-2 bg-orange-50 border border-orange-100 rounded-lg px-3 py-2 text-sm text-orange-700">
                            <span aria-hidden="true">📅</span>
                            <span>
                                Estimated Delivery:
                                <strong>{{ estimatedDeliveryLabel }}</strong>
                                <span class="text-orange-500 ml-1 text-xs">({{ form.payment_method === 'cod' ? '5' : '3' }} business days)</span>
                            </span>
                        </div>
                    </div>

                    <!-- Stripe card form -->
                    <StripePayment
                        v-if="form.payment_method === 'card'"
                        :amount="orderTotal + (orderTotal >= 2000 ? 0 : 200)"
                        @payment-success="onStripeSuccess"
                        @payment-error="onStripeError"
                    />

                    <!-- JazzCash -->
                    <JazzCashPayment
                        v-if="form.payment_method === 'jazzcash' && pendingOrderUuid"
                        ref="jazzCashRef"
                        :order-uuid="pendingOrderUuid"
                        :amount="orderTotal + (orderTotal >= 2000 ? 0 : 200)"
                        @payment-success="onJazzCashSuccess"
                        @payment-error="onPaymentError"
                    />

                    <!-- EasyPaisa -->
                    <EasyPaisaPayment
                        v-if="form.payment_method === 'easypaisa' && pendingOrderUuid"
                        ref="easyPaisaRef"
                        :order-uuid="pendingOrderUuid"
                        :amount="orderTotal + (orderTotal >= 2000 ? 0 : 200)"
                        @payment-error="onPaymentError"
                    />

                    <!-- Coupon code -->
                    <div>
                        <label for="coupon-code" class="text-sm text-gray-600 block mb-1">Coupon Code</label>
                        <div class="flex gap-2">
                            <input id="coupon-code" v-model="couponInput" :disabled="!!appliedCoupon"
                                class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-400 disabled:bg-gray-50"
                                placeholder="Enter coupon code" />
                            <button v-if="!appliedCoupon" type="button" @click="applyCoupon" :disabled="applyingCoupon"
                                class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-700 disabled:opacity-60">
                                {{ applyingCoupon ? '...' : 'Apply' }}
                            </button>
                            <button v-else type="button" @click="removeCoupon"
                                class="bg-red-100 text-red-600 px-4 py-2 rounded-lg text-sm hover:bg-red-200">
                                Remove
                            </button>
                        </div>
                        <p v-if="couponError" class="text-red-500 text-xs mt-1">{{ couponError }}</p>
                        <p v-if="appliedCoupon" class="text-green-600 text-xs mt-1 font-medium">
                            ✓ {{ appliedCoupon.description || appliedCoupon.code }} — Rs. {{ formatPrice(appliedCoupon.discount) }} off
                        </p>
                    </div>

                    <div>
                        <label for="order-notes" class="text-sm text-gray-600 block mb-1">Order Notes</label>
                        <textarea id="order-notes" v-model="form.notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Any special instructions..."></textarea>
                    </div>

                    <div v-if="errors.length" class="bg-red-50 border border-red-200 rounded-lg p-3">
                        <p v-for="err in errors" :key="err" class="text-red-600 text-xs">{{ err }}</p>
                    </div>

                    <!-- Place order button (before payment) -->
                    <button v-if="!pendingOrderUuid" type="submit" :disabled="placing"
                        class="w-full bg-orange-500 hover:bg-orange-600 disabled:opacity-60 text-white py-3 rounded-xl font-semibold">
                        {{ placing ? 'Placing Order...' : 'Place Order' }}
                    </button>

                    <!-- Pay now button (after order created, for JazzCash/EasyPaisa) -->
                    <button v-if="pendingOrderUuid && ['jazzcash','easypaisa'].includes(form.payment_method)"
                        type="button" @click="submitPayment"
                        :class="['w-full py-3 rounded-xl font-semibold text-white',
                            form.payment_method === 'jazzcash' ? 'bg-green-600 hover:bg-green-700' : 'bg-teal-600 hover:bg-teal-700']">
                        Pay Rs. {{ formatPrice(orderTotal + (orderTotal >= 2000 ? 0 : 200)) }} via
                        {{ form.payment_method === 'jazzcash' ? 'JazzCash' : 'EasyPaisa' }}
                    </button>
                </form>
            </div>

            <!-- Summary -->
            <div class="bg-white rounded-xl p-5 shadow-sm h-fit">
                <h2 class="font-semibold mb-4">Order Summary</h2>
                <div class="space-y-3 mb-4">
                    <div v-for="item in cart.items" :key="item.id" class="flex items-center gap-3">
                        <img :src="item.product?.thumbnail" :alt="item.product?.name" class="w-12 h-12 object-cover rounded-lg" />
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-700 line-clamp-1">{{ item.product?.name }}</p>
                            <p class="text-xs text-gray-500">x{{ item.quantity }}</p>
                        </div>
                        <span class="text-sm font-medium">Rs. {{ formatPrice((item.product?.sale_price ?? item.product?.price) * item.quantity) }}</span>
                    </div>
                </div>
                <div class="border-t pt-3 space-y-1 text-sm">
                    <div class="flex justify-between"><span class="text-gray-600">Subtotal</span><span>Rs. {{ formatPrice(cart.total) }}</span></div>
                    <div v-if="appliedCoupon" class="flex justify-between text-green-600">
                        <span>Discount ({{ appliedCoupon.code }})</span>
                        <span>− Rs. {{ formatPrice(appliedCoupon.discount) }}</span>
                    </div>
                    <div class="flex justify-between"><span class="text-gray-600">Shipping</span><span>{{ orderTotal >= 2000 ? 'FREE' : 'Rs. 200' }}</span></div>
                    <div class="flex justify-between font-bold text-base pt-2 border-t">
                        <span>Total</span>
                        <span class="text-orange-500">Rs. {{ formatPrice(orderTotal + (orderTotal >= 2000 ? 0 : 200)) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { formatPrice } from '@/composables/useFormatters'
import StripePayment   from '@/components/ui/StripePayment.vue'
import JazzCashPayment  from '@/components/ui/JazzCashPayment.vue'
import EasyPaisaPayment from '@/components/ui/EasyPaisaPayment.vue'
import api from '@/api'

const cart   = useCartStore()
const auth   = useAuthStore()
const router = useRouter()
const toast  = useToast()

const placing          = ref(false)
const errors           = ref([])
const couponInput      = ref('')
const appliedCoupon    = ref(null)
const couponError      = ref('')
const pendingOrderUuid = ref(null)   // Set after order created, used by payment components
const jazzCashRef      = ref(null)
const easyPaisaRef     = ref(null)
const applyingCoupon = ref(false)

const orderTotal = computed(() => cart.total - (appliedCoupon.value?.discount || 0))

const form = ref({
    shipping_name:        auth.user?.name || '',
    shipping_phone:       auth.user?.phone || '',
    shipping_address:     '',
    shipping_city:        '',
    shipping_state:       '',
    shipping_postal_code: '',
    payment_method:       'cod',
    notes:                '',
})

const paymentMethods = [
    { value: 'cod',       label: 'Cash on Delivery', icon: '💵' },
    { value: 'jazzcash',  label: 'JazzCash',          icon: '📱' },
    { value: 'easypaisa', label: 'EasyPaisa',          icon: '💚' },
    { value: 'card',      label: 'Card',               icon: '💳' },
]

// Compute estimated delivery label based on selected payment method
const estimatedDeliveryLabel = computed(() => {
    const days = form.value.payment_method === 'cod' ? 5 : 3
    let date = new Date()
    let added = 0
    while (added < days) {
        date.setDate(date.getDate() + 1)
        const dow = date.getDay()
        if (dow !== 0 && dow !== 6) added++ // skip Sunday (0) and Saturday (6)
    }
    return date.toLocaleDateString('en-PK', { weekday: 'short', month: 'short', day: 'numeric' })
})

function onStripeSuccess(paymentIntent) {
    toast.success('Payment authorised! Placing your order...')
    placeOrder()
}

function onStripeError(err) {
    toast.error(err?.message || 'Card payment failed. Please try again.')
}

async function applyCoupon() {
    if (!couponInput.value.trim()) return
    applyingCoupon.value = true
    couponError.value    = ''
    try {
        const { data } = await api.post('/coupons/validate', {
            code:       couponInput.value.trim(),
            cart_total: cart.total,
        })
        appliedCoupon.value = data
        toast.success(`Coupon applied! Rs. ${formatPrice(data.discount)} off.`)
    } catch (e) {
        couponError.value = e.response?.data?.message || 'Invalid coupon.'
    } finally {
        applyingCoupon.value = false
    }
}

function removeCoupon() {
    appliedCoupon.value = null
    couponInput.value   = ''
    couponError.value   = ''
}

function sendWhatsAppNotification(order) {
    const items = cart.items.map(i =>
        `• ${i.product?.name} x${i.quantity} = Rs. ${Number((i.product?.sale_price ?? i.product?.price) * i.quantity).toLocaleString('en-PK')}`
    ).join('\n')

    const msg = [
        `🛒 *New Order — ${order.order_number}*`,
        ``,
        `👤 *Customer:* ${order.shipping_name}`,
        `📞 *Phone:* ${order.shipping_phone}`,
        `📍 *Address:* ${order.shipping_address}, ${order.shipping_city}`,
        `💳 *Payment:* ${order.payment_method?.toUpperCase()}`,
        ``,
        `📦 *Items:*`,
        items,
        ``,
        `💰 *Total: Rs. ${Number(order.total).toLocaleString('en-PK')}*`,
    ].join('\n')

    const waUrl = `https://wa.me/923090046009?text=${encodeURIComponent(msg)}`
    window.open(waUrl, '_blank')
}

async function placeOrder() {
    errors.value  = []
    placing.value = true

    try {
        const payload = { ...form.value }
        if (appliedCoupon.value) payload.coupon_code = appliedCoupon.value.code

        const { data } = await api.post('/orders', payload)

        // Send WhatsApp to admin
        sendWhatsAppNotification(data)

        // COD or Card — go straight to order page
        if (form.value.payment_method === 'cod' || form.value.payment_method === 'card') {
            toast.success('Order placed successfully!')
            router.push({ name: 'order', params: { uuid: data.uuid } })
            return
        }

        // JazzCash or EasyPaisa — show payment form on the same page
        pendingOrderUuid.value = data.uuid
        toast.success('Order created! Please complete your payment below.')

    } catch (e) {
        if (e.response?.data?.errors) {
            errors.value = Object.values(e.response.data.errors).flat()
        } else {
            toast.error(e.response?.data?.message || 'Failed to place order.')
        }
    } finally {
        placing.value = false
    }
}

async function submitPayment() {
    if (form.value.payment_method === 'jazzcash') {
        const ok = await jazzCashRef.value?.pay(api)
        if (ok) router.push({ name: 'order', params: { uuid: pendingOrderUuid.value } })
    } else if (form.value.payment_method === 'easypaisa') {
        await easyPaisaRef.value?.pay(api)
        // EasyPaisa redirects to external page — no further action needed
    }
}

function onJazzCashSuccess() {
    toast.success('JazzCash payment successful!')
    router.push({ name: 'order', params: { uuid: pendingOrderUuid.value } })
}

function onPaymentError(data) {
    toast.error(data.message || 'Payment failed. Please try again.')
}

onMounted(() => cart.fetchCart())
</script>
