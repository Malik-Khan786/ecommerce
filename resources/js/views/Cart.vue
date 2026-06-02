<template>
    <div>
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Shopping Cart</h1>

        <div v-if="cart.items.length === 0" class="text-center py-20">
            <p class="text-6xl mb-4">🛒</p>
            <p class="text-gray-500 mb-4">Your cart is empty.</p>
            <RouterLink to="/products" class="bg-orange-500 text-white px-6 py-2 rounded-xl hover:bg-orange-600">Continue Shopping</RouterLink>
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Cart Items -->
            <div class="lg:col-span-2 space-y-3">
                <div v-for="item in cart.items" :key="item.id" class="bg-white rounded-xl p-4 shadow-sm flex gap-4">
                    <button @click="askLeave(item.product)" class="shrink-0">
                        <img :src="item.product?.thumbnail || FALLBACK_IMG" @error="onImgError"
                            :alt="item.product?.name"
                            class="w-20 h-20 object-cover rounded-lg bg-gray-100 hover:opacity-80 transition-opacity" />
                    </button>
                    <div class="flex-1 min-w-0">
                        <button @click="askLeave(item.product)" class="font-medium text-gray-800 hover:text-orange-500 line-clamp-2 text-sm text-left w-full">
                            {{ item.product?.name }}
                        </button>
                        <p class="text-orange-500 font-bold mt-1">Rs. {{ formatPrice(item.product?.sale_price ?? item.product?.price) }}</p>
                        <div class="flex items-center gap-3 mt-2">
                            <div class="flex items-center border rounded-lg overflow-hidden">
                                <button @click="updateQty(item, item.quantity - 1)" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-sm">−</button>
                                <span class="px-3 py-1 text-sm">{{ item.quantity }}</span>
                                <button @click="updateQty(item, item.quantity + 1)" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-sm">+</button>
                            </div>
                            <button @click="removeItem(item.id)" class="text-red-400 hover:text-red-600 text-xs">Remove</button>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="font-bold text-gray-800">Rs. {{ formatPrice((item.product?.sale_price ?? item.product?.price) * item.quantity) }}</p>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="bg-white rounded-xl p-5 shadow-sm h-fit sticky top-24">
                <h2 class="font-bold text-lg mb-4">Order Summary</h2>
                <div class="space-y-2 text-sm mb-4">
                    <div class="flex justify-between"><span class="text-gray-600">Subtotal</span><span>Rs. {{ formatPrice(cart.total) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-600">Shipping</span><span :class="cart.total >= 2000 ? 'text-green-600' : ''">{{ cart.total >= 2000 ? 'FREE' : 'Rs. 200' }}</span></div>
                </div>
                <div class="border-t pt-3 flex justify-between font-bold text-base mb-5">
                    <span>Total</span>
                    <span class="text-orange-500">Rs. {{ formatPrice(cart.total + (cart.total >= 2000 ? 0 : 200)) }}</span>
                </div>
                <RouterLink to="/checkout" class="block bg-orange-500 hover:bg-orange-600 text-white text-center py-3 rounded-xl font-semibold transition-colors">
                    Proceed to Checkout
                </RouterLink>
                <RouterLink to="/products" class="block text-center text-sm text-gray-500 hover:text-gray-700 mt-3">Continue Shopping</RouterLink>
            </div>
        </div>

        <!-- Confirmation popup — outside v-if/v-else so it always renders -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="pendingProduct"
                    class="fixed inset-0 bg-black/60 flex items-center justify-center z-[9999] px-4"
                    @click.self="pendingProduct = null">
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 mx-auto">
                        <!-- Product preview -->
                        <div class="flex items-center gap-3 mb-5 bg-gray-50 rounded-xl p-3">
                            <img :src="pendingProduct.thumbnail || FALLBACK_IMG" @error="onImgError"
                                :alt="pendingProduct.name"
                                class="w-14 h-14 object-cover rounded-lg bg-gray-200 shrink-0" />
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-800 text-sm line-clamp-2">{{ pendingProduct.name }}</p>
                                <p class="text-orange-500 font-bold text-sm">Rs. {{ formatPrice(pendingProduct.sale_price ?? pendingProduct.price) }}</p>
                            </div>
                        </div>

                        <h3 class="text-lg font-bold text-gray-800 mb-1">Leave Cart?</h3>
                        <p class="text-gray-500 text-sm mb-6">
                            Your cart items are saved. You can come back and continue from where you left off.
                        </p>

                        <div class="flex gap-3">
                            <button @click="confirmLeave"
                                class="flex-1 bg-orange-500 hover:bg-orange-600 text-white py-2.5 rounded-xl font-semibold text-sm">
                                Yes, Open Product
                            </button>
                            <button @click="pendingProduct = null"
                                class="flex-1 border border-gray-300 text-gray-700 hover:bg-gray-50 py-2.5 rounded-xl text-sm">
                                Stay in Cart
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import { useCartStore } from '@/stores/cart'
import { FALLBACK_IMG, onImgError } from '@/composables/useImgFallback'

const cart   = useCartStore()
const toast  = useToast()
const router = useRouter()

const pendingProduct = ref(null)

function formatPrice(val) {
    return Number(val || 0).toLocaleString('en-PK')
}

function askLeave(product) {
    if (!product) return
    pendingProduct.value = product
}

function confirmLeave() {
    const slug = pendingProduct.value?.slug
    pendingProduct.value = null
    if (slug) router.push(`/products/${slug}`)
}

async function updateQty(item, qty) {
    if (qty < 1) { await removeItem(item.id); return }
    try { await cart.updateItem(item.id, qty) }
    catch { toast.error('Failed to update.') }
}

async function removeItem(id) {
    try { await cart.removeItem(id); toast.success('Item removed.') }
    catch { toast.error('Failed.') }
}

onMounted(() => cart.fetchCart())
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to       { opacity: 0; }
</style>
