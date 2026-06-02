<template>
    <Transition name="popup">
        <div v-if="visible" class="fixed inset-0 z-[9999] flex items-center justify-center px-4" @click.self="dismiss">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/50"></div>

            <!-- Card -->
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
                <!-- Top banner -->
                <div class="bg-gradient-to-r from-orange-500 to-orange-400 px-6 pt-8 pb-10 text-center">
                    <p class="text-white/80 text-sm font-medium uppercase tracking-wider mb-1">Special Offer</p>
                    <h2 class="text-white text-3xl font-bold mb-1">Get 10% OFF</h2>
                    <p class="text-white/90 text-sm">on your first order at Huzaifa Mobile</p>
                    <div class="absolute -bottom-5 left-1/2 -translate-x-1/2 w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-md">
                        <span class="text-xl">🎁</span>
                    </div>
                </div>

                <!-- Form -->
                <div class="px-6 pt-10 pb-6">
                    <p class="text-gray-600 text-sm text-center mb-4">
                        Enter your details below and we'll send you an exclusive discount code.
                    </p>

                    <form v-if="!submitted" @submit.prevent="submit" class="space-y-3">
                        <div>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="Your name (optional)"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400"
                            />
                        </div>
                        <div>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                placeholder="Your email address *"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="saving"
                            class="w-full bg-orange-500 hover:bg-orange-600 disabled:opacity-60 text-white font-semibold py-3 rounded-xl transition-colors"
                        >
                            {{ saving ? 'Saving...' : 'Claim My 10% Off' }}
                        </button>

                        <p class="text-xs text-gray-400 text-center">
                            No spam. Unsubscribe anytime.
                        </p>
                    </form>

                    <!-- Success state -->
                    <div v-else class="text-center py-4">
                        <p class="text-4xl mb-3">🎉</p>
                        <h3 class="text-lg font-bold text-gray-800 mb-1">You're in!</h3>
                        <p class="text-gray-500 text-sm">We'll contact you soon with your discount code.</p>
                        <button @click="dismiss" class="mt-4 text-orange-500 text-sm hover:underline">Continue Shopping →</button>
                    </div>
                </div>

                <!-- Close button -->
                <button
                    @click="dismiss"
                    class="absolute top-3 right-3 text-white/70 hover:text-white text-2xl leading-none w-8 h-8 flex items-center justify-center"
                    aria-label="Close"
                >✕</button>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import api from '@/api'

const props = defineProps({
    productId: { type: Number, default: null },
    delay:     { type: Number, default: 10000 },
})

const visible   = ref(false)
const submitted = ref(false)
const saving    = ref(false)
const form      = ref({ name: '', email: '' })

let timer = null

const STORAGE_KEY = 'hm_lead_captured'

function shouldShow() {
    // Don't show again if already submitted in this session
    return !sessionStorage.getItem(STORAGE_KEY)
}

function dismiss() {
    visible.value = false
    // Don't show again for this browser session even if not submitted
    sessionStorage.setItem(STORAGE_KEY, '1')
}

async function submit() {
    saving.value = true
    try {
        const ua      = navigator.userAgent
        const browser = ua.includes('Chrome') ? 'Chrome' : ua.includes('Firefox') ? 'Firefox' : ua.includes('Safari') ? 'Safari' : ua.includes('Edge') ? 'Edge' : 'Other'
        const device  = /Mobi|Android/i.test(ua) ? 'Mobile' : /Tablet|iPad/i.test(ua) ? 'Tablet' : 'Desktop'

        await api.post('/leads', {
            email:      form.value.email,
            name:       form.value.name || null,
            product_id: props.productId,
            browser,
            device,
        })

        submitted.value = true
        sessionStorage.setItem(STORAGE_KEY, '1')
    } catch {
        // fail silently — don't interrupt the user experience
    } finally {
        saving.value = false
    }
}

onMounted(() => {
    if (!shouldShow()) return
    timer = setTimeout(() => { visible.value = true }, props.delay)
})

onUnmounted(() => clearTimeout(timer))
</script>

<style scoped>
.popup-enter-active, .popup-leave-active { transition: opacity 0.3s ease; }
.popup-enter-from, .popup-leave-to      { opacity: 0; }
.popup-enter-active .relative,
.popup-leave-active .relative           { transition: transform 0.3s ease; }
.popup-enter-from .relative             { transform: scale(0.9); }
.popup-leave-to .relative               { transform: scale(0.9); }
</style>
