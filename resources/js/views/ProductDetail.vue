<template>
    <div v-if="loading" class="text-center py-20 text-gray-400">Loading...</div>

    <div v-else-if="product" class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Images -->
        <div class="flex gap-4">
            <!-- Main image + zoom wrapper -->
            <div class="flex-1">
                <div
                    class="bg-gray-100 rounded-xl overflow-hidden mb-3 aspect-square flex items-center justify-center relative cursor-crosshair"
                    @mousemove="onImageMouseMove"
                    @mouseleave="zoomActive = false"
                    @mouseenter="onImageMouseEnter"
                    ref="imgContainer"
                >
                    <img
                        v-if="activeImage && !mainImgError"
                        :src="activeImage"
                        :alt="product.name"
                        @error="mainImgError = true"
                        class="w-full h-full object-contain p-4"
                        style="pointer-events: none;"
                    />
                    <div v-else class="flex flex-col items-center justify-center text-gray-300 p-8">
                        <svg class="w-24 h-24 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-sm text-gray-400">No image available</span>
                    </div>
                </div>
                <div v-if="allImages.length > 1" class="flex gap-2 overflow-x-auto">
                    <img
                        v-for="(img, i) in allImages" :key="i"
                        :src="img"
                        :alt="`${product.name} — view ${i + 1}`"
                        @click="activeImage = img; zoomActive = false"
                        :class="['w-16 h-16 object-cover rounded-lg cursor-pointer border-2', activeImage === img ? 'border-orange-500' : 'border-transparent']"
                    />
                </div>
            </div>

            <!-- Zoom panel (desktop only) -->
            <div
                v-if="zoomActive && activeImage && !mainImgError"
                class="hidden md:block w-72 h-72 rounded-xl border border-gray-200 shadow-xl overflow-hidden flex-shrink-0 bg-white"
                style="position: sticky; top: 80px; align-self: flex-start;"
            >
                <div
                    class="w-full h-full"
                    :style="{
                        backgroundImage: `url(${activeImage})`,
                        backgroundRepeat: 'no-repeat',
                        backgroundSize: '250%',
                        backgroundPosition: zoomBgPosition,
                    }"
                ></div>
            </div>
        </div>

        <!-- Info -->
        <div>
            <p class="text-sm text-gray-400 mb-1">{{ product.category?.name }} › {{ product.brand?.name }}</p>
            <h1 class="text-2xl font-bold text-gray-900 mb-3">{{ product.name }}</h1>

            <!-- Rating -->
            <div class="flex items-center gap-2 mb-4">
                <div class="flex">
                    <span v-for="s in 5" :key="s" :class="s <= avgRating ? 'text-yellow-400' : 'text-gray-300'">★</span>
                </div>
                <span class="text-sm text-gray-500">({{ product.reviews?.length || 0 }} reviews)</span>
            </div>

            <!-- Price -->
            <div class="flex items-center gap-3 mb-4">
                <span class="text-3xl font-bold text-orange-500">Rs. {{ formatPrice(product.sale_price ?? product.price) }}</span>
                <span v-if="product.sale_price" class="text-gray-400 line-through text-lg">Rs. {{ formatPrice(product.price) }}</span>
                <span v-if="product.sale_price" class="bg-red-100 text-red-600 text-sm px-2 py-0.5 rounded-full">{{ discountPercent }}% OFF</span>
            </div>

            <p class="text-gray-600 mb-4">{{ product.short_description }}</p>

            <!-- Stock -->
            <div class="mb-4">
                <span v-if="product.stock > 0" class="text-green-600 text-sm font-medium">✓ In Stock ({{ product.stock }} available)</span>
                <span v-else class="text-red-500 text-sm font-medium">✗ Out of Stock</span>
            </div>

            <!-- Qty + Add to Cart -->
            <div v-if="product.stock > 0" class="flex items-center gap-3 mb-6">
                <div class="flex items-center border rounded-lg overflow-hidden">
                    <button @click="qty = Math.max(1, qty - 1)" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-lg">−</button>
                    <span class="px-4 py-2 text-sm font-medium">{{ qty }}</span>
                    <button @click="qty = Math.min(product.stock, qty + 1)" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-lg">+</button>
                </div>
                <button @click="addToCart" :disabled="adding" class="flex-1 bg-orange-500 hover:bg-orange-600 disabled:opacity-60 text-white py-3 rounded-xl font-semibold transition-colors">
                    {{ adding ? 'Adding...' : 'Add to Cart' }}
                </button>
                <button @click="toggleWishlist" class="p-3 border rounded-xl hover:border-orange-400 transition-colors" :title="inWishlist ? 'Remove from wishlist' : 'Add to wishlist'">
                    <svg class="w-5 h-5" :class="inWishlist ? 'text-red-500 fill-current' : 'text-gray-400'" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </button>
            </div>

            <!-- Features -->
            <div class="grid grid-cols-2 gap-3 mb-6">
                <div class="flex items-center gap-2 text-sm text-gray-600"><span>🚚</span> Free Delivery above Rs. 2000</div>
                <div class="flex items-center gap-2 text-sm text-gray-600"><span>💵</span> Cash on Delivery</div>
                <div class="flex items-center gap-2 text-sm text-gray-600"><span>🔄</span> 7 Day Returns</div>
                <div class="flex items-center gap-2 text-sm text-gray-600"><span>✅</span> 100% Genuine</div>
            </div>
        </div>

        <!-- Description / Reviews / Q&A tabs -->
        <div class="md:col-span-2 bg-white rounded-xl p-6 shadow-sm">
            <div class="border-b mb-4 flex gap-4">
                <button @click="tab = 'desc'" :class="['pb-3 text-sm font-medium border-b-2 transition-colors', tab === 'desc' ? 'border-orange-500 text-orange-500' : 'border-transparent text-gray-500']">Description</button>
                <button @click="tab = 'reviews'" :class="['pb-3 text-sm font-medium border-b-2 transition-colors', tab === 'reviews' ? 'border-orange-500 text-orange-500' : 'border-transparent text-gray-500']">Reviews ({{ product.reviews?.length || 0 }})</button>
                <button @click="tab = 'qa'" :class="['pb-3 text-sm font-medium border-b-2 transition-colors', tab === 'qa' ? 'border-orange-500 text-orange-500' : 'border-transparent text-gray-500']">Q&amp;A</button>
            </div>

            <div v-if="tab === 'desc'" class="prose max-w-none text-gray-700 text-sm" v-html="sanitizedDescription"></div>

            <div v-else-if="tab === 'reviews'">
                <div v-for="review in product.reviews" :key="review.id" class="border-b last:border-0 py-4">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-medium text-sm">{{ review.user?.name }}</span>
                        <div class="flex text-yellow-400 text-xs">
                            <span v-for="s in 5" :key="s">{{ s <= review.rating ? '★' : '☆' }}</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600">{{ review.body }}</p>
                </div>
                <div v-if="!product.reviews?.length" class="text-gray-400 text-sm text-center py-8">No reviews yet.</div>
            </div>

            <div v-else-if="tab === 'qa'">
                <ProductQA :product-slug="product.slug" />
            </div>
        </div>

        <!-- Stock alert for out-of-stock -->
        <div v-if="product.stock === 0" class="md:col-span-2">
            <StockAlertButton :product-id="product.id" />
        </div>

        <!-- Related products -->
        <div v-if="related.length" class="md:col-span-2">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Related Products</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <ProductCard v-for="p in related" :key="p.id" :product="p" />
            </div>
        </div>

        <!-- Recently viewed -->
        <div class="md:col-span-2">
            <RecentlyViewed />
        </div>
    </div>

    <div v-else class="text-center py-20">
        <p class="text-5xl mb-4">😕</p>
        <p class="text-gray-500">Product not found.</p>
    </div>

    <!-- Email capture popup — shows after 10s if user not already submitted -->
    <EmailCapturePopup v-if="product" :product-id="product.id" :delay="10000" />
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useToast } from 'vue-toastification'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import api from '@/api'
import DOMPurify from 'dompurify'
import ProductCard from '@/components/ui/ProductCard.vue'
import EmailCapturePopup from '@/components/ui/EmailCapturePopup.vue'
import StockAlertButton from '@/components/ui/StockAlertButton.vue'
import RecentlyViewed from '@/components/ui/RecentlyViewed.vue'
import ProductQA from '@/components/ui/ProductQA.vue'
import { formatPrice } from '@/composables/useFormatters'

const route   = useRoute()
const cart    = useCartStore()
const auth    = useAuthStore()
const toast   = useToast()

const product    = ref(null)
const related    = ref([])
const loading    = ref(true)
const adding     = ref(false)
const inWishlist = ref(false)
const qty        = ref(1)
const tab        = ref('desc')
const activeImage  = ref('')
const mainImgError = ref(false)

watch(activeImage, () => { mainImgError.value = false })

const allImages = computed(() => {
    const imgs = product.value?.images?.map(i => i.image) || []
    if (product.value?.thumbnail && !imgs.includes(product.value.thumbnail)) {
        imgs.unshift(product.value.thumbnail)
    }
    return imgs
})

const avgRating = computed(() => {
    const reviews = product.value?.reviews || []
    if (!reviews.length) return 0
    return Math.round(reviews.reduce((s, r) => s + r.rating, 0) / reviews.length)
})

const discountPercent = computed(() => {
    if (!product.value?.sale_price) return 0
    return Math.round(((product.value.price - product.value.sale_price) / product.value.price) * 100)
})

const sanitizedDescription = computed(() =>
    DOMPurify.sanitize(product.value?.description || '', { USE_PROFILES: { html: true } })
)

async function addToCart() {
    adding.value = true
    try {
        await cart.addItem(product.value.id, qty.value)
        toast.success('Added to cart!')
    } catch {
        toast.error('Failed to add.')
    } finally {
        adding.value = false
    }
}

async function toggleWishlist() {
    if (!auth.isAuthenticated) {
        toast.info('Please login to use wishlist.')
        return
    }
    try {
        const { data } = await api.post('/wishlist', { product_id: product.value.id })
        inWishlist.value = data.status === 'added'
        toast.success(inWishlist.value ? 'Added to wishlist!' : 'Removed from wishlist.')
    } catch {
        toast.error('Failed.')
    }
}

function logView(productId) {
    const ua = navigator.userAgent
    const browser  = ua.includes('Chrome') ? 'Chrome' : ua.includes('Firefox') ? 'Firefox' : ua.includes('Safari') ? 'Safari' : ua.includes('Edge') ? 'Edge' : 'Other'
    const device   = /Mobi|Android/i.test(ua) ? 'Mobile' : /Tablet|iPad/i.test(ua) ? 'Tablet' : 'Desktop'
    const platform = navigator.platform || ua.match(/\(([^)]+)\)/)?.[1] || 'Unknown'
    api.post(`/products/${productId}/view`, { browser, device, platform }).catch(() => {})
}

async function loadProduct(slug) {
    loading.value    = true
    product.value    = null
    related.value    = []
    mainImgError.value = false
    try {
        const [pRes, rRes] = await Promise.all([
            api.get(`/products/${slug}`),
            api.get(`/products/${slug}/related`),
        ])
        product.value     = pRes.data
        related.value     = rRes.data
        activeImage.value = product.value.thumbnail || allImages.value[0] || ''
        logView(product.value.id)
        // Log recently viewed (fire-and-forget)
        api.post(`/recently-viewed/${product.value.id}`).catch(() => {})
    } catch {
        product.value = null
    } finally {
        loading.value = false
    }
}

// Re-load when navigating between products (Vue reuses the component)
watch(() => route.params.slug, (slug) => { if (slug) loadProduct(slug) })

onMounted(() => loadProduct(route.params.slug))
</script>
