<template>
    <div class="max-w-2xl mx-auto px-4 py-8">
        <!-- Page Title -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Refer &amp; Earn</h1>
            <p class="text-gray-500">Invite friends and earn loyalty points for every successful referral.</p>
        </div>

        <!-- Referral Code Card -->
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-8 mb-6 text-white shadow-lg">
            <p class="text-orange-200 text-sm font-medium uppercase tracking-wide mb-2">Your Referral Code</p>
            <div class="flex items-center gap-3 mb-4">
                <span class="text-4xl font-bold tracking-widest bg-white/20 px-6 py-3 rounded-xl">
                    {{ referralCode || '------' }}
                </span>
                <button
                    @click="copyCode"
                    class="flex items-center gap-2 bg-white text-orange-500 font-semibold px-4 py-3 rounded-xl hover:bg-orange-50 transition-colors text-sm"
                >
                    <svg v-if="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <svg v-else class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ copied ? 'Copied!' : 'Copy' }}
                </button>
            </div>

            <!-- Sharing message -->
            <div class="bg-white/20 rounded-xl p-4 text-sm leading-relaxed">
                🎁 Share this code with friends. They get <strong>10% off their first order</strong>, you get <strong>200 loyalty points!</strong>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 text-center">
                <p class="text-3xl font-bold text-orange-500">{{ stats.totalReferrals ?? 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Friends Referred</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 text-center">
                <p class="text-3xl font-bold text-green-500">{{ stats.pointsEarned ?? 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Points Earned</p>
            </div>
        </div>

        <!-- Apply Referral Code -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-1">Apply a Referral Code</h2>
            <p class="text-sm text-gray-400 mb-4">Have a friend's referral code? Enter it here to get 10% off your first order.</p>

            <div class="flex gap-2">
                <input
                    v-model="applyCode"
                    type="text"
                    placeholder="Enter referral code"
                    maxlength="20"
                    class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-orange-400"
                    @input="applyCode = applyCode.toUpperCase()"
                />
                <button
                    @click="submitReferral"
                    :disabled="applying || !applyCode.trim()"
                    class="bg-orange-500 hover:bg-orange-600 disabled:opacity-50 text-white font-semibold px-6 py-2.5 rounded-xl transition-colors text-sm whitespace-nowrap"
                >
                    {{ applying ? 'Applying...' : 'Apply' }}
                </button>
            </div>

            <p v-if="applySuccess" class="text-green-600 text-sm mt-3 font-medium">✓ Referral code applied! You'll get 10% off your first order.</p>
            <p v-if="applyError" class="text-red-500 text-sm mt-3">{{ applyError }}</p>
        </div>

        <!-- How it Works -->
        <div class="mt-6 bg-orange-50 rounded-2xl p-6">
            <h3 class="text-base font-bold text-orange-700 mb-4">How it Works</h3>
            <ol class="space-y-3 text-sm text-orange-800">
                <li class="flex items-start gap-3">
                    <span class="bg-orange-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">1</span>
                    Share your unique referral code with friends.
                </li>
                <li class="flex items-start gap-3">
                    <span class="bg-orange-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">2</span>
                    Your friend signs up and enters your code at checkout.
                </li>
                <li class="flex items-start gap-3">
                    <span class="bg-orange-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">3</span>
                    They get <strong>10% off</strong> their first order, you earn <strong>200 points</strong> instantly!
                </li>
            </ol>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api'

const referralCode  = ref('')
const applyCode     = ref('')
const stats         = ref({})
const copied        = ref(false)
const applying      = ref(false)
const applySuccess  = ref(false)
const applyError    = ref('')

async function fetchReferralData() {
    try {
        const { data } = await api.get('/referral')
        referralCode.value = data.code ?? ''
        stats.value        = data.stats ?? {}
    } catch {
        referralCode.value = ''
    }
}

async function copyCode() {
    if (!referralCode.value) return
    try {
        await navigator.clipboard.writeText(referralCode.value)
        copied.value = true
        setTimeout(() => { copied.value = false }, 2500)
    } catch {
        // Fallback for browsers that block clipboard
        const el = document.createElement('textarea')
        el.value = referralCode.value
        document.body.appendChild(el)
        el.select()
        document.execCommand('copy')
        document.body.removeChild(el)
        copied.value = true
        setTimeout(() => { copied.value = false }, 2500)
    }
}

async function submitReferral() {
    applySuccess.value = false
    applyError.value   = ''
    applying.value     = true
    try {
        await api.post('/referral/apply', { code: applyCode.value.trim() })
        applySuccess.value = true
        applyCode.value    = ''
    } catch (err) {
        applyError.value = err?.response?.data?.message || 'Invalid or already used referral code.'
    } finally {
        applying.value = false
    }
}

onMounted(fetchReferralData)
</script>
