<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
    modelValue: { type: Number, default: 0 },
    placeholder: { type: String, default: '0,00' },
    required: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

const display = ref('')
let syncing = false

function toDisplay(num) {
    return num.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

// Sincroniza display quando modelValue muda externamente (ex: reset do form)
watch(() => props.modelValue, (val) => {
    if (!syncing) {
        display.value = val ? toDisplay(val) : ''
    }
}, { immediate: true })

function handleInput(e) {
    syncing = true

    const digits = e.target.value.replace(/\D/g, '')
    const num = parseInt(digits || '0', 10) / 100

    display.value = digits ? toDisplay(num) : ''
    e.target.value = display.value

    requestAnimationFrame(() => {
        const len = display.value.length
        e.target.setSelectionRange(len, len)
        syncing = false
    })

    emit('update:modelValue', num)
}

function handleFocus(e) {
    e.target.select()
}

function handleKeydown(e) {
    // Permite: dígitos, backspace, delete, tab, setas, Ctrl/Cmd combos
    if (
        e.ctrlKey || e.metaKey ||
        ['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(e.key)
    ) return

    if (!/^\d$/.test(e.key)) e.preventDefault()
}
</script>

<template>
    <input
        type="text"
        inputmode="numeric"
        :value="display"
        :placeholder="placeholder"
        :required="required"
        @input="handleInput"
        @focus="handleFocus"
        @keydown="handleKeydown"
    >
</template>
