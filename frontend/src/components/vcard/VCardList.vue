<script setup>
import VCard from './VCard.vue'

const props = defineProps({
    vcards: Array,
    readonly: Boolean
})

const emit = defineEmits([
    'requestRemoveVCardFromList',
    'requestUpdateVCard'
])

const handleRequestToRemoveVCard = (vcard) => {
    emit('requestRemoveVCardFromList', vcard)
}

const updateVCard = (vcard) => {
    emit("requestUpdateVCard", vcard)
}
</script>



<template>
    <div>
        <ul class="list-group">
            <VCard v-for="vcard in vcards" :key="vcard.id" 
                :vcard="vcard" 
                :readonly="readonly" 
                @requestRemoveVCardFromList="handleRequestToRemoveVCard"
                @requestUpdateVCard="updateVCard"></VCard>
        </ul>
    </div>
</template>