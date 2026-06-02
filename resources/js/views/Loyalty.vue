<template>
    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-gradient-to-r from-orange-500 to-yellow-400 rounded-2xl p-8 mb-8 text-white shadow-lg">
            <div class="flex items-center gap-4 mb-2">
                <span class="text-5xl">🪙</span>
                <div>
                    <p class="text-orange-100 text-sm font-medium uppercase tracking-wide">Your Balance</p>
                    <p class="text-5xl font-bold">{{ balance.toLocaleString() }}</p>
                    <p class="text-orange-100 text-sm">Loyalty Points</p>
                </div>
            </div>
            <div class="mt-4 bg-white/20 rounded-xl px-4 py-2 inline-block">
                <p class="text-sm font-medium">💡 100 Points = Rs. 100 discount</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
            <!-- Redeem Form -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Redeem Points</h2>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Points to Redeem</label>
                    <input
                        v-model.number="redeemPoints"
                        type="number"
                        :min="100"
                        :max="balance"
                        placeholder="Minimum 100 points"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                    />
                    <p v-if="redeemPoints && redeemPoints < 100" class="text-red-500 text-xs mt-1">Minimum redemption is 100 points.</p>
                    <p v-if="redeemPoints && redeemPoints > balance" class="text-red-500 text-xs mt-1">You don't have enough points.</p>
                </div>

                <div v-if="redeemPoints >= 100" class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 mb-4 flex items-center gap-2">
                    <span class="text-green-600 font-bold text-lg">= Rs. {{ formatPrice(redeemPoints) }}</span>
                    <span class="text-green-500 text-sm">discount on your next order</span>
                </div>

                <button
                    @click="redeemNow"
                    :disabled="redeeming || !redeemPoints || redeemPoints < 100 || redeemPoints > balance"
                    class="w-full bg-orange-500 hover:bg-orange-600 disabled:opacity-50 text-white font-semibold py-2.5 rounded-xl transition-colors"
                >
                    {{ redeeming ? 'Redeeming...' : 'Redeem Points' }}
                </button>

                <p v-if="redeemSuccess" class="text-green-600 text-sm text-center mt-3 font-medium">✓ Points redeemed successfully!</p>
                <p v-if="redeemError" class="text-red-500 text-sm text-center mt-3">{{ redeemError }}</p>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Points Summary</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500">Total Earned</span>
                        <span class="font-semibold text-green-600">+{{ stats.totalEarned?.toLocaleString() || 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500">Total Redeemed</span>
                        <span class="font-semibold text-red-500">-{{ stats.totalRedeemed?.toLocaleString() || 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm text-gray-500">Current Balance</span>
                        <span class="font-bold text-orange-500 text-lg">{{ balance.toLocaleString() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Points History -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Points History</h2>

            <div v-if="historyLoading" class="text-center py-10 text-gray-400 text-sm">Loading history...</div>

            <div v-else-if="history.length === 0" class="text-center py-10 text-gray-400">
                <p class="text-4xl mb-2">🪙</p>
                <p class="text-sm">No points activity yet. Start shopping to earn points!</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-400 border-b border-gray-100">
                            <th class="pb-3 font-medium">Date</th>
                            <th class="pb-3 font-medium">Description</th>
                            <th class="pb-3 font-medium">Type</th>
                            <th class="pb-3 font-medium text-right">Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="entry in history" :key="entry.id" class="border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors">
                            <td class="py-3 text-gray-500 whitespace-nowrap">{{ formatDate(entry.created_at) }}</td>
                            <td class="py-3 text-gray-700">{{ entry.description }}</td>
                            <td class="py-3">
                                <span
                                    :class="[
                                        'px-2.5 py-1 rounded-full text-xs font-semibold',
                                        entry.type === 'earned'   ? 'bg-green-100 text-green-700' :
                                        entry.type === 'redeemed' ? 'bg-red-100 text-red-600' :
                                        entry.type === 'bonus'    ? 'bg-orange-100 text-orange-600' :
                                        'bg-gray-100 text-gray-500'
                                    ]"
                                >
                                    {{ entry.type.charAt(0).toUpperCase() + entry.type.slice(1) }}
                                </span>
                            </td>
                            <td class="py-3 text-right font-semibold"
                                :class="entry.type === 'redeemed' ? 'text-red-500' : 'text-green-600'"
                            >
                                {{ entry.type === 'redeemed' ? '-' : '+' }}{{ Math.abs(entry.points).toLocaleString() }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api'
import { formatPrice, formatDate } from '@/composables/useFormatters'

const balance      = ref(0)
const history      = ref([])
const stats        = ref({})
const historyLoading = ref(true)
const redeemPoints = ref(null)
const redeeming    = ref(false)
const redeemSuccess = ref(false)
const redeemError  = ref('')

async function fetchBalance() {
    try {
        const { data } = await api.get('/loyalty/balance')
        balance.value = data.balance ?? 0
        stats.value   = data.stats ?? {}
    } catch {
        balance.value = 0
    }
}

async function fetchHistory() {
    historyLoading.value = true
    try {
        const { data } = await api.get('/loyalty/history')
        history.value = data
    } catch {
        history.value = []
    } finally {
        historyLoading.value = false
    }
}

async function redeemNow() {
    redeemSuccess.value = false
    redeemError.value   = ''
    redeeming.value     = true
    try {
        await api.post('/loyalty/redeem', { points: redeemPoints.value })
        redeemSuccess.value = true
        balance.value -= redeemPoints.value
        redeemPoints.value = null
        await fetchHistory()
    } catch (err) {
        redeemError.value = err?.response?.data?.message || 'Redemption failed. Please try again.'
    } finally {
        redeeming.value = false
    }
}

onMounted(() => {
    fetchBalance()
    fetchHistory()
})
</script>
