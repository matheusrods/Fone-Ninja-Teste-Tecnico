<script setup>
import { ref } from 'vue'
import { apiLogin } from '../composables/useApi.js'

const emit = defineEmits(['sucesso'])

const form = ref({ email: '', password: '' })
const loading = ref(false)
const error = ref(null)

async function entrar() {
    loading.value = true
    error.value = null
    try {
        const data = await apiLogin(form.value.email, form.value.password)
        emit('sucesso', data)
    } catch (e) {
        error.value = e.message
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="login-page">
        <form class="login-card" @submit.prevent="entrar">
            <div class="login-brand">
                <div class="login-icon">FN</div>
                <h1 class="login-title">Fone Ninja</h1>
                <p class="login-sub">ERP de Estoque — faça login para continuar</p>
            </div>

            <div v-if="error" class="flash flash-error">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {{ error }}
            </div>

            <div class="form-group">
                <label class="form-label">E-mail</label>
                <input
                    v-model.trim="form.email"
                    type="email"
                    placeholder="admin@foneninja.com"
                    required
                    autocomplete="email"
                >
            </div>

            <div class="form-group">
                <label class="form-label">Senha</label>
                <input
                    v-model="form.password"
                    type="password"
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" class="login-submit" :disabled="loading">
                {{ loading ? 'Entrando...' : 'Entrar' }}
            </button>
        </form>
    </div>
</template>
