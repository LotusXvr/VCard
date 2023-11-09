<script setup>
import { ref, onMounted } from "vue"

const props = defineProps({
    vcard: Object,
})


const emit = defineEmits(["hide", "requestUpdateVCard"])

const editVCard = ref(Object.assign({}, props.vcard))

const save = () => {
    // Instead of updating the data (task) here, we request to update it by emiting an event
    emit("requestUpdateVCard", editVCard.value)

    // after saving we also want to hide the component
    emit("hide")
}

const cancel = () => {
    emit("hide")
}

onMounted(() => {
    // Initializing with the focus on the input

})
</script>

<template>
    <form action="#" class="d-flex">

        <div class="me-0">
            <div class="d-flex flex-column">
                <button type="submit" class="btn btn-primary" @click.prevent="save">
                    <i class="bi-check-lg" aria-hidden="true"></i> Save
                </button>
                <button type="button" class="btn btn-secondary mt-2" @click="cancel">
                    <i class="bi-x-lg" aria-hidden="true"></i> Cancel
                </button>
            </div>
        </div>
    </form>
</template>
