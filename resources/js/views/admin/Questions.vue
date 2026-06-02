<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-bold text-gray-800">Product Q&A</h1>
            <div class="flex gap-2">
                <select v-model="filterAnswered" @change="fetchQuestions" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">All Questions</option>
                    <option value="0">Unanswered</option>
                    <option value="1">Answered</option>
                </select>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl p-4 shadow-sm text-center">
                <p class="text-2xl font-bold text-orange-500">{{ total }}</p>
                <p class="text-sm text-gray-500">Total Questions</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm text-center">
                <p class="text-2xl font-bold text-red-400">{{ unanswered }}</p>
                <p class="text-sm text-gray-500">Unanswered</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm text-center">
                <p class="text-2xl font-bold text-green-500">{{ total - unanswered }}</p>
                <p class="text-sm text-gray-500">Answered</p>
            </div>
        </div>

        <!-- Questions list -->
        <div class="space-y-3">
            <div v-if="loading" class="space-y-3">
                <div v-for="i in 5" :key="i" class="bg-white rounded-xl h-24 animate-pulse"></div>
            </div>

            <div v-else-if="!questions.length" class="bg-white rounded-xl p-10 text-center text-gray-400">
                <p class="text-3xl mb-2">💬</p>
                <p>No questions yet.</p>
            </div>

            <div v-for="q in questions" :key="q.id" class="bg-white rounded-xl shadow-sm overflow-hidden">
                <!-- Question -->
                <div class="p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="w-6 h-6 rounded-full bg-orange-100 text-orange-600 text-xs font-bold flex items-center justify-center shrink-0">Q</span>
                                <p class="font-medium text-gray-800">{{ q.question }}</p>
                            </div>
                            <div class="ml-8 flex items-center gap-3 text-xs text-gray-400">
                                <span>👤 {{ q.user?.name || q.guest_name || 'Anonymous' }}</span>
                                <span>📦 {{ q.product?.name }}</span>
                                <span>🕐 {{ formatDate(q.created_at) }}</span>
                            </div>
                        </div>
                        <span :class="q.is_answered ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
                            class="px-2 py-0.5 rounded-full text-xs font-medium shrink-0">
                            {{ q.is_answered ? '✓ Answered' : 'Pending' }}
                        </span>
                    </div>

                    <!-- Existing answer -->
                    <div v-if="q.is_answered && q.answer" class="mt-3 ml-8 bg-green-50 rounded-xl p-3">
                        <div class="flex items-start gap-2">
                            <span class="w-6 h-6 rounded-full bg-green-100 text-green-600 text-xs font-bold flex items-center justify-center shrink-0">A</span>
                            <p class="text-sm text-gray-700">{{ q.answer }}</p>
                        </div>
                    </div>

                    <!-- Answer form -->
                    <div v-if="answeringId === q.id" class="mt-3 ml-8">
                        <textarea
                            v-model="answerText"
                            rows="3"
                            placeholder="Type your answer..."
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400"
                            autofocus
                        ></textarea>
                        <div class="flex gap-2 mt-2">
                            <button @click="submitAnswer(q)" :disabled="!answerText.trim() || saving"
                                class="bg-orange-500 hover:bg-orange-600 disabled:opacity-60 text-white text-sm px-4 py-2 rounded-xl font-medium">
                                {{ saving ? 'Saving...' : 'Post Answer' }}
                            </button>
                            <button @click="answeringId = null; answerText = ''"
                                class="border text-gray-500 text-sm px-4 py-2 rounded-xl hover:bg-gray-50">
                                Cancel
                            </button>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="mt-3 ml-8 flex gap-2">
                        <button
                            @click="startAnswer(q)"
                            class="text-xs text-orange-500 hover:text-orange-700 font-medium">
                            {{ q.is_answered ? '✏️ Edit Answer' : '💬 Write Answer' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <Pagination v-if="pagination?.last_page > 1"
                :current-page="pagination.current_page"
                :last-page="pagination.last_page"
                @change="p => { page = p; fetchQuestions() }"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { formatDate } from '@/composables/useFormatters'
import Pagination from '@/components/ui/Pagination.vue'
import api from '@/api'

const toast          = useToast()
const questions      = ref([])
const pagination     = ref(null)
const loading        = ref(true)
const filterAnswered = ref('')
const page           = ref(1)
const answeringId    = ref(null)
const answerText     = ref('')
const saving         = ref(false)

const total      = computed(() => pagination.value?.total || 0)
const unanswered = computed(() => questions.value.filter(q => !q.is_answered).length)

async function fetchQuestions() {
    loading.value = true
    try {
        const params = { page: page.value }
        if (filterAnswered.value !== '') params.is_answered = filterAnswered.value
        const { data } = await api.get('/admin/questions', { params })
        questions.value  = data.data
        pagination.value = data
    } finally {
        loading.value = false
    }
}

function startAnswer(q) {
    answeringId.value = q.id
    answerText.value  = q.answer || ''
}

async function submitAnswer(q) {
    if (!answerText.value.trim()) return
    saving.value = true
    try {
        const { data } = await api.put(`/admin/questions/${q.id}/answer`, { answer: answerText.value })
        // Update local state
        const idx = questions.value.findIndex(x => x.id === q.id)
        if (idx >= 0) questions.value[idx] = { ...questions.value[idx], ...data }
        answeringId.value = null
        answerText.value  = ''
        toast.success('Answer posted!')
    } catch {
        toast.error('Failed to post answer.')
    } finally {
        saving.value = false
    }
}

onMounted(fetchQuestions)
</script>
