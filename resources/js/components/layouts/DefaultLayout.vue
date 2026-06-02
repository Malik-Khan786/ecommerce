<template>
    <div>
        <header class="bg-white shadow-sm sticky top-0 z-50">
            <!-- Top bar -->
            <div class="bg-orange-500 text-white text-xs text-center py-1">
                Free delivery on orders above Rs. 2,000 | Cash on Delivery Available
            </div>
            <!-- Main nav -->
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center gap-4">
                <!-- Logo -->
                <RouterLink to="/" class="text-2xl font-bold text-orange-500 shrink-0">Huzaifa Mobile</RouterLink>

                <!-- Search -->
                <form @submit.prevent="search" class="flex-1 max-w-xl">
                    <div class="flex">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search products..."
                            class="flex-1 border border-gray-300 rounded-l-lg px-4 py-2 text-sm focus:outline-none focus:border-orange-400"
                        />
                        <button type="submit" class="bg-orange-500 text-white px-4 py-2 rounded-r-lg hover:bg-orange-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </button>
                    </div>
                </form>

                <!-- Actions -->
                <div class="flex items-center gap-3 shrink-0">
                    <!-- Track Order -->
                    <RouterLink to="/track" class="flex flex-col items-center text-gray-600 hover:text-orange-500 text-xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        Track
                    </RouterLink>

                    <!-- Compare -->
                    <RouterLink to="/compare" class="flex flex-col items-center text-gray-600 hover:text-orange-500 text-xs relative">
                        <div class="relative">
                            <span class="text-xl leading-6">⚖️</span>
                            <span v-if="comparison.count > 0" class="absolute -top-2 -right-2 bg-orange-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ comparison.count }}</span>
                        </div>
                        Compare
                    </RouterLink>

                    <!-- Wishlist -->
                    <RouterLink v-if="auth.isAuthenticated" to="/wishlist" class="flex flex-col items-center text-gray-600 hover:text-orange-500 text-xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        Wishlist
                    </RouterLink>

                    <!-- Dark / Light mode toggle -->
                    <button @click="toggleDark"
                        :class="['relative flex items-center gap-1 px-2.5 py-1.5 rounded-full text-xs font-medium border transition-all shrink-0',
                            isDark
                                ? 'bg-gray-800 border-gray-600 text-yellow-300 hover:bg-gray-700'
                                : 'bg-gray-100 border-gray-200 text-gray-600 hover:bg-gray-200']"
                        :title="isDark ? 'Switch to Light mode' : 'Switch to Dark mode'">
                        <!-- Sun icon -->
                        <svg v-if="isDark" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm0 15a5 5 0 100-10 5 5 0 000 10zm8-4a1 1 0 110 2h-1a1 1 0 110-2h1zM4 13a1 1 0 110 2H3a1 1 0 110-2h1zm14.95-7.364a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM6.757 17.657a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM12 20a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.636a1 1 0 011.414 0l.707.707A1 1 0 115.757 8.757l-.707-.707a1 1 0 010-1.414zm12.728 11.314a1 1 0 011.414 0 1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707z"/>
                        </svg>
                        <!-- Moon icon -->
                        <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M21.752 15.002A9.718 9.718 0 0118 15.75 9.75 9.75 0 018.25 6c0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25 9.75 9.75 0 0012.75 21a9.753 9.753 0 009.002-5.998z"/>
                        </svg>
                        <span>{{ isDark ? 'Light' : 'Dark' }}</span>
                    </button>

                    <!-- Cart -->
                    <RouterLink to="/cart" class="flex flex-col items-center text-gray-600 hover:text-orange-500 text-xs relative">
                        <div class="relative">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span v-if="cart.count > 0" class="absolute -top-2 -right-2 bg-orange-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ cart.count }}</span>
                        </div>
                        Cart
                    </RouterLink>

                    <!-- Auth -->
                    <div v-if="auth.isAuthenticated" class="relative" ref="userMenuRef">
                        <button @click.stop="userMenuOpen = !userMenuOpen" class="flex flex-col items-center text-gray-600 hover:text-orange-500 text-xs select-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            {{ auth.user?.name?.split(' ')[0] }}
                        </button>
                        <div v-if="userMenuOpen" class="absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100" style="z-index:9999">
                            <div class="px-4 py-3 border-b">
                                <p class="text-sm font-semibold text-gray-800">{{ auth.user?.name }}</p>
                                <p class="text-xs text-gray-400 truncate">{{ auth.user?.email }}</p>
                            </div>
                            <RouterLink @click="userMenuOpen = false" to="/profile" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                My Profile
                            </RouterLink>
                            <RouterLink @click="userMenuOpen = false" to="/orders" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                My Orders
                            </RouterLink>
                            <RouterLink @click="userMenuOpen = false" to="/loyalty" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-600">
                                <span>🪙</span> Loyalty Points
                                <span v-if="auth.user?.loyalty_points" class="ml-auto text-xs bg-orange-100 text-orange-600 px-1.5 py-0.5 rounded-full">{{ auth.user.loyalty_points }}</span>
                            </RouterLink>
                            <RouterLink @click="userMenuOpen = false" to="/referral" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-600">
                                <span>🎁</span> Refer & Earn
                            </RouterLink>
                            <RouterLink @click="userMenuOpen = false" to="/wallet" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-600">
                                <span>💳</span> My Wallet
                            </RouterLink>
                            <RouterLink v-if="auth.isAdmin" @click="userMenuOpen = false" to="/admin" class="flex items-center gap-2 px-4 py-2.5 text-sm text-orange-600 hover:bg-orange-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Admin Panel
                            </RouterLink>
                            <div class="border-t mt-1">
                                <button @click="handleLogout" class="flex items-center gap-2 w-full px-4 py-2.5 text-sm text-red-500 hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Logout
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex gap-2 text-sm">
                        <RouterLink to="/login" class="text-gray-600 hover:text-orange-500">Login</RouterLink>
                        <span class="text-gray-300">|</span>
                        <RouterLink to="/register" class="text-orange-500 font-medium">Register</RouterLink>
                    </div>
                </div>
            </div>

            <!-- Mega Menu nav -->
            <nav class="bg-gray-800 text-white relative" @mouseleave="scheduleMegaClose">
                <div class="max-w-7xl mx-auto px-4 flex gap-1 overflow-x-auto">
                    <!-- Desktop items -->
                    <div
                        v-for="cat in categories"
                        :key="cat.id"
                        class="relative hidden md:block"
                        @mouseenter="openMegaMenu(cat)"
                    >
                        <RouterLink
                            :to="`/products?category=${cat.slug}`"
                            class="flex items-center gap-1 px-3 py-2.5 text-sm whitespace-nowrap hover:text-orange-400 transition-colors"
                            :class="activeCategory?.id === cat.id ? 'text-orange-400' : ''"
                            @click="closeMegaMenu"
                        >
                            {{ cat.name }}
                            <svg v-if="cat.children?.length" class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </RouterLink>
                    </div>

                    <!-- Mobile items (accordion) -->
                    <template v-for="cat in categories" :key="'mob-' + cat.id">
                        <div class="md:hidden">
                            <button
                                v-if="cat.children?.length"
                                @click="mobileToggle(cat)"
                                class="flex items-center gap-1 px-3 py-2.5 text-sm whitespace-nowrap hover:text-orange-400 transition-colors"
                                :class="mobileOpenCat?.id === cat.id ? 'text-orange-400' : ''"
                            >
                                {{ cat.name }}
                                <svg class="w-3 h-3 opacity-60 transition-transform" :class="mobileOpenCat?.id === cat.id ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <RouterLink
                                v-else
                                :to="`/products?category=${cat.slug}`"
                                class="flex items-center px-3 py-2.5 text-sm whitespace-nowrap hover:text-orange-400 transition-colors"
                            >
                                {{ cat.name }}
                            </RouterLink>
                        </div>
                    </template>
                </div>

                <!-- Mobile accordion sub-links (rendered outside the flex row) -->
                <template v-if="mobileOpenCat">
                    <div class="md:hidden bg-gray-700 max-w-7xl mx-auto px-8 py-2 flex flex-wrap gap-x-6 gap-y-1">
                        <RouterLink
                            v-for="sub in mobileOpenCat.children"
                            :key="sub.id"
                            :to="`/products?category=${sub.slug}`"
                            class="text-sm text-gray-300 hover:text-orange-400 py-1"
                            @click="mobileOpenCat = null"
                        >
                            {{ sub.name }}
                        </RouterLink>
                    </div>
                </template>

                <!-- Mega dropdown panel (desktop) -->
                <Transition name="mega">
                    <div
                        v-if="activeCategory && activeCategory.children?.length"
                        class="hidden md:block absolute left-0 right-0 top-full bg-white text-gray-800 shadow-2xl border-t-2 border-orange-400 z-50"
                        @mouseenter="cancelMegaClose"
                        @mouseleave="scheduleMegaClose"
                    >
                        <div class="max-w-7xl mx-auto px-4 py-6 grid grid-cols-4 gap-8">
                            <!-- Subcategories -->
                            <div class="col-span-2">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">{{ activeCategory.name }}</p>
                                <div class="grid grid-cols-2 gap-x-6 gap-y-1">
                                    <RouterLink
                                        v-for="sub in activeCategory.children"
                                        :key="sub.id"
                                        :to="`/products?category=${sub.slug}`"
                                        class="flex items-center gap-1.5 text-sm text-gray-700 hover:text-orange-500 py-1 group"
                                        @click="closeMegaMenu"
                                    >
                                        <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-orange-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                        {{ sub.name }}
                                    </RouterLink>
                                </div>
                                <RouterLink
                                    :to="`/products?category=${activeCategory.slug}`"
                                    class="inline-flex items-center gap-1 mt-4 text-xs font-semibold text-orange-500 hover:underline"
                                    @click="closeMegaMenu"
                                >
                                    View all {{ activeCategory.name }}
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </RouterLink>
                            </div>

                            <!-- Featured placeholders -->
                            <div class="col-span-2">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Featured</p>
                                <div class="grid grid-cols-2 gap-4">
                                    <RouterLink
                                        v-for="(fp, i) in featuredPlaceholders"
                                        :key="i"
                                        :to="`/products?category=${activeCategory.slug}`"
                                        class="group rounded-xl overflow-hidden border border-gray-100 hover:border-orange-200 hover:shadow-md transition-all"
                                        @click="closeMegaMenu"
                                    >
                                        <div class="aspect-square bg-gradient-to-br from-orange-50 to-amber-100 flex items-center justify-center text-5xl">
                                            {{ fp.icon }}
                                        </div>
                                        <div class="p-2.5">
                                            <p class="text-xs font-medium text-gray-700 group-hover:text-orange-500 line-clamp-1">{{ fp.label }}</p>
                                            <p class="text-xs text-orange-400 mt-0.5">Shop now →</p>
                                        </div>
                                    </RouterLink>
                                </div>
                            </div>
                        </div>
                    </div>
                </Transition>
            </nav>
        </header>

        <main class="max-w-7xl mx-auto px-4 py-6">
            <slot />
        </main>

        <BackToTop />

        <!-- Floating WhatsApp button -->
        <a href="https://wa.me/923090046009?text=Hi%20Huzaifa%20Mobile%2C%20I%20need%20help!" target="_blank" rel="noopener"
            class="fixed bottom-20 right-6 z-50 w-14 h-14 bg-green-500 hover:bg-green-600 text-white rounded-full shadow-xl flex items-center justify-center transition-all hover:scale-110"
            title="Chat on WhatsApp">
            <svg class="w-7 h-7 fill-white" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
            <!-- Pulse ring -->
            <span class="absolute w-full h-full rounded-full bg-green-400 opacity-30 animate-ping"></span>
        </a>

        <!-- Replace YOUR_TAWK_ID with your Tawk.to property ID from tawk.to dashboard -->
        <LiveChat />

        <footer class="bg-gray-900 text-gray-400 mt-12">
            <div class="max-w-7xl mx-auto px-4 py-10 grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-white font-bold text-lg mb-3">Huzaifa Mobile</h3>
                    <p class="text-sm">Pakistan's best online shopping destination. Quality products at the best prices.</p>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-3">Quick Links</h4>
                    <ul class="space-y-2 text-sm">
                        <li><RouterLink to="/products" class="hover:text-white">All Products</RouterLink></li>
                        <li><RouterLink to="/cart" class="hover:text-white">Cart</RouterLink></li>
                        <li><RouterLink to="/orders" class="hover:text-white">My Orders</RouterLink></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-3">Customer Service</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="tel:03001234567" class="hover:text-white flex items-center gap-1">📞 0300-1234567</a></li>
                        <li><a href="mailto:support@huzaifamobile.pk" class="hover:text-white flex items-center gap-1">✉️ support@huzaifamobile.pk</a></li>
                        <li class="flex items-center gap-1">🕘 Hours: 9am - 9pm</li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-3">Follow Us</h4>
                    <div class="flex gap-3 mb-4">
                        <!-- WhatsApp -->
                        <a href="https://wa.me/923090046009?text=Hi%20Huzaifa%20Mobile%2C%20I%20need%20help%20with%20my%20order." target="_blank" rel="noopener" class="w-9 h-9 bg-gray-700 hover:bg-green-500 rounded-lg flex items-center justify-center transition-colors" title="WhatsApp">
                            <svg class="w-5 h-5 fill-white" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </a>
                        <a href="https://www.tiktok.com/@ranatanveer462" target="_blank" rel="noopener" class="w-9 h-9 bg-gray-700 hover:bg-pink-600 rounded-lg flex items-center justify-center transition-colors" title="TikTok">
                            <svg class="w-5 h-5 fill-white" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.32 6.32 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.2 8.2 0 004.79 1.53V6.75a4.85 4.85 0 01-1.02-.06z"/></svg>
                        </a>
                        <a href="https://www.youtube.com/@bestnow.pk" target="_blank" rel="noopener" class="w-9 h-9 bg-gray-700 hover:bg-red-600 rounded-lg flex items-center justify-center transition-colors" title="YouTube">
                            <svg class="w-5 h-5 fill-white" viewBox="0 0 24 24"><path d="M23.5 6.19a3.02 3.02 0 00-2.12-2.14C19.54 3.5 12 3.5 12 3.5s-7.54 0-9.38.55A3.02 3.02 0 00.5 6.19C0 8.04 0 12 0 12s0 3.96.5 5.81a3.02 3.02 0 002.12 2.14C4.46 20.5 12 20.5 12 20.5s7.54 0 9.38-.55a3.02 3.02 0 002.12-2.14C24 15.96 24 12 24 12s0-3.96-.5-5.81zM9.75 15.5v-7l6.5 3.5-6.5 3.5z"/></svg>
                        </a>
                        <a href="https://www.instagram.com/bestnow.pk" target="_blank" rel="noopener" class="w-9 h-9 bg-gray-700 hover:bg-gradient-to-br hover:from-purple-600 hover:to-pink-500 rounded-lg flex items-center justify-center transition-colors" title="Instagram">
                            <svg class="w-5 h-5 fill-white" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="https://www.facebook.com/bestnow.pk" target="_blank" rel="noopener" class="w-9 h-9 bg-gray-700 hover:bg-blue-600 rounded-lg flex items-center justify-center transition-colors" title="Facebook">
                            <svg class="w-5 h-5 fill-white" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                    </div>
                    <h4 class="text-white font-semibold mb-2 text-sm">We Accept</h4>
                    <div class="flex gap-2 flex-wrap text-xs">
                        <span class="bg-gray-700 px-2 py-1 rounded">💵 Cash on Delivery</span>
                        <span class="bg-gray-700 px-2 py-1 rounded">📱 JazzCash</span>
                        <span class="bg-gray-700 px-2 py-1 rounded">💚 EasyPaisa</span>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 text-center py-4 text-sm">
                © 2026 Huzaifa Mobile. All rights reserved.
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import BackToTop from '@/components/ui/BackToTop.vue'
import LiveChat from '@/components/ui/LiveChat.vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import { useComparisonStore } from '@/stores/comparison'
import { useDarkMode } from '@/composables/useDarkMode'
import api from '@/api'

const { isDark, toggle: toggleDark } = useDarkMode()

const auth       = useAuthStore()
const cart       = useCartStore()
const comparison = useComparisonStore()
const router     = useRouter()

const searchQuery  = ref('')
const categories   = ref([])
const userMenuOpen = ref(false)
const userMenuRef  = ref(null)

// ── Mega Menu ────────────────────────────────────────────────────
const activeCategory = ref(null)
const mobileOpenCat  = ref(null)
let megaCloseTimer   = null

// Hardcoded placeholder featured product tiles per mega menu panel
const featuredPlaceholders = [
    { icon: '📱', label: 'Top Picks' },
    { icon: '🔥', label: 'Best Sellers' },
]

function openMegaMenu(cat) {
    cancelMegaClose()
    activeCategory.value = cat
}

function closeMegaMenu() {
    activeCategory.value = null
}

function scheduleMegaClose() {
    megaCloseTimer = setTimeout(() => {
        activeCategory.value = null
    }, 150)
}

function cancelMegaClose() {
    if (megaCloseTimer) {
        clearTimeout(megaCloseTimer)
        megaCloseTimer = null
    }
}

function mobileToggle(cat) {
    mobileOpenCat.value = mobileOpenCat.value?.id === cat.id ? null : cat
}
// ────────────────────────────────────────────────────────────────

function handleClickOutside(e) {
    if (userMenuOpen.value && userMenuRef.value && !userMenuRef.value.contains(e.target)) {
        userMenuOpen.value = false
    }
}

onMounted(async () => {
    try {
        const { data } = await api.get('/categories')
        categories.value = data
    } catch {}

    if (auth.isAuthenticated) {
        cart.fetchCart()
    }

    document.addEventListener('click', handleClickOutside)

    // Register PWA service worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(() => {})
    }
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
    if (megaCloseTimer) clearTimeout(megaCloseTimer)
})

function search() {
    if (searchQuery.value.trim()) {
        router.push({ name: 'products', query: { search: searchQuery.value } })
    }
}

async function handleLogout() {
    userMenuOpen.value = false
    await auth.logout()
    router.push({ name: 'home' })
}
</script>

<style scoped>
.mega-enter-active,
.mega-leave-active {
    transition: opacity 0.15s ease, transform 0.15s ease;
}
.mega-enter-from,
.mega-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}
</style>
