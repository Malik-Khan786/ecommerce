<template>
    <div>
        <!-- Loading skeleton -->
        <div v-if="loading" class="space-y-4">
            <div v-for="i in 3" :key="i" class="animate-pulse">
                <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                <div class="h-3 bg-gray-100 rounded w-1/2"></div>
            </div>
        </div>

        <div v-else>
            <!-- Q&A List -->
            <div v-if="questions.length" class="space-y-4 mb-8">
                <div
                    v-for="q in questions"
                    :key="q.id"
                    class="border border-gray-100 rounded-xl overflow-hidden"
                >
                    <!-- Question -->
                    <button
                        @click="toggle(q.id)"
                        class="w-full text-left px-4 py-3 flex items-start gap-3 hover:bg-gray-50 transition-colors"
                    >
                        <span class="shrink-0 w-6 h-6 rounded-full bg-orange-100 text-orange-600 text-xs font-bold flex items-center justify-center mt-0.5">Q</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800">{{ q.question }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ q.user?.name || q.guest_name || 'Anonymous' }} ·
                                {{ formatDate(q.created_at) }}
                                <span v-if="q.is_answered" class="ml-2 inline-flex items-center gap-0.5 text-green-600 bg-green-50 px-1.5 py-0.5 rounded-full text-xs">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Answered
                                </span>
                            </p>
                        </div>
                        <svg
                            class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200"
                            :class="expanded.has(q.id) ? 'rotate-180' : ''"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Answer -->
                    <div v-if="expanded.has(q.id) && q.is_answered" class="px-4 py-3 bg-green-50 border-t flex gap-3">
                        <span class="shrink-0 w-6 h-6 rounded-full bg-green-100 text-green-600 text-xs font-bold flex items-center justify-center mt-0.5">A</span>
                        <p class="text-sm text-gray-700">{{ q.answer }}</p>
                    </div>
                    <div v-else-if="expanded.has(q.id) && !q.is_answered" class="px-4 py-3 bg-gray-50 border-t">
                        <p class="text-sm text-gray-400 italic">No answer yet. We'll get back to you soon!</p>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="text-center py-8 text-gray-400">
                <p class="text-4xl mb-2">💬</p>
                <p>No questions yet. Be the first to ask!</p>
            </div>

            <!-- Ask a Question form -->
            <div class="bg-gray-50 rounded-xl p-5">
                <h4 class="font-semibold text-gray-800 mb-4">Ask a Question</h4>
                <form @submit.prevent="submitQuestion" class="space-y-3">
                    <div v-if="!auth.isAuthenticated">
                        <input
                            v-model="form.guest_name"
                            type="text"
                            placeholder="Your name *"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-400"
                            required
                        />
                    </div>
                    <textarea
                        v-model="form.question"
                        rows="3"
                        placeholder="Type your question here... (max 500 characters)"
                        maxlength="500"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-400 resize-none"
                        required
                    ></textarea>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400">{{ form.question.length }}/500</span>
                        <button
                            type="submit"
                            :disabled="submitting"
                            class="bg-orange-500 hover:bg-orange-600 disabled:opacity-60 text-white text-sm px-5 py-2 rounded-lg transition-colors"
                        >
                            {{ submitting ? 'Submitting...' : 'Submit Question' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { useAuthStore } from '@/stores/auth'
import { formatDate } from '@/composables/useFormatters'
import api from '@/api'

const props = defineProps({
    productSlug: { type: String, required: true },
})

const auth     = useAuthStore()
const toast    = useToast()

const questions = ref([])
const loading   = ref(true)
const submitting = ref(false)
const expanded  = ref(new Set())

const form = reactive({
    question:   '',
    guest_name: '',
})

function toggle(id) {
    if (expanded.value.has(id)) {
        expanded.value.delete(id)
    } else {
        expanded.value.add(id)
    }
    // trigger reactivity
    expanded.value = new Set(expanded.value)
}

async function fetchQuestions() {
    loading.value = true
    try {
        const { data } = await api.get(`/products/${props.productSlug}/questions`)
        questions.value = data
    } catch {
        // silent
    } finally {
        loading.value = false
    }
}

async function submitQuestion() {
    if (!form.question.trim()) return
    submitting.value = true
    try {
        const payload = { question: form.question }
        if (!auth.isAuthenticated) {
            payload.guest_name = form.guest_name
        }
        const { data } = await api.post(`/products/${props.productSlug}/questions`, payload)
        questions.value.unshift(data)
        form.question   = ''
        form.guest_name = ''
        toast.success('Your question has been submitted!')
    } catch (err) {
        const msg = err.response?.data?.message || 'Failed to submit question.'
        toast.error(msg)
    } finally {
        submitting.value = false
    }
}

onMounted(fetchQuestions)
</script>
