<template>
    <div v-if="lastPage > 1" class="flex items-center justify-center gap-1 mt-8 flex-wrap">
        <!-- Prev -->
        <button @click="$emit('change', currentPage - 1)" :disabled="currentPage === 1"
            class="px-3 py-1.5 rounded-lg text-sm border disabled:opacity-40 disabled:cursor-not-allowed hover:bg-gray-100 transition-colors">
            ‹
        </button>

        <template v-for="page in pages" :key="page">
            <span v-if="page === '...'" class="px-2 py-1.5 text-gray-400 text-sm">…</span>
            <button v-else @click="$emit('change', page)"
                :class="['px-3 py-1.5 rounded-lg text-sm transition-colors', page === currentPage ? 'bg-orange-500 text-white font-medium' : 'border hover:bg-gray-100 text-gray-700']">
                {{ page }}
            </button>
        </template>

        <!-- Next -->
        <button @click="$emit('change', currentPage + 1)" :disabled="currentPage === lastPage"
            class="px-3 py-1.5 rounded-lg text-sm border disabled:opacity-40 disabled:cursor-not-allowed hover:bg-gray-100 transition-colors">
            ›
        </button>

        <span class="text-xs text-gray-400 ml-2">Page {{ currentPage }} of {{ lastPage }}</span>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    currentPage: { type: Number, required: true },
    lastPage:    { type: Number, required: true },
})

defineEmits(['change'])

const pages = computed(() => {
    const total   = props.lastPage
    const current = props.currentPage
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)

    const result = []
    result.push(1)

    if (current > 3)          result.push('...')
    for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) {
        result.push(i)
    }
    if (current < total - 2)  result.push('...')

    result.push(total)
    return result
})
</script>
