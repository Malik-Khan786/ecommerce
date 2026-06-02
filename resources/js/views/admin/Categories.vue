<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-bold text-gray-800">Categories</h1>
            <button @click="openModal()" class="bg-orange-500 text-white px-4 py-2 rounded-xl text-sm hover:bg-orange-600">+ Add Category</button>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr class="text-left text-gray-500">
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Slug</th>
                        <th class="px-4 py-3">Parent</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="cat in categories" :key="cat.id">
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">{{ cat.name }}</td>
                            <td class="px-4 py-3 text-gray-400 font-mono text-xs">{{ cat.slug }}</td>
                            <td class="px-4 py-3 text-gray-400">—</td>
                            <td class="px-4 py-3">
                                <span :class="cat.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="px-2 py-0.5 rounded-full text-xs">{{ cat.is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <button @click="openModal(cat)" class="text-blue-500 hover:text-blue-700 text-xs">Edit</button>
                                    <button @click="deleteCategory(cat)" class="text-red-400 hover:text-red-600 text-xs">Delete</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="child in cat.children" :key="child.id" class="border-b hover:bg-gray-50 bg-gray-50/50">
                            <td class="px-4 py-3 pl-8 text-gray-600">↳ {{ child.name }}</td>
                            <td class="px-4 py-3 text-gray-400 font-mono text-xs">{{ child.slug }}</td>
                            <td class="px-4 py-3 text-xs text-gray-400">{{ cat.name }}</td>
                            <td class="px-4 py-3">
                                <span :class="child.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="px-2 py-0.5 rounded-full text-xs">{{ child.is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <button @click="openModal(child)" class="text-blue-500 hover:text-blue-700 text-xs">Edit</button>
                                    <button @click="deleteCategory(child)" class="text-red-400 hover:text-red-600 text-xs">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl w-full max-w-md p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="font-bold text-lg">{{ editing ? 'Edit Category' : 'Add Category' }}</h2>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 text-xl">×</button>
                </div>
                <form @submit.prevent="saveCategory" class="space-y-3">
                    <div>
                        <label class="text-xs text-gray-600 block mb-1">Name *</label>
                        <input v-model="form.name" required class="w-full border rounded-lg px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-600 block mb-1">Parent Category</label>
                        <select v-model="form.parent_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">None (Top Level)</option>
                            <option v-for="cat in parentCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600 block mb-1">Description</label>
                        <textarea v-model="form.description" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                    </div>
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" v-model="form.is_active" /> Active
                    </label>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" :disabled="saving" class="flex-1 bg-orange-500 text-white py-2.5 rounded-xl text-sm font-medium disabled:opacity-60">
                            {{ saving ? 'Saving...' : 'Save' }}
                        </button>
                        <button type="button" @click="showModal = false" class="flex-1 border text-gray-600 py-2.5 rounded-xl text-sm">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import api from '@/api'

const toast      = useToast()
const categories = ref([])
const showModal  = ref(false)
const editing    = ref(null)
const saving     = ref(false)
const form       = ref({ name: '', parent_id: '', description: '', is_active: true })

const parentCategories = computed(() => categories.value.filter(c => !c.parent_id))

async function fetchCategories() {
    const { data } = await api.get('/admin/categories')
    categories.value = data
}

function openModal(cat = null) {
    editing.value = cat
    form.value = cat
        ? { name: cat.name, parent_id: cat.parent_id || '', description: cat.description || '', is_active: cat.is_active }
        : { name: '', parent_id: '', description: '', is_active: true }
    showModal.value = true
}

async function saveCategory() {
    saving.value = true
    try {
        if (editing.value) {
            await api.put(`/admin/categories/${editing.value.id}`, form.value)
        } else {
            await api.post('/admin/categories', form.value)
        }
        toast.success(editing.value ? 'Updated.' : 'Created.')
        showModal.value = false
        fetchCategories()
    } catch {
        toast.error('Failed to save.')
    } finally {
        saving.value = false
    }
}

async function deleteCategory(cat) {
    if (!confirm(`Delete "${cat.name}"?`)) return
    try {
        await api.delete(`/admin/categories/${cat.id}`)
        toast.success('Deleted.')
        fetchCategories()
    } catch {
        toast.error('Failed.')
    }
}

onMounted(fetchCategories)
</script>
