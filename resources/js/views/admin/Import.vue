<template>
    <div>
        <h1 class="text-xl font-bold text-gray-800 mb-6">Bulk Product Import</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Upload area + guide -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Step 1 -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-7 h-7 bg-orange-500 text-white rounded-full flex items-center justify-center text-sm font-bold shrink-0">1</span>
                        <h2 class="font-semibold text-gray-800">Download Template</h2>
                    </div>
                    <p class="text-sm text-gray-500 mb-3">Download the sample CSV template to see the expected format and fill in your products.</p>
                    <button @click="downloadTemplate" class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Download Sample CSV
                    </button>
                </div>

                <!-- Step 2 -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-7 h-7 bg-orange-500 text-white rounded-full flex items-center justify-center text-sm font-bold shrink-0">2</span>
                        <h2 class="font-semibold text-gray-800">Upload CSV File</h2>
                    </div>

                    <!-- Drop zone -->
                    <div
                        class="border-2 border-dashed rounded-xl p-10 text-center transition-colors cursor-pointer"
                        :class="isDragging ? 'border-orange-400 bg-orange-50' : 'border-gray-300 hover:border-orange-400'"
                        @click="$refs.fileInput.click()"
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="onDrop"
                    >
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-gray-600 font-medium mb-1">
                            {{ selectedFile ? selectedFile.name : 'Drag & drop your CSV here' }}
                        </p>
                        <p class="text-sm text-gray-400">or click to browse — max 5MB, up to 500 rows</p>
                        <input ref="fileInput" type="file" accept=".csv,text/csv" class="hidden" @change="onFileSelect" />
                    </div>

                    <!-- Progress bar -->
                    <div v-if="uploading" class="mt-4">
                        <div class="flex items-center justify-between text-sm text-gray-600 mb-1">
                            <span>Importing...</span>
                            <span>{{ progressLabel }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                            <div class="h-2.5 bg-orange-500 rounded-full transition-all duration-300" :style="{ width: progress + '%' }"></div>
                        </div>
                    </div>

                    <!-- Upload button -->
                    <div class="mt-4 flex items-center gap-3">
                        <button
                            @click="uploadFile"
                            :disabled="!selectedFile || uploading"
                            class="bg-orange-500 hover:bg-orange-600 disabled:opacity-50 disabled:cursor-not-allowed text-white px-6 py-2.5 rounded-xl text-sm font-medium transition-colors"
                        >
                            {{ uploading ? 'Importing...' : 'Start Import' }}
                        </button>
                        <button v-if="selectedFile" @click="clearFile" class="text-sm text-gray-400 hover:text-gray-600">Clear</button>
                    </div>

                    <p v-if="uploadError" class="mt-3 text-sm text-red-500">{{ uploadError }}</p>
                </div>

                <!-- Step 3: Results -->
                <div v-if="results" class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-7 h-7 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-bold shrink-0">3</span>
                        <h2 class="font-semibold text-gray-800">Import Results</h2>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mb-5">
                        <div class="bg-green-50 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold text-green-600">{{ results.imported }}</p>
                            <p class="text-xs text-gray-500 mt-1">Imported</p>
                        </div>
                        <div class="bg-red-50 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold text-red-500">{{ results.failed.length }}</p>
                            <p class="text-xs text-gray-500 mt-1">Failed</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold text-gray-700">{{ results.total }}</p>
                            <p class="text-xs text-gray-500 mt-1">Total Rows</p>
                        </div>
                    </div>

                    <div v-if="results.failed.length" class="mt-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Failed Rows</h3>
                        <div class="bg-red-50 rounded-xl overflow-hidden">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-red-100">
                                        <th class="text-left px-4 py-2 text-xs text-red-400 font-medium uppercase">Row</th>
                                        <th class="text-left px-4 py-2 text-xs text-red-400 font-medium uppercase">Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="fail in results.failed" :key="fail.row" class="border-b border-red-100 last:border-0">
                                        <td class="px-4 py-2 font-medium text-red-600">#{{ fail.row }}</td>
                                        <td class="px-4 py-2 text-red-500">{{ fail.reason }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Column guide -->
            <div class="space-y-4">
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <h2 class="font-semibold text-gray-800 mb-4">CSV Column Guide</h2>
                    <div class="space-y-3">
                        <div v-for="col in columns" :key="col.name" class="flex items-start gap-2">
                            <span :class="col.required ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-500'" class="text-xs px-2 py-0.5 rounded font-mono shrink-0 mt-0.5">{{ col.name }}</span>
                            <div>
                                <p class="text-xs font-medium text-gray-700">{{ col.label }}</p>
                                <p class="text-xs text-gray-400">{{ col.hint }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex gap-4 text-xs text-gray-400">
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 bg-orange-400 rounded-sm"></span>Required</span>
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 bg-gray-300 rounded-sm"></span>Optional</span>
                    </div>
                </div>

                <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 text-sm text-orange-700">
                    <p class="font-semibold mb-1">Tips</p>
                    <ul class="space-y-1 text-xs list-disc list-inside text-orange-600">
                        <li>Max 500 rows per upload</li>
                        <li>Categories & brands auto-created from slug</li>
                        <li>Slugs use lowercase with hyphens (e.g. samsung)</li>
                        <li>Leave sale_price blank if no discount</li>
                        <li>thumbnail should be a full URL</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useToast } from 'vue-toastification'
import api from '@/api'

const toast = useToast()
const selectedFile = ref(null)
const isDragging   = ref(false)
const uploading    = ref(false)
const progress     = ref(0)
const progressLabel = ref('')
const uploadError  = ref('')
const results      = ref(null)
const fileInput    = ref(null)

const columns = [
    { name: 'name',          label: 'Product Name',    hint: 'Full product name',                 required: true  },
    { name: 'category_slug', label: 'Category Slug',   hint: 'e.g. smartphones, accessories',     required: true  },
    { name: 'brand_slug',    label: 'Brand Slug',      hint: 'e.g. samsung, apple',               required: false },
    { name: 'price',         label: 'Regular Price',   hint: 'Numeric value in PKR',              required: true  },
    { name: 'sale_price',    label: 'Sale Price',      hint: 'Discounted price, leave blank if none', required: false },
    { name: 'stock',         label: 'Stock Quantity',  hint: 'Integer, 0 or more',                required: true  },
    { name: 'description',   label: 'Description',     hint: 'Full product description (HTML ok)', required: false },
    { name: 'thumbnail',     label: 'Thumbnail URL',   hint: 'Full URL to product image',         required: false },
]

function onFileSelect(e) {
    selectedFile.value = e.target.files[0] || null
    results.value = null
    uploadError.value = ''
}

function onDrop(e) {
    isDragging.value = false
    const file = e.dataTransfer.files[0]
    if (file && (file.type === 'text/csv' || file.name.endsWith('.csv'))) {
        selectedFile.value = file
        results.value = null
        uploadError.value = ''
    } else {
        uploadError.value = 'Please drop a CSV file.'
    }
}

function clearFile() {
    selectedFile.value = null
    results.value = null
    uploadError.value = ''
    if (fileInput.value) fileInput.value.value = ''
}

async function downloadTemplate() {
    try {
        const response = await api.get('/admin/import/template', { responseType: 'blob' })
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const a   = document.createElement('a')
        a.href    = url
        a.download = 'product-import-template.csv'
        a.click()
        window.URL.revokeObjectURL(url)
    } catch {
        toast.error('Failed to download template.')
    }
}

async function uploadFile() {
    if (!selectedFile.value) return
    uploading.value   = true
    progress.value    = 10
    progressLabel.value = 'Uploading...'
    uploadError.value = ''
    results.value     = null

    try {
        const fd = new FormData()
        fd.append('file', selectedFile.value)

        progress.value    = 40
        progressLabel.value = 'Processing rows...'

        const { data } = await api.post('/admin/import', fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })

        progress.value    = 100
        progressLabel.value = 'Done!'
        results.value = data

        if (data.imported > 0) {
            toast.success(`Imported ${data.imported} product(s) successfully.`)
        }
        if (data.failed.length > 0) {
            toast.warning(`${data.failed.length} row(s) failed. See results below.`)
        }
    } catch (e) {
        uploadError.value = e.response?.data?.message || 'Import failed. Please check your CSV format.'
        toast.error(uploadError.value)
    } finally {
        uploading.value = false
    }
}
</script>
