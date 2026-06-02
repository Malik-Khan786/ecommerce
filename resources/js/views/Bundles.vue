<template>
    <div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Bundle Deals</h1>
        <p class="text-gray-500 mb-8">Save more when you buy products together.</p>

        <!-- Loading skeleton -->
        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="i in 6" :key="i" class="animate-pulse bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="aspect-video bg-gray-100"></div>
                <div class="p-4 space-y-3">
                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                    <div class="h-3 bg-gray-100 rounded w-full"></div>
                    <div class="h-3 bg-gray-100 rounded w-2/3"></div>
                    <div class="h-8 bg-gray-100 rounded mt-2"></div>
                </div>
            </div>
        </div>

        <!-- Empty state -->
        <div v-else-if="!bundles.length" class="text-center py-20">
            <p class="text-5xl mb-4">📦</p>
            <p class="text-gray-500">No bundle deals available right now. Check back soon!</p>
        </div>

        <!-- Bundle grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <BundleCard
                v-for="bundle in bundles"
                :key="bundle.id"
                :bundle="bundle"
                :adding="adding[bundle.id]"
                @add-to-cart="addBundleToCart"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import BundleCard from '@/components/ui/BundleCard.vue'
import api from '@/api'

const cart   = useCartStore()
const auth   = useAuthStore()
const toast  = useToast()
const router = useRouter()

const bundles = ref([])
const loading = ref(true)
const adding  = ref({})

async function fetchBundles() {
    loading.value = true
    try {
        const { data } = await api.get('/bundles')
        bundles.value = data
    } catch {
        toast.error('Failed to load bundle deals.')
    } finally {
        loading.value = false
    }
}

async function addBundleToCart(bundle) {
    if (!auth.isAuthenticated) {
        toast.info('Please login to add bundles to cart.')
        router.push({ name: 'login' })
        return
    }

    adding.value[bundle.id] = true
    try {
        await api.post(`/bundles/${bundle.id}/add-to-cart`)
        await cart.fetchCart()
        toast.success(`"${bundle.name}" added to cart!`)
    } catch {
        toast.error('Failed to add bundle to cart.')
    } finally {
        adding.value[bundle.id] = false
    }
}

onMounted(fetchBundles)
</script>
