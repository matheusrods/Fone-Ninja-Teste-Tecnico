const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

const TOKEN_KEY = 'fone_ninja_token'
const USER_KEY = 'fone_ninja_user'

export const auth = {
    getToken: () => localStorage.getItem(TOKEN_KEY),
    setToken: (token) => localStorage.setItem(TOKEN_KEY, token),
    getUser: () => { try { return JSON.parse(localStorage.getItem(USER_KEY)) } catch { return null } },
    setUser: (user) => localStorage.setItem(USER_KEY, JSON.stringify(user)),
    clear: () => { localStorage.removeItem(TOKEN_KEY); localStorage.removeItem(USER_KEY) },
}

export async function apiFetch(path, options = {}) {
    const token = auth.getToken()

    const response = await fetch(`${apiUrl}${path}`, {
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
        },
        ...options,
    })

    if (response.status === 401) {
        auth.clear()
        window.dispatchEvent(new CustomEvent('auth:expired'))
        throw new Error('Sessao expirada. Faca login novamente.')
    }

    // 204 No Content — sem body (ex: DELETE produto)
    if (response.status === 204) {
        return null
    }

    const text = await response.text()
    const payload = text ? JSON.parse(text) : null

    if (!response.ok) {
        const detail = payload?.errors
            ? Object.values(payload.errors).flat().join(' ')
            : payload?.message
        throw new Error(detail || 'Nao foi possivel concluir a operacao.')
    }

    return payload?.data ?? payload
}

export async function apiLogin(email, password) {
    const response = await fetch(`${apiUrl}/login`, {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password }),
    })

    const payload = await response.json()

    if (!response.ok) {
        const detail = payload.errors
            ? Object.values(payload.errors).flat().join(' ')
            : payload.message
        throw new Error(detail || 'Credenciais invalidas.')
    }

    return payload
}

export function money(value) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(value || 0))
}

export function formatDate(value) {
    return new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(value))
}
