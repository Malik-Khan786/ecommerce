import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api'

export const useCartStore = defineStore('cart', () => {
    const items = ref([])

    const count   = computed(() => items.value.reduce((sum, i) => sum + i.quantity, 0))
    const total   = computed(() => items.value.reduce((sum, i) => sum + (i.product?.sale_price ?? i.product?.price ?? 0) * i.quantity, 0))

    async function fetchCart() {
        const { data } = await api.get('/cart')
        items.value = data
    }

    async function addItem(productId, quantity = 1) {
        const { data } = await api.post('/cart', { product_id: productId, quantity })
        const idx = items.value.findIndex(i => i.product_id === productId)
        if (idx >= 0) items.value[idx] = data
        else items.value.push(data)
    }

    async function updateItem(cartId, quantity) {
        const { data } = await api.put(`/cart/${cartId}`, { quantity })
        const idx = items.value.findIndex(i => i.id === cartId)
        if (idx >= 0) items.value[idx] = data
    }

    async function removeItem(cartId) {
        await api.delete(`/cart/${cartId}`)
        items.value = items.value.filter(i => i.id !== cartId)
    }

    async function clearCart() {
        await api.delete('/cart')
        items.value = []
    }

    return { items, count, total, fetchCart, addItem, updateItem, removeItem, clearCart }
})
