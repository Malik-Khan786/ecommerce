<template>
    <div>
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Product Comparison</h1>

        <!-- Empty state -->
        <div v-if="comparison.items.length === 0" class="text-center py-20">
            <div class="text-6xl mb-4">⚖️</div>
            <p class="text-gray-500 text-lg">Add products to compare</p>
            <RouterLink to="/products" class="mt-4 inline-block bg-orange-500 text-white px-6 py-2.5 rounded-xl hover:bg-orange-600 transition-colors">
                Browse Products
            </RouterLink>
        </div>

        <div v-else>
            <!-- Sticky header row -->
            <div class="sticky top-16 z-30 bg-white shadow-md rounded-xl mb-6 overflow-hidden">
                <div class="grid gap-0" :style="`grid-template-columns: 180px repeat(${comparison.items.length}, 1fr)`">
                    <div class="p-4 bg-gray-50 flex items-center">
                        <span class="text-sm font-semibold text-gray-500">Products</span>
                    </div>
                    <div
                        v-for="product in comparison.items"
                        :key="product.id"
                        class="p-4 border-l text-center relative"
                    >
                        <button
                            @click="comparison.remove(product.id)"
                            class="absolute top-2 right-2 w-6 h-6 rounded-full bg-gray-100 hover:bg-red-100 hover:text-red-500 text-gray-400 flex items-center justify-center text-xs transition-colors"
                            title="Remove from comparison"
                        >✕</button>
                        <img
                            v-if="product.thumbnail"
                            :src="product.thumbnail"
                            :alt="product.name"
                            class="w-16 h-16 object-contain mx-auto mb-2 rounded-lg"
                        />
                        <div v-else class="w-16 h-16 bg-gray-100 rounded-lg mx-auto mb-2 flex items-center justify-center text-gray-300">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <RouterLink :to="`/products/${product.slug}`" class="text-sm font-medium text-gray-800 hover:text-orange-500 line-clamp-2 block">
                            {{ product.name }}
                        </RouterLink>
                    </div>
                </div>
            </div>

            <!-- Comparison table -->
            <div v-if="loading" class="text-center py-12 text-gray-400">Loading comparison data...</div>

            <div v-else-if="fullProducts.length" class="bg-white rounded-xl shadow-sm overflow-hidden">
                <!-- Row: Price -->
                <div
                    class="grid border-b"
                    :style="`grid-template-columns: 180px repeat(${fullProducts.length}, 1fr)`"
                >
                    <div class="p-4 bg-gray-50 font-semibold text-sm text-gray-600">Price</div>
                    <div
                        v-for="product in fullProducts"
                        :key="product.id"
                        class="p-4 border-l text-center"
                    >
                        <span v-if="product.sale_price" class="text-gray-400 line-through text-xs block">
                            Rs. {{ formatPrice(product.price) }}
                        </span>
                        <span class="text-orange-500 font-bold text-base block">
                            Rs. {{ formatPrice(product.sale_price ?? product.price) }}
                        </span>
                    </div>
                </div>

                <!-- Row: Sale Price -->
                <div
                    class="grid border-b"
                    :style="`grid-template-columns: 180px repeat(${fullProducts.length}, 1fr)`"
                >
                    <div class="p-4 bg-gray-50 font-semibold text-sm text-gray-600">Discount</div>
                    <div
                        v-for="product in fullProducts"
                        :key="product.id"
                        class="p-4 border-l text-center"
                    >
                        <span v-if="product.sale_price" class="bg-red-100 text-red-600 text-xs px-2 py-0.5 rounded-full">
                            {{ discountPercent(product) }}% OFF
                        </span>
                        <span v-else class="text-gray-400 text-sm">—</span>
                    </div>
                </div>

                <!-- Row: Category -->
                <div
                    class="grid border-b"
                    :style="`grid-template-columns: 180px repeat(${fullProducts.length}, 1fr)`"
                >
                    <div class="p-4 bg-gray-50 font-semibold text-sm text-gray-600">Category</div>
                    <div
                        v-for="product in fullProducts"
                        :key="product.id"
                        class="p-4 border-l text-center text-sm text-gray-700"
                    >
                        {{ product.specs?.category || product.category?.name || '—' }}
                    </div>
                </div>

                <!-- Row: Brand -->
                <div
                    class="grid border-b"
                    :style="`grid-template-columns: 180px repeat(${fullProducts.length}, 1fr)`"
                >
                    <div class="p-4 bg-gray-50 font-semibold text-sm text-gray-600">Brand</div>
                    <div
                        v-for="product in fullProducts"
                        :key="product.id"
                        class="p-4 border-l text-center text-sm text-gray-700"
                    >
                        {{ product.specs?.brand || product.brand?.name || '—' }}
                    </div>
                </div>

                <!-- Row: Stock -->
                <div
                    class="grid border-b"
                    :style="`grid-template-columns: 180px repeat(${fullProducts.length}, 1fr)`"
                >
                    <div class="p-4 bg-gray-50 font-semibold text-sm text-gray-600">Stock</div>
                    <div
                        v-for="product in fullProducts"
                        :key="product.id"
                        class="p-4 border-l text-center"
                    >
                        <span v-if="product.stock > 0" class="text-green-600 text-sm font-medium">In Stock ({{ product.stock }})</span>
                        <span v-else class="text-red-500 text-sm">Out of Stock</span>
                    </div>
                </div>

                <!-- Row: Rating -->
                <div
                    class="grid border-b"
                    :style="`grid-template-columns: 180px repeat(${fullProducts.length}, 1fr)`"
                >
                    <div class="p-4 bg-gray-50 font-semibold text-sm text-gray-600">Rating</div>
                    <div
                        v-for="product in fullProducts"
                        :key="product.id"
                        class="p-4 border-l text-center"
                    >
                        <div v-if="product.specs?.avg_rating" class="flex justify-center gap-0.5">
                            <span v-for="s in 5" :key="s" :class="s <= Math.round(product.specs.avg_rating) ? 'text-yellow-400' : 'text-gray-300'" class="text-base">★</span>
                        </div>
                        <span class="text-xs text-gray-500 block">{{ product.specs?.review_count || 0 }} reviews</span>
                    </div>
                </div>

                <!-- Row: Description -->
                <div
                    class="grid border-b"
                    :style="`grid-template-columns: 180px repeat(${fullProducts.length}, 1fr)`"
                >
                    <div class="p-4 bg-gray-50 font-semibold text-sm text-gray-600">Description</div>
                    <div
                        v-for="product in fullProducts"
                        :key="product.id"
                        class="p-4 border-l text-sm text-gray-600"
                    >
                        {{ truncate(product.short_description || product.description || '—', 120) }}
                    </div>
                </div>

                <!-- Row: Add to Cart -->
                <div
                    class="grid"
                    :style="`grid-template-columns: 180px repeat(${fullProducts.length}, 1fr)`"
                >
                    <div class="p-4 bg-gray-50 font-semibold text-sm text-gray-600">Actions</div>
                    <div
                        v-for="product in fullProducts"
                        :key="product.id"
                        class="p-4 border-l flex flex-col gap-2"
                    >
                        <button
                            v-if="product.stock > 0"
                            @click="addToCart(product)"
                            :disabled="adding[product.id]"
                            :class="[
                                'w-full py-2 rounded-lg text-sm font-medium transition-colors',
                                lowestPrice === effectivePrice(product)
                                    ? 'bg-green-500 hover:bg-green-600 text-white'
                                    : 'bg-orange-500 hover:bg-orange-600 text-white',
                                'disabled:opacity-60'
                            ]"
                        >
                            {{ adding[product.id] ? 'Adding...' : 'Add to Cart' }}
                        </button>
                        <span v-else class="text-center text-sm text-red-400">Out of Stock</span>
                        <button
                            @click="comparison.remove(product.id)"
                            class="w-full py-1.5 rounded-lg text-xs text-gray-500 border border-gray-200 hover:border-red-300 hover:text-red-500 transition-colors"
                        >
                            Remove
                        </button>
                    </div>
                </div>
            </div>

            <!-- Best value note -->
            <p v-if="fullProducts.length > 1" class="text-xs text-gray-400 mt-3 text-center">
                <span class="inline-block w-3 h-3 bg-green-500 rounded-sm mr-1 align-middle"></span> Green "Add to Cart" = best value (lowest price)
            </p>

            <!-- Clear all -->
            <div class="text-center mt-6">
                <button @click="comparison.clear()" class="text-sm text-red-500 hover:text-red-700 underline">
                    Clear all comparisons
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useToast } from 'vue-toastification'
import { useComparisonStore } from '@/stores/comparison'
import { useCartStore } from '@/stores/cart'
import { formatPrice } from '@/composables/useFormatters'
import api from '@/api'

const comparison  = useComparisonStore()
const cart        = useCartStore()
const toast       = useToast()

const fullProducts = ref([])
const loading      = ref(false)
const adding       = ref({})

function effectivePrice(product) {
    return parseFloat(product.sale_price ?? product.price)
}

const lowestPrice = computed(() => {
    if (!fullProducts.value.length) return null
    return Math.min(...fullProducts.value.map(effectivePrice))
})

function discountPercent(product) {
    if (!product.sale_price) return 0
    return Math.round(((product.price - product.sale_price) / product.price) * 100)
}

function truncate(text, max) {
    if (!text) return '—'
    // Strip HTML tags
    const plain = text.replace(/<[^>]+>/g, '')
    return plain.length > max ? plain.slice(0, max) + '…' : plain
}

async function fetchComparison() {
    if (!comparison.items.length) {
        fullProducts.value = []
        return
    }
    loading.value = true
    try {
        const { data } = await api.post('/compare', {
            product_ids: comparison.items.map(p => p.id),
        })
        fullProducts.value = data
    } catch {
        toast.error('Failed to load comparison data.')
    } finally {
        loading.value = false
    }
}

async function addToCart(product) {
    adding.value[product.id] = true
    try {
        await cart.addItem(product.id)
        toast.success('Added to cart!')
    } catch {
        toast.error('Failed to add to cart.')
    } finally {
        adding.value[product.id] = false
    }
}

watch(() => comparison.items.length, fetchComparison)
onMounted(fetchComparison)
</script>
