<template>
    <div></div>
</template>

<script setup>
import { onMounted } from 'vue'

onMounted(() => {
    // Replace 'YOUR_TAWK_PROPERTY_ID' and 'YOUR_TAWK_WIDGET_ID' with values from your Tawk.to dashboard
    // Go to tawk.to → Admin → Property Settings → copy the embed code
    // Example: https://embed.tawk.to/64abc123def456/1h2i3j4k5
    const TAWK_PROPERTY_ID = 'YOUR_TAWK_PROPERTY_ID'
    const TAWK_WIDGET_ID   = 'default'

    if (TAWK_PROPERTY_ID === 'YOUR_TAWK_PROPERTY_ID') {
        // Tawk.to not configured yet — skip loading
        return
    }

    window.Tawk_API  = window.Tawk_API  || {}
    window.Tawk_LoadStart = new Date()

    const s1 = document.createElement('script')
    const s0 = document.getElementsByTagName('script')[0]
    s1.async = true
    s1.src   = `https://embed.tawk.to/${TAWK_PROPERTY_ID}/${TAWK_WIDGET_ID}`
    s1.charset     = 'UTF-8'
    s1.setAttribute('crossorigin', '*')
    s0.parentNode.insertBefore(s1, s0)
})
</script>
