import { ref, watchEffect } from 'vue'

// Read initial value immediately (safe — no lifecycle hooks)
function getInitial() {
    try {
        const saved = localStorage.getItem('darkMode')
        if (saved !== null) return saved === '1'
        return window.matchMedia('(prefers-color-scheme: dark)').matches
    } catch {
        return false
    }
}

const isDark = ref(getInitial())

// Apply immediately on module load
function applyClass(dark) {
    document.documentElement.classList.toggle('dark', dark)
    try { localStorage.setItem('darkMode', dark ? '1' : '0') } catch {}
}

applyClass(isDark.value)

export function useDarkMode() {
    function toggle() {
        isDark.value = !isDark.value
        applyClass(isDark.value)
    }

    return { isDark, toggle }
}
