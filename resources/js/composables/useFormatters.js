export function formatPrice(val) {
    return Number(val || 0).toLocaleString('en-PK')
}

export function formatDate(d, opts = {}) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('en-PK', {
        year: 'numeric', month: 'short', day: 'numeric',
        ...opts,
    })
}

export function formatDateTime(d) {
    if (!d) return '—'
    return new Date(d).toLocaleString('en-PK', {
        month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    })
}

export function statusClass(s) {
    const map = {
        pending:    'bg-yellow-100 text-yellow-700',
        confirmed:  'bg-blue-100 text-blue-700',
        processing: 'bg-indigo-100 text-indigo-700',
        shipped:    'bg-purple-100 text-purple-700',
        delivered:  'bg-green-100 text-green-700',
        cancelled:  'bg-red-100 text-red-700',
    }
    return map[s] || 'bg-gray-100 text-gray-600'
}

export function statusLabel(s) {
    return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''
}
