<template>
    <div v-if="items.length > 0" class="mt-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Recently Viewed</h2>

        <div class="overflow-x-auto pb-2 -mx-1">
            <div class="flex gap-3 px-1" style="width: max-content;">
                <router-link
                    v-for="item in items"
                    :key="item.id"
                    :to="{ name: 'product', params: { slug: item.slug } }"
                    class="flex-shrink-0 w-36 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-orange-200 transition-all duration-200 overflow-hidden group"
                >
                    <!-- Thumbnail -->
                    <div class="h-24 bg-gray-50 flex items-center justify-center overflow-hidden">
                        <img
                            v-if="item.thumbnail"
                            :src="item.thumbnail"
                            :alt="item.name"
                            class="w-full h-full object-contain p-2 group-hover:scale-105 transition-transform duration-200"
                        />
                        <div v-else class="flex items-center justify-center text-gray-200">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="px-3 py-2">
                        <p class="text-xs text-gray-700 font-medium leading-snug line-clamp-2 mb-1">{{ item.name }}</p>
                        <p class="text-sm font-bold text-orange-500">
                            Rs. {{ formatPrice(item.sale_price ?? item.price) }}
                        </p>
                    </div>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api'
import { formatPrice } from '@/composables/useFormatters'

const items = ref([])

async function fetchRecentlyViewed() {
    try {
        const { data } = await api.get('/recently-viewed')
        items.value = (Array.isArray(data) ? data : (data.data ?? [])).slice(0, 8)
    } catch {
        items.value = []
    }
}

onMounted(fetchRecentlyViewed)
</script>
