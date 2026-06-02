<template>
    <div>
        <h1 class="text-2xl font-bold text-gray-800 mb-6">My Wishlist</h1>

        <div v-if="loading" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div v-for="i in 4" :key="i" class="bg-white rounded-xl h-72 animate-pulse"></div>
        </div>

        <div v-else-if="items.length === 0" class="text-center py-20">
            <p class="text-5xl mb-4">❤️</p>
            <p class="text-gray-500 mb-4">Your wishlist is empty.</p>
            <RouterLink to="/products" class="bg-orange-500 text-white px-6 py-2 rounded-xl hover:bg-orange-600">Browse Products</RouterLink>
        </div>

        <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <ProductCard v-for="item in items" :key="item.id" :product="item.product" />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api'
import ProductCard from '@/components/ui/ProductCard.vue'

const items   = ref([])
const loading = ref(true)

onMounted(async () => {
    try {
        const { data } = await api.get('/wishlist')
        items.value = data
    } finally {
        loading.value = false
    }
})
</script>
