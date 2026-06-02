import axios from 'axios'

// Persistent guest cart token — survives page refresh
function getCartToken() {
    let t = localStorage.getItem('cart_token')
    if (!t) {
        t = crypto.randomUUID()
        localStorage.setItem('cart_token', t)
    }
    return t
}

const api = axios.create({
    baseURL: '/api',
    headers: { 'Accept': 'application/json' },
})

api.interceptors.request.use((config) => {
    const token = localStorage.getItem('token')
    if (token) config.headers.Authorization = `Bearer ${token}`
    config.headers['X-Cart-Token'] = getCartToken()
    return config
})

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('token')
            localStorage.removeItem('user')
            window.location.href = '/login'
        }
        return Promise.reject(error)
    }
)

export default api
