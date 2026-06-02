<template>
    <div class="flex gap-6">
        <!-- Sidebar filters -->
        <aside class="w-56 shrink-0">
            <div class="bg-white rounded-xl p-4 shadow-sm sticky top-24">
                <h3 class="font-semibold mb-3">Filters</h3>

                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700 block mb-2">Category</label>
                    <select v-model="filters.category" @change="applyFilters" class="w-full border border-gray-300 rounded-lg text-sm px-3 py-2">
                        <option value="">All Categories</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.slug">{{ cat.name }}</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700 block mb-2">Sort By</label>
                    <select v-model="filters.sort" @change="applyFilters" class="w-full border border-gray-300 rounded-lg text-sm px-3 py-2">
                        <option value="newest">Newest</option>
                        <option value="price_asc">Price: Low to High</option>
                        <option value="price_desc">Price: High to Low</option>
                        <option value="popular">Most Popular</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700 block mb-2">Price Range (Rs.)</label>
                    <div class="flex gap-2">
                        <input v-model="filters.min_price" type="number" placeholder="Min" class="w-full border border-gray-300 rounded text-xs px-2 py-1" />
                        <input v-model="filters.max_price" type="number" placeholder="Max" class="w-full border border-gray-300 rounded text-xs px-2 py-1" />
                    </div>
                    <button @click="applyFilters" class="mt-2 w-full bg-orange-500 text-white text-xs py-1.5 rounded-lg hover:bg-orange-600">Apply</button>
                </div>

                <button @click="clearFilters" class="w-full text-xs text-gray-500 hover:text-red-500 mt-1">Clear Filters</button>
            </div>
        </aside>

        <!-- Products grid -->
        <div class="flex-1">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-xl font-bold text-gray-800">
                    {{ filters.search ? `Search: "${filters.search}"` : 'All Products' }}
                </h1>
                <span class="text-sm text-gray-500">{{ pagination?.total || 0 }} products</span>
            </div>

            <div v-if="loading" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                <SkeletonCard v-for="i in 12" :key="i" />
            </div>

            <div v-else-if="products.length === 0" class="text-center py-20 text-gray-400">
                <p class="text-5xl mb-4">🔍</p>
                <p class="font-medium">No products found.</p>
                <button @click="clearFilters" class="mt-4 text-orange-500 text-sm hover:underline">Clear all filters</button>
            </div>

            <div v-else class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                <ProductCard v-for="product in products" :key="product.id" :product="product" />
            </div>

            <Pagination
                v-if="pagination"
                :current-page="pagination.current_page"
                :last-page="pagination.last_page"
                @change="goToPage"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api'
import ProductCard from '@/components/ui/ProductCard.vue'
import SkeletonCard from '@/components/ui/SkeletonCard.vue'
import Pagination from '@/components/ui/Pagination.vue'

const route    = useRoute()
const router   = useRouter()
const products = ref([])
const categories = ref([])
const pagination = ref(null)
const loading  = ref(true)

const filters = ref({
    category:  route.query.category || '',
    search:    route.query.search   || '',
    sort:      route.query.sort     || 'newest',
    min_price: route.query.min_price || '',
    max_price: route.query.max_price || '',
    featured:  route.query.featured  || '',
    page:      route.query.page      || 1,
})

async function fetchProducts() {
    loading.value = true
    try {
        const params = Object.fromEntries(Object.entries(filters.value).filter(([, v]) => v !== ''))
        const { data } = await api.get('/products', { params })
        products.value   = data.data
        pagination.value = data
    } finally {
        loading.value = false
    }
}

function applyFilters() {
    filters.value.page = 1
    router.replace({ query: Object.fromEntries(Object.entries(filters.value).filter(([, v]) => v)) })
}

function clearFilters() {
    filters.value = { category: '', search: '', sort: 'newest', min_price: '', max_price: '', featured: '', page: 1 }
    router.replace({ query: {} })
}

function goToPage(page) {
    filters.value.page = page
    applyFilters()
}

watch(() => route.query, (q) => {
    filters.value = { ...filters.value, ...q }
    fetchProducts()
}, { immediate: true })

onMounted(async () => {
    const { data } = await api.get('/categories')
    categories.value = data.flatMap(c => [c, ...(c.children || [])])
})
</script>
