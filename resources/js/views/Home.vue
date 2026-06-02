<template>
    <div>
        <!-- Hero Banners -->
        <section class="mb-8">
            <div v-if="banners.length" class="relative overflow-hidden rounded-xl">
                <div class="flex w-full transition-transform duration-500" :style="{ transform: `translateX(-${currentBanner * 100}%)` }">
                    <div v-for="banner in banners" :key="banner.id" class="min-w-full w-full shrink-0 relative">
                        <img :src="banner.image" :alt="banner.title" class="w-full h-64 md:h-96 object-cover rounded-xl" />
                        <div class="absolute inset-0 flex flex-col justify-center px-10 bg-black/20 rounded-xl">
                            <h2 class="text-white text-3xl md:text-5xl font-bold mb-2">{{ banner.title }}</h2>
                            <p class="text-white/90 text-lg mb-4">{{ banner.subtitle }}</p>
                            <RouterLink
                                v-if="banner.link"
                                :to="banner.link"
                                class="inline-block bg-orange-500 text-white px-6 py-2 rounded-lg hover:bg-orange-600 w-max"
                            >{{ banner.button_text || 'Shop Now' }}</RouterLink>
                        </div>
                    </div>
                </div>
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
                    <button
                        v-for="(_, i) in banners" :key="i"
                        @click="currentBanner = i"
                        :class="['w-2 h-2 rounded-full transition-colors', i === currentBanner ? 'bg-white' : 'bg-white/50']"
                    />
                </div>
            </div>
        </section>

        <!-- Features strip -->
        <section class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div v-for="feat in features" :key="feat.title" class="flex items-center gap-3 bg-white p-4 rounded-xl shadow-sm">
                <span class="text-2xl">{{ feat.icon }}</span>
                <div>
                    <p class="font-semibold text-sm text-gray-800">{{ feat.title }}</p>
                    <p class="text-xs text-gray-500">{{ feat.desc }}</p>
                </div>
            </div>
        </section>

        <!-- Categories -->
        <section class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Shop by Category</h2>
            <div class="grid grid-cols-3 md:grid-cols-6 gap-3">
                <RouterLink
                    v-for="cat in categories"
                    :key="cat.id"
                    :to="`/products?category=${cat.slug}`"
                    class="flex flex-col items-center gap-2 bg-white p-4 rounded-xl shadow-sm hover:shadow-md hover:border-orange-300 border border-transparent transition-all"
                >
                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center text-xl">
                        {{ getCategoryEmoji(cat.name) }}
                    </div>
                    <span class="text-xs text-center text-gray-700 font-medium">{{ cat.name }}</span>
                </RouterLink>
            </div>
        </section>

        <!-- Flash Sales -->
        <section v-if="flashSales.length" class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800">⚡ Flash Sales</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <FlashSaleBanner v-for="sale in flashSales" :key="sale.id" :sale="sale" @click="$router.push('/products/' + sale.product?.slug)" class="cursor-pointer" />
            </div>
        </section>

        <!-- Featured Products -->
        <section class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800">Featured Products</h2>
                <RouterLink to="/products?featured=1" class="text-orange-500 text-sm hover:underline">View All</RouterLink>
            </div>
            <div v-if="loadingFeatured" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <SkeletonCard v-for="i in 8" :key="i" />
            </div>
            <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <ProductCard v-for="product in featured" :key="product.id" :product="product" />
            </div>
        </section>

        <!-- All Products preview -->
        <section>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800">Latest Products</h2>
                <RouterLink to="/products" class="text-orange-500 text-sm hover:underline">View All</RouterLink>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <ProductCard v-for="product in latest" :key="product.id" :product="product" />
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import api from '@/api'
import ProductCard from '@/components/ui/ProductCard.vue'
import SkeletonCard from '@/components/ui/SkeletonCard.vue'
import FlashSaleBanner from '@/components/ui/FlashSaleBanner.vue'

const banners       = ref([])
const categories    = ref([])
const featured      = ref([])
const latest        = ref([])
const flashSales    = ref([])
const loadingFeatured = ref(true)
const currentBanner = ref(0)
let bannerInterval  = null

const features = [
    { icon: '🚚', title: 'Free Delivery', desc: 'On orders above Rs. 2000' },
    { icon: '💳', title: 'Cash on Delivery', desc: 'Pay when you receive' },
    { icon: '🔄', title: 'Easy Returns', desc: '7 day return policy' },
    { icon: '🔒', title: 'Secure Shopping', desc: '100% safe & secure' },
]

function getCategoryEmoji(name) {
    const map = { 'Electronics': '📱', 'Fashion': '👗', 'Home & Living': '🏠', 'Sports': '⚽', 'Mobile Phones': '📱', 'Laptops': '💻', 'Audio': '🎧', "Men's Clothing": '👕', "Women's Clothing": '👗', 'Footwear': '👟' }
    return map[name] || '🛍️'
}

onMounted(async () => {
    try {
        const [bannersRes, catsRes, featuredRes, latestRes] = await Promise.all([
            api.get('/banners').catch(() => ({ data: [] })),
            api.get('/categories'),
            api.get('/products/featured'),
            api.get('/products?per_page=8'),
        ])
        banners.value    = bannersRes.data
        categories.value = catsRes.data
        featured.value   = featuredRes.data
        latest.value     = latestRes.data.data
        // Load flash sales separately (non-blocking)
        api.get('/flash-sales').then(r => { flashSales.value = r.data }).catch(() => {})
    } finally {
        loadingFeatured.value = false
    }

    if (banners.value.length > 1) {
        bannerInterval = setInterval(() => {
            currentBanner.value = (currentBanner.value + 1) % banners.value.length
        }, 4000)
    }
})

onUnmounted(() => clearInterval(bannerInterval))
</script>
