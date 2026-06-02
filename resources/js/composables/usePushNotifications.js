import { ref } from 'vue'
import api from '@/api'

export function usePushNotifications() {
    const isSupported = 'PushManager' in window && 'serviceWorker' in navigator
    const isSubscribed = ref(false)

    async function subscribe() {
        if (!isSupported) return
        try {
            const reg = await navigator.serviceWorker.ready
            const sub = await reg.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: 'YOUR_VAPID_PUBLIC_KEY',
            })
            const { endpoint, keys: { p256dh, auth } } = sub.toJSON()
            await api.post('/push/subscribe', { endpoint, p256dh, auth })
            isSubscribed.value = true
        } catch (e) {
            console.warn('Push subscribe failed', e)
        }
    }

    async function unsubscribe() {
        if (!isSupported) return
        try {
            const reg = await navigator.serviceWorker.ready
            const sub = await reg.pushManager.getSubscription()
            if (sub) {
                const { endpoint } = sub.toJSON()
                await api.delete('/push/subscribe', { data: { endpoint } })
                await sub.unsubscribe()
            }
            isSubscribed.value = false
        } catch (e) {
            console.warn('Push unsubscribe failed', e)
        }
    }

    async function checkSubscriptionStatus() {
        if (!isSupported) return
        try {
            const reg = await navigator.serviceWorker.ready
            const sub = await reg.pushManager.getSubscription()
            isSubscribed.value = !!sub
        } catch (e) {
            isSubscribed.value = false
        }
    }

    return { isSupported, isSubscribed, subscribe, unsubscribe, checkSubscriptionStatus }
}
