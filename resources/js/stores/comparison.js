import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useToast } from 'vue-toastification'

const STORAGE_KEY = 'comparison_items'

export const useComparisonStore = defineStore('comparison', () => {
    // Hydrate from localStorage
    const stored = localStorage.getItem(STORAGE_KEY)
    const items  = ref(stored ? JSON.parse(stored) : [])

    function persist() {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(items.value))
    }

    const count = computed(() => items.value.length)

    function isAdded(productId) {
        return items.value.some(p => p.id === productId)
    }

    function add(product) {
        // Get toast inside the function call — safe to use here
        const toast = useToast()
        if (isAdded(product.id)) return
        if (items.value.length >= 3) {
            toast.warning('You can compare up to 3 products at a time.')
            return
        }
        items.value.push(product)
        persist()
        toast.success(`"${product.name}" added to comparison.`)
    }

    function remove(productId) {
        items.value = items.value.filter(p => p.id !== productId)
        persist()
    }

    function clear() {
        items.value = []
        persist()
    }

    return { items, count, isAdded, add, remove, clear }
})
