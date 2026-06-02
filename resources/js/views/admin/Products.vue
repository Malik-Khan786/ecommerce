<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-bold text-gray-800">Products</h1>
            <button @click="openModal()" class="bg-orange-500 text-white px-4 py-2 rounded-xl text-sm hover:bg-orange-600">+ Add Product</button>
        </div>

        <!-- Search -->
        <div class="bg-white rounded-xl p-4 shadow-sm mb-4">
            <input v-model="search" @input="fetchProducts" type="text" placeholder="Search products..." class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-64 focus:outline-none focus:border-orange-400" />
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr class="text-left text-gray-500">
                        <th class="px-4 py-3">Product</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Price</th>
                        <th class="px-4 py-3">Stock</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading">
                        <td colspan="6" class="text-center py-8 text-gray-400">Loading...</td>
                    </tr>
                    <tr v-for="product in products" :key="product.id" class="border-b last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <img :src="product.thumbnail" class="w-10 h-10 object-cover rounded-lg" />
                                <div>
                                    <p class="font-medium text-gray-800 line-clamp-1">{{ product.name }}</p>
                                    <p class="text-xs text-gray-400">{{ product.brand?.name }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ product.category?.name }}</td>
                        <td class="px-4 py-3">
                            <span class="font-medium">Rs. {{ formatPrice(product.sale_price ?? product.price) }}</span>
                            <span v-if="product.sale_price" class="text-xs text-gray-400 line-through ml-1">{{ formatPrice(product.price) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span :class="product.stock < 10 ? 'text-red-500' : 'text-green-600'" class="font-medium">{{ product.stock }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span :class="product.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="px-2 py-0.5 rounded-full text-xs">
                                {{ product.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <button @click="openModal(product)" class="text-blue-500 hover:text-blue-700 text-xs">Edit</button>
                                <button @click="deleteProduct(product)" class="text-red-400 hover:text-red-600 text-xs">Delete</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination?.last_page > 1" class="flex justify-center gap-2 mt-4">
            <button v-for="p in pagination.last_page" :key="p" @click="page = p; fetchProducts()"
                :class="['px-3 py-1 rounded text-sm', p === page ? 'bg-orange-500 text-white' : 'bg-white border text-gray-600 hover:bg-gray-50']">{{ p }}</button>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="font-bold text-lg">{{ editing ? 'Edit Product' : 'Add Product' }}</h2>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 text-xl">×</button>
                </div>

                <form @submit.prevent="saveProduct" class="space-y-3">
                    <div>
                        <label class="text-xs text-gray-600 block mb-1">Name *</label>
                        <input v-model="form.name" required class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-400" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-600 block mb-1">Category *</label>
                            <select v-model="form.category_id" required class="w-full border rounded-lg px-3 py-2 text-sm">
                                <option value="">Select...</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-600 block mb-1">Brand</label>
                            <select v-model="form.brand_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                                <option value="">None</option>
                                <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="text-xs text-gray-600 block mb-1">Price *</label>
                            <input v-model="form.price" type="number" required class="w-full border rounded-lg px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="text-xs text-gray-600 block mb-1">Sale Price</label>
                            <input v-model="form.sale_price" type="number" class="w-full border rounded-lg px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="text-xs text-gray-600 block mb-1">Stock *</label>
                            <input v-model="form.stock" type="number" required class="w-full border rounded-lg px-3 py-2 text-sm" />
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600 block mb-1">SKU</label>
                        <input v-model="form.sku" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="e.g. SAM-S25-BLK" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-600 block mb-1">Short Description</label>
                        <textarea v-model="form.short_description" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600 block mb-1">Full Description</label>
                        <textarea v-model="form.description" rows="4" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Full product details (HTML allowed)"></textarea>
                    </div>
                    <!-- Image Upload -->
                    <div>
                        <label class="text-xs text-gray-600 block mb-1">Product Image</label>

                        <!-- Preview current image -->
                        <div v-if="form.thumbnail" class="relative w-full h-40 mb-2 bg-gray-100 rounded-lg overflow-hidden">
                            <img :src="form.thumbnail" :alt="form.name || 'Product thumbnail'" class="w-full h-full object-contain" />
                            <button type="button" @click="removeImage"
                                class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                                ✕
                            </button>
                        </div>

                        <!-- Upload area -->
                        <div v-if="!form.thumbnail"
                            class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-orange-400 transition-colors"
                            @click="$refs.imgInput.click()"
                            @dragover.prevent
                            @drop.prevent="onDrop">
                            <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-sm text-gray-500">Click or drag & drop to upload</p>
                            <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP — max 2MB</p>
                        </div>
                        <button v-else type="button" @click="$refs.imgInput.click()"
                            class="mt-1 text-xs text-orange-500 hover:underline">
                            Change image
                        </button>

                        <input ref="imgInput" type="file" accept="image/*" class="hidden" @change="onFileSelect" />

                        <!-- Upload progress -->
                        <div v-if="uploading" class="mt-2 flex items-center gap-2 text-xs text-gray-500">
                            <svg class="w-4 h-4 animate-spin text-orange-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            Uploading...
                        </div>
                        <p v-if="uploadError" class="mt-1 text-xs text-red-500">{{ uploadError }}</p>
                    </div>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" v-model="form.is_active" class="rounded" /> Active
                        </label>
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" v-model="form.is_featured" class="rounded" /> Featured
                        </label>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600 block mb-1">Schedule to go live at <span class="text-gray-400">(leave blank for now)</span></label>
                        <input v-model="form.scheduled_at" type="datetime-local" class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-400" />
                        <p class="text-xs text-gray-400 mt-1">If set, the product will automatically become active at this time (requires is_active = OFF).</p>
                    </div>
                    <div v-if="formErrors.length" class="bg-red-50 rounded-lg p-2">
                        <p v-for="err in formErrors" :key="err" class="text-red-600 text-xs">{{ err }}</p>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" :disabled="saving" class="flex-1 bg-orange-500 hover:bg-orange-600 disabled:opacity-60 text-white py-2.5 rounded-xl text-sm font-medium">
                            {{ saving ? 'Saving...' : 'Save' }}
                        </button>
                        <button type="button" @click="showModal = false" class="flex-1 border text-gray-600 hover:bg-gray-50 py-2.5 rounded-xl text-sm">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import api from '@/api'

const toast = useToast()
const products   = ref([])
const categories = ref([])
const brands     = ref([])
const pagination = ref(null)
const loading    = ref(true)
const search     = ref('')
const page       = ref(1)
const showModal  = ref(false)
const editing    = ref(null)
const saving     = ref(false)
const formErrors = ref([])
const uploading  = ref(false)
const uploadError = ref('')
const imgInput   = ref(null)

async function uploadFile(file) {
    if (!file) return
    uploadError.value = ''
    uploading.value   = true
    try {
        const fd = new FormData()
        fd.append('image', file)
        const { data } = await api.post('/admin/upload', fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
        form.value.thumbnail = data.url
    } catch (e) {
        uploadError.value = e.response?.data?.message || 'Upload failed. Max size is 2MB.'
    } finally {
        uploading.value = false
    }
}

function onFileSelect(e) {
    uploadFile(e.target.files[0])
}

function onDrop(e) {
    uploadFile(e.dataTransfer.files[0])
}

function removeImage() {
    form.value.thumbnail = ''
    uploadError.value    = ''
    if (imgInput.value) imgInput.value.value = ''
}

const defaultForm = () => ({ name: '', category_id: '', brand_id: '', price: '', sale_price: '', stock: '', sku: '', short_description: '', description: '', thumbnail: '', is_active: true, is_featured: false })
const form = ref(defaultForm())

function formatPrice(val) { return Number(val || 0).toLocaleString('en-PK') }

async function fetchProducts() {
    loading.value = true
    try {
        const { data } = await api.get('/admin/products', { params: { search: search.value, page: page.value } })
        products.value   = data.data
        pagination.value = data
    } finally {
        loading.value = false
    }
}

function openModal(product = null) {
    editing.value    = product
    formErrors.value = []
    form.value = product
        ? { ...product, brand_id: product.brand_id || '' }
        : defaultForm()
    showModal.value  = true
}

async function saveProduct() {
    saving.value     = true
    formErrors.value = []
    try {
        if (editing.value) {
            await api.put(`/admin/products/${editing.value.id}`, form.value)
            toast.success('Product updated.')
        } else {
            await api.post('/admin/products', form.value)
            toast.success('Product created.')
        }
        showModal.value = false
        fetchProducts()
    } catch (e) {
        formErrors.value = e.response?.data?.errors
            ? Object.values(e.response.data.errors).flat()
            : [e.response?.data?.message || 'Error saving.']
    } finally {
        saving.value = false
    }
}

async function deleteProduct(product) {
    if (!confirm(`Delete "${product.name}"?`)) return
    try {
        await api.delete(`/admin/products/${product.id}`)
        toast.success('Deleted.')
        fetchProducts()
    } catch {
        toast.error('Failed to delete.')
    }
}

onMounted(async () => {
    fetchProducts()
    const [cRes, bRes] = await Promise.all([
        api.get('/admin/categories'),
        api.get('/brands').catch(() => ({ data: [] })),
    ])
    categories.value = cRes.data.flatMap(c => [c, ...(c.children || [])])
    brands.value     = bRes.data
})
</script>
