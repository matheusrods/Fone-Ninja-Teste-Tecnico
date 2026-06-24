<script setup>
import { ref, computed, inject } from 'vue'
import { apiFetch, money, formatDate } from '../composables/useApi.js'
import { vAutoAnimate } from '@formkit/auto-animate/vue'
import CurrencyInput from './CurrencyInput.vue'

const props = defineProps({
    produtos: { type: Array, required: true },
    vendas: { type: Array, required: true },
})

const { loading, flash, carregarTudo } = inject('app')

const form = ref({
    cliente: '',
    produtos: [{ id: '', quantidade: 1, preco_unitario: 0 }],
})

const estimativa = computed(() =>
    form.value.produtos.reduce((acc, item) => {
        const produto = props.produtos.find(p => p.id === Number(item.id))
        const qtd = Number(item.quantidade || 0)
        const preco = Number(item.preco_unitario || 0)
        const custo = Number(produto?.custo_medio || 0)
        acc.total += qtd * preco
        acc.lucro += qtd * (preco - custo)
        return acc
    }, { total: 0, lucro: 0 })
)

function adicionar() {
    form.value.produtos.push({ id: '', quantidade: 1, preco_unitario: '' })
}

function remover(index) {
    if (form.value.produtos.length > 1) form.value.produtos.splice(index, 1)
}

async function registrar() {
    loading.value = true
    try {
        const venda = await apiFetch('/vendas', { method: 'POST', body: JSON.stringify(form.value) })
        form.value = { cliente: '', produtos: [{ id: '', quantidade: 1, preco_unitario: '' }] }
        await carregarTudo()
        flash('success', `Venda registrada. Total ${money(venda.total)} | Lucro ${money(venda.lucro)}.`)
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
                    <div class="card-title">Registrar venda</div>
                    <div class="card-subtitle">Saída de estoque com cálculo de lucro</div>
                </div>
            </div>

            <form @submit.prevent="registrar">
                <div class="form-group">
                    <label class="form-label">Cliente</label>
                    <input v-model.trim="form.cliente" placeholder="Nome do cliente" required>
                </div>

                <label class="form-label" style="margin-bottom:8px">Itens da venda</label>
                <div class="item-rows" v-auto-animate="{ duration: 150 }">
                    <div v-for="(item, index) in form.produtos" :key="index" class="item-row">
                        <select v-model.number="item.id" required>
                            <option disabled value="">Produto</option>
                            <option v-for="p in produtos" :key="p.id" :value="p.id">
                                {{ p.nome }} ({{ p.estoque }} em estoque)
                            </option>
                        </select>
                        <input v-model.number="item.quantidade" type="number" min="1" placeholder="Qtd" required>
                        <CurrencyInput v-model="item.preco_unitario" placeholder="Preço unit." :required="true" />
                        <button type="button" class="btn-remove" @click="remover(index)" title="Remover">×</button>
                    </div>
                </div>

                <div class="summary-box">
                    <div class="summary-item">
                        <div class="summary-label">Total estimado</div>
                        <div class="summary-value">{{ money(estimativa.total) }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Lucro estimado</div>
                        <div class="summary-value" :style="estimativa.lucro < 0 ? 'color:#b91c1c' : ''">{{ money(estimativa.lucro) }}</div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-outline btn-sm" @click="adicionar">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Adicionar item
                    </button>
                    <button type="submit" class="btn btn-teal" :disabled="loading">
                        Salvar venda
                    </button>
                </div>
            </form>
        </div>

        <!-- Recent -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Últimas vendas</div>
            </div>
            <div class="recent-list">
                <div v-for="venda in vendas.slice(0, 6)" :key="venda.id" class="recent-item">
                    <div class="recent-row">
                        <span class="recent-name">{{ venda.cliente }}</span>
                        <span class="badge" :class="venda.status === 'cancelada' ? 'badge-red' : 'badge-green'">
                            {{ venda.status }}
                        </span>
                    </div>
                    <div class="recent-row">
                        <span class="recent-meta">{{ formatDate(venda.created_at) }}</span>
                        <span class="recent-amount">{{ money(venda.total) }}</span>
                    </div>
                </div>
                <p v-if="vendas.length === 0" style="padding:16px 0;text-align:center;font-size:.82rem">Nenhuma venda registrada.</p>
            </div>
        </div>
    </div>
</template>
