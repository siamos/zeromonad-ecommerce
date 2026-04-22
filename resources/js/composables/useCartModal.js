import { ref } from 'vue'

const isOpen = ref(false)
const productName = ref('')

export function useCartModal() {
    function openModal(name) {
        productName.value = name
        isOpen.value = true
    }

    function closeModal() {
        isOpen.value = false
    }

    return { isOpen, productName, openModal, closeModal }
}
