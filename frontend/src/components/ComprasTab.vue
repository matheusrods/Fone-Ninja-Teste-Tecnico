<script setup>
import { ref, inject } from 'vue'
import { apiFetch, money, formatDate } from '../composables/useApi.js'
import { vAutoAnimate } from '@formkit/auto-animate/vue'
import CurrencyInput from './CurrencyInput.vue'

defineProps({
    produtos: { type: Array, required: true },
    compras: { type: Array, required: true },
})

const { loading, flash, carregarTudo } = inject('app')

const form = ref({
    fornecedor: '',
    produtos: [{ id: '', quantidade: 1, preco_unitario: 0 }],
})

function adicionar() {
    form.value.produtos.push({ id: '', quantidade: 1, preco_unitario: '' })
}

function remover(index) {
    if (form.value.produtos.length > 1) form.value.produtos.splice(index, 1)
}

async function registrar() {
    loading.value = true
    try {
        await apiFetch('/compras', { method: 'POST', body: JSON.stringify(form.value) })
        form.value = { fornecedor: '', produtos: [{ id: '', quantidade: 1, preco_unitario: '' }] }
        await carregarTudo()
        flash('success', 'Compra registrada com sucesso.')
    } catch (error) {
        flash('error', error.message)
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="split wide-first">
        <!-- Form -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Registrar compra</div>
                    <div class="card-subtitle">Entrada de estoque com atualização de custo médio</div>
                </div>
            </div>

            <form @submit.prevent="registrar">
                <div class="form-group">
                    <label class="form-label">Fornecedor</label>
                    <input v-model.trim="form.fornecedor" placeholder="Nome do fornecedor" required>
                </div>

                <label class="form-label" style="margin-bottom:8px">Itens da compra</label>
                <div class="item-rows" v-auto-animate="{ duration: 150 }">
                    <div v-for="(item, index) in form.produtos" :key="index" class="item-row">
                        <select v-model.number="item.id" required>
                            <option disabled value="">Produto</option>
                            <option v-for="p in produtos" :key="p.id" :value="p.id">{{ p.nome }}</option>
                        </select>
                        <input v-model.number="item.quantidade" type="number" min="1" placeholder="Qtd" required>
                        <CurrencyInput v-model="item.preco_unitario" placeholder="Preço unit." :required="true" />
                        <button type="button" class="btn-remove" @click="remover(index)" title="Remover">×</button>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-outline btn-sm" @click="adicionar">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Adicionar item
                    </button>
                    <button type="submit" class="btn btn-teal" :disabled="loading">
                        Salvar compra
                    </button>
                </div>
            </form>
        </div>

        <!-- Recent -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Últimas compras</div>
            </div>
            <div class="recent-list">
                <div v-for="compra in compras.slice(0, 6)" :key="compra.id" class="recent-item">
                    <div class="recent-row">
                        <span class="recent-name">{{ compra.fornecedor }}</span>
                        <span class="recent-amount">{{ money(compra.total) }}</span>
                    </div>
                    <span class="recent-meta">{{ formatDate(compra.created_at) }}</span>
                </div>
                <p v-if="compras.length === 0" style="padding:16px 0;text-align:center;font-size:.82rem">Nenhuma compra registrada.</p>
            </div>
        </div>
    </div>
</template>
