<template>
    <div class="min-h-screen bg-gray-50">
        <component :is="layout">
            <RouterView />
        </component>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import DefaultLayout from '@/components/layouts/DefaultLayout.vue'
import AdminLayout from '@/components/layouts/AdminLayout.vue'

const route = useRoute()
const layout = computed(() =>
    route.path.startsWith('/admin') ? AdminLayout : DefaultLayout
)
</script>
