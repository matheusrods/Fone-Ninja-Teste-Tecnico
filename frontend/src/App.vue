<script setup>
import { ref, computed, provide, onMounted } from 'vue'
import { apiFetch, auth } from './composables/useApi.js'
import LoginForm from './components/LoginForm.vue'
import ProdutosTab from './components/ProdutosTab.vue'
import ComprasTab from './components/ComprasTab.vue'
import VendasTab from './components/VendasTab.vue'
import HistoricoTab from './components/HistoricoTab.vue'

const user = ref(null)
const tab = ref('produtos')
const loading = ref(false)
const message = ref(null)
const produtos = ref([])
const compras = ref([])
const vendas = ref([])

const isAdmin = computed(() => user.value?.role === 'admin')
const podeVerCompras = computed(() => ['admin', 'comprador'].includes(user.value?.role))
const podeVerVendas = computed(() => ['admin', 'vendedor'].includes(user.value?.role))

function flash(type, text) {
    message.value = { type, text }
    setTimeout(() => { message.value = null }, 5000)
}

async function carregarTudo() {
    loading.value = true
    try {
        const [p, c, v] = await Promise.all([
            apiFetch('/produtos'),
            podeVerCompras.value ? apiFetch('/compras?per_page=1000') : Promise.resolve([]),
            podeVerVendas.value ? apiFetch('/vendas?per_page=1000') : Promise.resolve([]),
        ])
        produtos.value = p ?? []
        compras.value = Array.isArray(c) ? c : (c?.data ?? [])
        vendas.value = Array.isArray(v) ? v : (v?.data ?? [])
    } catch (error) {
        flash('error', error.message)
    } finally {
        loading.value = false
    }
}

function onLogin({ token, user: u }) {
    auth.setToken(token)
    auth.setUser(u)
    user.value = u
    tab.value = 'produtos'
    carregarTudo()
}

async function logout() {
    try { await apiFetch('/logout', { method: 'POST' }) } finally {
        auth.clear()
        user.value = null
        produtos.value = []
        compras.value = []
        vendas.value = []
        tab.value = 'produtos'
    }
}

const tabs = computed(() => [
    { key: 'produtos', label: 'Produtos', show: true, icon: 'M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2zM4 9h16v6H4V9zm0-4h16v2H4V5z' },
    { key: 'compras', label: 'Compras', show: podeVerCompras.value, icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.5 6M17 13l1.5 6M9 19h6' },
    { key: 'vendas', label: 'Vendas', show: podeVerVendas.value, icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
    { key: 'historico', label: 'Histórico', show: true, icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
])

provide('app', { loading, flash, carregarTudo })

onMounted(() => {
    const storedUser = auth.getUser()
    if (auth.getToken() && storedUser) {
        user.value = storedUser
        carregarTudo()
    }
    window.addEventListener('auth:expired', () => {
        user.value = null
        flash('error', 'Sessão expirada. Faça login novamente.')
    })
})
</script>

<template>
    <LoginForm v-if="!user" @sucesso="onLogin" />

    <div v-else class="app-shell">
        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-brand">
                <div class="brand-icon">FN</div>
                <div>
                    <div class="brand-name">Fone Ninja</div>
                    <div class="brand-sub">ERP de Estoque</div>
                </div>
            </div>

            <div class="topbar-actions">
                <div class="user-chip">
                    <span>{{ user.name }}</span>
                    <span class="role-badge">{{ user.role }}</span>
                </div>
                <button class="btn btn-ghost" style="color:#94a3b8" :disabled="loading" @click="carregarTudo" title="Atualizar">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>
                    </svg>
                </button>
                <button class="btn btn-outline btn-sm" @click="logout">Sair</button>
            </div>
        </header>

        <!-- Nav tabs -->
        <nav class="nav-bar">
            <template v-for="t in tabs" :key="t.key">
                <button
                    v-if="t.show"
                    class="nav-btn"
                    :class="{ active: tab === t.key }"
                    @click="tab = t.key"
                >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path :d="t.icon"/>
                    </svg>
                    {{ t.label }}
                </button>
            </template>
        </nav>

        <!-- Content -->
        <div class="page">
            <Transition name="flash">
                <div v-if="message" class="flash" :class="message.type === 'success' ? 'flash-success' : 'flash-error'">
                    <svg v-if="message.type === 'success'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    {{ message.text }}
                </div>
            </Transition>

            <div style="position:relative">
                <Transition name="tab" mode="out-in">
                    <ProdutosTab v-if="tab === 'produtos'" :key="'produtos'" :produtos="produtos" :can-create="isAdmin" />
                    <ComprasTab v-else-if="tab === 'compras' && podeVerCompras" :key="'compras'" :produtos="produtos" :compras="compras" />
                    <VendasTab v-else-if="tab === 'vendas' && podeVerVendas" :key="'vendas'" :produtos="produtos" :vendas="vendas" />
                    <HistoricoTab
                        v-else-if="tab === 'historico'"
                        :key="'historico'"
                        :compras="podeVerCompras ? compras : []"
                        :vendas="podeVerVendas ? vendas : []"
                        :is-admin="isAdmin"
                        @venda-cancelada="carregarTudo"
                    />
                </Transition>
            </div>
        </div>
    </div>
</template>
