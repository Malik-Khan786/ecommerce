<template>
    <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow overflow-hidden">
        <!-- Bundle image or product stack -->
        <div class="relative bg-gray-50 aspect-video flex items-center justify-center overflow-hidden">
            <img
                v-if="bundle.image"
                :src="bundle.image"
                :alt="bundle.name"
                class="w-full h-full object-cover"
            />
            <div v-else class="flex items-center justify-center gap-1 px-4">
                <div
                    v-for="(product, i) in bundle.products?.slice(0, 3)"
                    :key="product.id"
                    :style="`z-index: ${3 - i}; transform: translateX(${i * -12}px) rotate(${i * 4 - 4}deg)`"
                    class="w-20 h-20 rounded-lg border-2 border-white shadow-md overflow-hidden bg-white flex-shrink-0"
                >
                    <img
                        v-if="product.thumbnail"
                        :src="product.thumbnail"
                        :alt="product.name"
                        class="w-full h-full object-contain p-1"
                    />
                    <div v-else class="w-full h-full flex items-center justify-center text-gray-200">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                </div>
            </div>

            <!-- Discount badge -->
            <div
                v-if="bundle.discount_percent > 0"
                class="absolute top-3 right-3 bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow"
            >
                {{ bundle.discount_percent }}% OFF
            </div>
        </div>

        <div class="p-4">
            <h3 class="font-bold text-gray-800 text-base mb-1">{{ bundle.name }}</h3>
            <p v-if="bundle.description" class="text-sm text-gray-500 mb-3 line-clamp-2">{{ bundle.description }}</p>

            <!-- Included products list -->
            <div class="mb-4 space-y-1.5">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Includes:</p>
                <div
                    v-for="product in bundle.products"
                    :key="product.id"
                    class="flex items-center gap-2"
                >
                    <img
                        v-if="product.thumbnail"
                        :src="product.thumbnail"
                        :alt="product.name"
                        class="w-8 h-8 object-contain rounded border border-gray-100 bg-white"
                    />
                    <div v-else class="w-8 h-8 rounded border border-gray-100 bg-gray-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <span class="text-xs text-gray-700 line-clamp-1 flex-1">{{ product.name }}</span>
                    <span v-if="product.pivot?.quantity > 1" class="text-xs text-orange-500 font-medium">×{{ product.pivot.quantity }}</span>
                </div>
            </div>

            <!-- Pricing -->
            <div class="flex items-center gap-2 mb-4">
                <span class="text-gray-400 text-sm line-through">Rs. {{ formatPrice(totalOriginalPrice) }}</span>
                <span class="text-orange-500 font-bold text-lg">Rs. {{ formatPrice(bundlePrice) }}</span>
            </div>

            <!-- Add to Cart -->
            <button
                @click="$emit('add-to-cart', bundle)"
                :disabled="adding"
                class="w-full bg-orange-500 hover:bg-orange-600 disabled:opacity-60 text-white py-2.5 rounded-lg font-semibold text-sm transition-colors"
            >
                {{ adding ? 'Adding...' : 'Add All to Cart' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { formatPrice } from '@/composables/useFormatters'

const props = defineProps({
    bundle: { type: Object, required: true },
    adding: { type: Boolean, default: false },
})

defineEmits(['add-to-cart'])

const totalOriginalPrice = computed(() => {
    if (!props.bundle.products?.length) return 0
    return props.bundle.products.reduce((sum, p) => {
        const qty   = p.pivot?.quantity ?? 1
        const price = parseFloat(p.price ?? 0)
        return sum + price * qty
    }, 0)
})

const bundlePrice = computed(() => {
    const discount = parseFloat(props.bundle.discount_percent ?? 0)
    return totalOriginalPrice.value * (1 - discount / 100)
})
</script>
