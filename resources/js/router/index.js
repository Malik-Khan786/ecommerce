import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
    // Storefront
    { path: '/', name: 'home', component: () => import('@/views/Home.vue') },
    { path: '/products', name: 'products', component: () => import('@/views/Products.vue') },
    { path: '/products/:slug', name: 'product', component: () => import('@/views/ProductDetail.vue') },
    { path: '/cart', name: 'cart', component: () => import('@/views/Cart.vue') },
    { path: '/checkout', name: 'checkout', component: () => import('@/views/Checkout.vue'), meta: { requiresAuth: true } },
    { path: '/orders', name: 'orders', component: () => import('@/views/Orders.vue'), meta: { requiresAuth: true } },
    { path: '/orders/:uuid', name: 'order', component: () => import('@/views/OrderDetail.vue'), meta: { requiresAuth: true } },
    { path: '/orders/:uuid/return', name: 'order.return', component: () => import('@/views/ReturnRequest.vue'), meta: { requiresAuth: true } },
    { path: '/profile', name: 'profile', component: () => import('@/views/Profile.vue'), meta: { requiresAuth: true } },
    { path: '/wishlist', name: 'wishlist', component: () => import('@/views/Wishlist.vue'), meta: { requiresAuth: true } },
    { path: '/loyalty', name: 'loyalty', component: () => import('@/views/Loyalty.vue'), meta: { requiresAuth: true } },
    { path: '/referral', name: 'referral', component: () => import('@/views/Referral.vue'), meta: { requiresAuth: true } },
    { path: '/wallet', name: 'wallet', component: () => import('@/views/Wallet.vue'), meta: { requiresAuth: true } },
    { path: '/compare', name: 'compare', component: () => import('@/views/Compare.vue') },
    { path: '/bundles', name: 'bundles', component: () => import('@/views/Bundles.vue') },

    // Order tracking — public
    { path: '/track', name: 'track', component: () => import('@/views/OrderTracking.vue') },
    { path: '/track/:uuid', name: 'track.order', component: () => import('@/views/OrderTracking.vue') },

    { path: '/order-received/:token', name: 'order.received', component: () => import('@/views/OrderReceived.vue') },

    // Auth
    { path: '/login', name: 'login', component: () => import('@/views/auth/Login.vue'), meta: { guestOnly: true } },
    { path: '/register', name: 'register', component: () => import('@/views/auth/Register.vue'), meta: { guestOnly: true } },

    // Admin
    { path: '/admin', redirect: '/admin/dashboard' },
    { path: '/admin/dashboard',   name: 'admin.dashboard',   component: () => import('@/views/admin/Dashboard.vue'),   meta: { requiresAdmin: true } },
    { path: '/admin/analytics',   name: 'admin.analytics',   component: () => import('@/views/admin/Analytics.vue'),   meta: { requiresAdmin: true } },
    { path: '/admin/products',    name: 'admin.products',    component: () => import('@/views/admin/Products.vue'),    meta: { requiresAdmin: true } },
    { path: '/admin/categories',  name: 'admin.categories',  component: () => import('@/views/admin/Categories.vue'),  meta: { requiresAdmin: true } },
    { path: '/admin/orders',      name: 'admin.orders',      component: () => import('@/views/admin/Orders.vue'),      meta: { requiresAdmin: true } },
    { path: '/admin/returns',     name: 'admin.returns',     component: () => import('@/views/admin/Returns.vue'),     meta: { requiresAdmin: true } },
    { path: '/admin/flash-sales', name: 'admin.flash-sales', component: () => import('@/views/admin/FlashSales.vue'), meta: { requiresAdmin: true } },
    { path: '/admin/visitors',    name: 'admin.visitors',    component: () => import('@/views/admin/Visitors.vue'),    meta: { requiresAdmin: true } },
    { path: '/admin/leads',       name: 'admin.leads',       component: () => import('@/views/admin/Leads.vue'),       meta: { requiresAdmin: true } },
    { path: '/admin/coupons',     name: 'admin.coupons',     component: () => import('@/views/admin/Coupons.vue'),     meta: { requiresAdmin: true } },
    { path: '/admin/import',      name: 'admin.import',      component: () => import('@/views/admin/Import.vue'),      meta: { requiresAdmin: true } },
    { path: '/admin/questions',   name: 'admin.questions',   component: () => import('@/views/admin/Questions.vue'),   meta: { requiresAdmin: true } },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior: () => ({ top: 0 }),
})

router.beforeEach((to) => {
    const auth = useAuthStore()

    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return { name: 'login', query: { redirect: to.fullPath } }
    }

    if (to.meta.requiresAdmin && !auth.isAdmin) {
        return { name: 'home' }
    }

    if (to.meta.guestOnly && auth.isAuthenticated) {
        return { name: 'home' }
    }
})

export default router
