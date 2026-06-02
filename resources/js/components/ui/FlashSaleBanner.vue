<template>
    <div
        v-if="!expired"
        class="w-full rounded-2xl overflow-hidden shadow-lg"
        style="background: linear-gradient(135deg, #ff6b35 0%, #f7c59f 50%, #e63946 100%);"
    >
        <div class="px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <!-- Label + Product -->
            <div class="flex items-center gap-3">
                <span class="text-2xl">⚡</span>
                <div>
                    <p class="text-white font-black text-lg sm:text-xl tracking-wide leading-none">
                        FLASH SALE &mdash; {{ sale.discount_percent }}% OFF
                    </p>
                    <p v-if="sale.product_name" class="text-white/80 text-sm mt-0.5">{{ sale.product_name }}</p>
                </div>
            </div>

            <!-- Countdown -->
            <div class="flex items-center gap-1 sm:gap-2">
                <div
                    v-for="(segment, i) in timeSegments"
                    :key="i"
                    class="flex items-center gap-1 sm:gap-2"
                >
                    <div class="bg-black/30 backdrop-blur-sm text-white rounded-xl px-3 py-2 text-center min-w-[52px]">
                        <p class="text-2xl font-mono font-bold leading-none">{{ segment.value }}</p>
                        <p class="text-white/70 text-xs mt-0.5">{{ segment.label }}</p>
                    </div>
                    <span v-if="i < timeSegments.length - 1" class="text-white font-bold text-xl hidden sm:block">:</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
    sale: {
        type: Object,
        required: true,
    },
})

const emit = defineEmits(['expired'])

const remaining = ref(0)
const expired   = ref(false)
let timer       = null

function calcRemaining() {
    const diff = new Date(props.sale.ends_at).getTime() - Date.now()
    return Math.max(0, Math.floor(diff / 1000))
}

function pad(n) {
    return String(n).padStart(2, '0')
}

const timeSegments = computed(() => {
    const s = remaining.value
    const h = Math.floor(s / 3600)
    const m = Math.floor((s % 3600) / 60)
    const sec = s % 60
    return [
        { value: pad(h),   label: 'HRS' },
        { value: pad(m),   label: 'MIN' },
        { value: pad(sec), label: 'SEC' },
    ]
})

function tick() {
    remaining.value = calcRemaining()
    if (remaining.value === 0) {
        expired.value = true
        clearInterval(timer)
        emit('expired')
    }
}

onMounted(() => {
    remaining.value = calcRemaining()
    if (remaining.value > 0) {
        timer = setInterval(tick, 1000)
    } else {
        expired.value = true
        emit('expired')
    }
})

onUnmounted(() => {
    if (timer) clearInterval(timer)
})
</script>
