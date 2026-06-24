<script setup>
import { inject } from 'vue'
import { apiFetch, money, formatDate } from '../composables/useApi.js'
import { vAutoAnimate } from '@formkit/auto-animate/vue'

const props = defineProps({
    compras: { type: Array, required: true },
    vendas: { type: Array, required: true },
    isAdmin: { type: Boolean, default: false },
})

const emit = defineEmits(['venda-cancelada'])
const { loading, flash } = inject('app')

async function cancelarVenda(venda) {
    if (!confirm(`Cancelar venda para "${venda.cliente}" de ${money(venda.total)}? O estoque será revertido.`)) return
    loading.value = true
    try {
        await apiFetch(`/vendas/${venda.id}/cancelar`, { method: 'POST' })
        emit('venda-cancelada')
        flash('success', 'Venda cancelada. Estoque revertido.')
    } catch (error) {
        flash('error', error.message)
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div style="display:flex;flex-direction:column;gap:20px">

        <!-- Compras -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Histórico de compras</div>
                    <div class="card-subtitle">{{ compras.length }} registro{{ compras.length !== 1 ? 's' : '' }}</div>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fornecedor</th>
                            <th>Data</th>
                            <th>Itens</th>
                            <th style="text-align:right">Total</th>
                        </tr>
                    </thead>
                    <tbody v-auto-animate="{ duration: 200 }">
                        <tr v-for="compra in compras" :key="compra.id">
                            <td class="muted">#{{ compra.id }}</td>
                            <td><strong>{{ compra.fornecedor }}</strong></td>
                            <td class="muted">{{ formatDate(compra.created_at) }}</td>
                            <td class="muted">
                                <span v-for="(item, i) in compra.itens" :key="item.id">
                                    {{ item.produto.nome }} ({{ item.quantidade }})<span v-if="i < compra.itens.length - 1">, </span>
                                </span>
                            </td>
                            <td class="right mono"><strong>{{ money(compra.total) }}</strong></td>
                        </tr>
                        <tr v-if="compras.length === 0" class="empty-row">
                            <td colspan="5">Nenhuma compra registrada.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Vendas -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Histórico de vendas</div>
                    <div class="card-subtitle">{{ vendas.length }} registro{{ vendas.length !== 1 ? 's' : '' }}</div>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th>Itens</th>
                            <th style="text-align:right">Total</th>
                            <th style="text-align:right">Lucro</th>
                            <th v-if="isAdmin"></th>
                        </tr>
                    </thead>
                    <tbody v-auto-animate="{ duration: 200 }">
                        <tr v-for="venda in vendas" :key="venda.id">
                            <td class="muted">#{{ venda.id }}</td>
                            <td><strong>{{ venda.cliente }}</strong></td>
                            <td class="muted">{{ formatDate(venda.created_at) }}</td>
                            <td>
                                <span class="badge" :class="venda.status === 'cancelada' ? 'badge-red' : 'badge-green'">
                                    {{ venda.status === 'cancelada' ? 'Cancelada' : 'Ativa' }}
                                </span>
                            </td>
                            <td class="muted">
                                <span v-for="(item, i) in venda.itens" :key="item.id">
                                    {{ item.produto.nome }} ({{ item.quantidade }})<span v-if="i < venda.itens.length - 1">, </span>
                                </span>
                            </td>
                            <td class="right mono"><strong>{{ money(venda.total) }}</strong></td>
                            <td class="right mono" :style="Number(venda.lucro) < 0 ? 'color:#b91c1c' : 'color:#166534'">
                                {{ money(venda.lucro) }}
                            </td>
                            <td v-if="isAdmin" style="text-align:right">
                                <button
                                    v-if="venda.status !== 'cancelada'"
                                    class="btn btn-danger btn-sm"
                                    :disabled="loading"
                                    @click="cancelarVenda(venda)"
                                >
                                    Cancelar
                                </button>
                            </td>
                        </tr>
                        <tr v-if="vendas.length === 0" class="empty-row">
                            <td :colspan="isAdmin ? 8 : 7">Nenhuma venda registrada.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</template>
