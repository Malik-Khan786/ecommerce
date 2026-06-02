<template>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">My Wallet</h1>

        <!-- Balance card -->
        <div class="bg-gradient-to-br from-gray-800 to-gray-700 rounded-2xl p-6 mb-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white/70 text-sm">Available Balance</p>
                    <p class="text-5xl font-bold mt-1">Rs. {{ formatPrice(balance.wallet_balance || 0) }}</p>
                    <p class="text-white/60 text-sm mt-2">Store credit for refunds & special offers</p>
                </div>
                <div class="text-6xl opacity-20">💳</div>
            </div>
        </div>

        <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 mb-6 text-sm text-orange-700">
            💡 Wallet credits are added by our team for refunds and special promotions. To request a refund to wallet, place a return request on your order.
        </div>

        <!-- Recent transactions -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="p-5 border-b flex items-center justify-between">
                <h2 class="font-bold text-gray-800">Transaction History</h2>
            </div>
            <div v-if="loading" class="p-5 space-y-2">
                <div v-for="i in 5" :key="i" class="h-12 bg-gray-100 rounded animate-pulse"></div>
            </div>
            <div v-else-if="!transactions.length" class="p-10 text-center text-gray-400">
                <p class="text-3xl mb-2">💳</p>
                <p>No wallet transactions yet.</p>
            </div>
            <div v-else>
                <div v-for="tx in transactions" :key="tx.id" class="flex items-center gap-3 px-5 py-3 border-b last:border-0 hover:bg-gray-50">
                    <div :class="tx.type === 'credit' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-500'" class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold shrink-0">
                        {{ tx.type === 'credit' ? '+' : '−' }}
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-700">{{ tx.description }}</p>
                        <p class="text-xs text-gray-400">{{ formatDate(tx.created_at) }}</p>
                    </div>
                    <span :class="tx.type === 'credit' ? 'text-green-600' : 'text-red-500'" class="font-bold text-sm">
                        {{ tx.type === 'credit' ? '+' : '−' }} Rs. {{ formatPrice(tx.amount) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { formatPrice, formatDate } from '@/composables/useFormatters'
import api from '@/api'

const balance      = ref({})
const transactions = ref([])
const loading      = ref(true)

onMounted(async () => {
    try {
        const { data } = await api.get('/wallet/balance')
        balance.value      = data
        transactions.value = data.recent_transactions || []
    } finally {
        loading.value = false
    }
})
</script>
