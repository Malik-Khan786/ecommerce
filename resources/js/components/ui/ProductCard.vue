<template>
    <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow overflow-hidden group">
        <RouterLink :to="`/products/${product.slug}`">
            <div class="relative overflow-hidden aspect-square bg-gray-100">
                <img
                    v-if="!imgError && product.thumbnail"
                    :src="product.thumbnail"
                    :alt="product.name"
                    loading="lazy"
                    @error="imgError = true"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                />
                <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-300 select-none">
                    <svg class="w-16 h-16 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-xs text-center px-2 line-clamp-2">{{ product.name }}</span>
                </div>
                <span v-if="product.sale_price" class="absolute top-2 left-2 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full font-medium">
                    {{ discountPercent }}% OFF
                </span>
                <span v-if="product.stock === 0" class="absolute inset-0 bg-black/40 flex items-center justify-center text-white font-semibold text-sm">
                    Out of Stock
                </span>
            </div>
        </RouterLink>

        <div class="p-3">
            <p class="text-xs text-gray-400 mb-1">{{ product.brand?.name }}</p>
            <RouterLink :to="`/products/${product.slug}`">
                <h3 class="text-sm font-medium text-gray-800 line-clamp-2 hover:text-orange-500">{{ product.name }}</h3>
            </RouterLink>

            <div class="flex items-center gap-2 mt-2">
                <span class="text-orange-500 font-bold text-base">
                    Rs. {{ formatPrice(product.sale_price ?? product.price) }}
                </span>
                <span v-if="product.sale_price" class="text-gray-400 text-xs line-through">
                    Rs. {{ formatPrice(product.price) }}
                </span>
            </div>

            <button
                v-if="product.stock > 0"
                @click="addToCart"
                :disabled="adding"
                class="mt-3 w-full bg-orange-500 hover:bg-orange-600 disabled:opacity-60 text-white text-sm py-2 rounded-lg transition-colors"
            >
                {{ adding ? 'Adding...' : 'Add to Cart' }}
            </button>

            <button
                @click.prevent="toggleCompare"
                :class="[
                    'mt-1.5 w-full text-xs py-1.5 rounded-lg border transition-colors',
                    isInComparison
                        ? 'border-orange-400 bg-orange-50 text-orange-600 font-medium'
                        : 'border-gray-200 text-gray-500 hover:border-orange-300 hover:text-orange-500'
                ]"
            >
                ⚖️ {{ isInComparison ? 'Remove Compare' : 'Compare' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useToast } from 'vue-toastification'
import { useCartStore } from '@/stores/cart'
import { useComparisonStore } from '@/stores/comparison'

const props      = defineProps({ product: Object })
const cart       = useCartStore()
const comparison = useComparisonStore()
const toast      = useToast()
const adding   = ref(false)
const imgError = ref(false)

const isInComparison = computed(() => comparison.isAdded(props.product.id))

function toggleCompare() {
    if (isInComparison.value) {
        comparison.remove(props.product.id)
    } else {
        comparison.add(props.product)
    }
}

const discountPercent = computed(() => {
    if (!props.product.sale_price) return 0
    return Math.round(((props.product.price - props.product.sale_price) / props.product.price) * 100)
})

function formatPrice(val) {
    return Number(val).toLocaleString('en-PK')
}

async function addToCart() {
    adding.value = true
    try {
        await cart.addItem(props.product.id)
        toast.success('Added to cart!')
    } catch {
        toast.error('Failed to add to cart.')
    } finally {
        adding.value = false
    }
}
</script>
