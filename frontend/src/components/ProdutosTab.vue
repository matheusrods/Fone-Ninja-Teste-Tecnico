<script setup>
import { ref, inject } from 'vue'
import { apiFetch, money } from '../composables/useApi.js'
import { vAutoAnimate } from '@formkit/auto-animate/vue'
import CurrencyInput from './CurrencyInput.vue'

const props = defineProps({
    produtos: { type: Array, required: true },
    canCreate: { type: Boolean, default: false },
})

const { loading, flash, carregarTudo } = inject('app')

const form = ref({ nome: '', preco_venda: 0 })
const editando = ref(null) // { id, nome, preco_venda }

async function cadastrar() {
    loading.value = true
    try {
        await apiFetch('/produtos', { method: 'POST', body: JSON.stringify(form.value) })
        form.value = { nome: '', preco_venda: 0 }
        await carregarTudo()
        flash('success', 'Produto cadastrado com sucesso.')
    } catch (error) {
        flash('error', error.message)
    } finally {
        loading.value = false
    }
}

function iniciarEdicao(produto) {
    editando.value = { id: produto.id, nome: produto.nome, preco_venda: Number(produto.preco_venda) }
}

function cancelarEdicao() {
    editando.value = null
}

async function salvarEdicao() {
    loading.value = true
    try {
        await apiFetch(`/produtos/${editando.value.id}`, {
            method: 'PATCH',
            body: JSON.stringify({ nome: editando.value.nome, preco_venda: editando.value.preco_venda }),
        })
        editando.value = null
        await carregarTudo()
        flash('success', 'Produto atualizado.')
    } catch (error) {
        flash('error', error.message)
    } finally {
        loading.value = false
    }
}

async function excluir(produto) {
    if (!confirm(`Arquivar "${produto.nome}"? O histórico será preservado.`)) return
    loading.value = true
    try {
        await apiFetch(`/produtos/${produto.id}`, { method: 'DELETE' })
        await carregarTudo()
        flash('success', `Produto "${produto.nome}" arquivado.`)
    } catch (error) {
        flash('error', error.message)
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="split">
        <!-- Form -->
        <div v-if="canCreate" class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Novo produto</div>
                    <div class="card-subtitle">Preencha os dados do produto</div>
                </div>
            </div>

            <form @submit.prevent="cadastrar">
                <div class="form-group">
                    <label class="form-label">Nome do produto</label>
                    <input v-model.trim="form.nome" placeholder="Ex: Fone Bluetooth Pro" minlength="3" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Preço de venda (R$)</label>
                    <CurrencyInput v-model="form.preco_venda" :required="true" />
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%" :disabled="loading">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Cadastrar produto
                </button>
            </form>
        </div>

        <!-- Table -->
        <div class="card" :style="canCreate ? '' : 'grid-column:1/-1'">
            <div class="card-header">
                <div>
                    <div class="card-title">Produtos em estoque</div>
                    <div class="card-subtitle">{{ produtos.length }} produto{{ produtos.length !== 1 ? 's' : '' }} cadastrado{{ produtos.length !== 1 ? 's' : '' }}</div>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Custo médio</th>
                            <th>Preço venda</th>
                            <th style="text-align:right">Estoque</th>
                            <th v-if="canCreate" style="text-align:right">Ações</th>
                        </tr>
                    </thead>
                    <tbody v-auto-animate="{ duration: 200 }">
                        <template v-for="produto in produtos" :key="produto.id">
                            <!-- Modo edição inline -->
                            <tr v-if="editando?.id === produto.id" style="background:#f8fafc">
                                <td>
                                    <input v-model.trim="editando.nome" style="width:100%;min-width:140px" required>
                                </td>
                                <td class="muted">{{ money(produto.custo_medio) }}</td>
                                <td>
                                    <CurrencyInput v-model="editando.preco_venda" style="width:110px" :required="true" />
                                </td>
                                <td class="right">
                                    <span class="badge badge-green">{{ produto.estoque }}</span>
                                </td>
                                <td style="text-align:right">
                                    <div style="display:flex;gap:6px;justify-content:flex-end">
                                        <button class="btn btn-teal btn-sm" :disabled="loading" @click="salvarEdicao">Salvar</button>
                                        <button class="btn btn-outline btn-sm" @click="cancelarEdicao">Cancelar</button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modo visualização -->
                            <tr v-else>
                                <td><strong>{{ produto.nome }}</strong></td>
                                <td class="mono">{{ money(produto.custo_medio) }}</td>
                                <td class="mono">{{ money(produto.preco_venda) }}</td>
                                <td class="right mono">
                                    <span :class="produto.estoque === 0 ? 'badge badge-red' : produto.estoque < 5 ? 'badge badge-gray' : 'badge badge-green'">
                                        {{ produto.estoque }}
                                    </span>
                                </td>
                                <td v-if="canCreate" style="text-align:right">
                                    <div style="display:flex;gap:6px;justify-content:flex-end">
                                        <button class="btn btn-outline btn-sm" :disabled="loading" @click="iniciarEdicao(produto)" title="Editar">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            Editar
                                        </button>
                                        <button class="btn btn-danger btn-sm" :disabled="loading" @click="excluir(produto)" title="Arquivar">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M9 6V4h6v2"/></svg>
                                            Arquivar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <tr v-if="produtos.length === 0" class="empty-row">
                            <td :colspan="canCreate ? 5 : 4">Nenhum produto cadastrado.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
