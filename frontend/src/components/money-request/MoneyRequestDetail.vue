<script setup>
import { ref, watch, computed, onMounted } from "vue"
import { useToast } from "vue-toastification"
import { useUserStore } from "../../stores/user"
import axios from "axios"

const toast = useToast()
const userStore = useUserStore()

const props = defineProps({
    moneyRequest: {
        type: Object,
        required: false,
    },
    errors: {
        type: Object,
        required: false,
    },
})

const editingMoneyRequest = ref(props.moneyRequest)

const emit = defineEmits(["save", "cancel"])

watch(
    () => props.moneyRequest,
    (newMoneyRequest) => {
        editingMoneyRequest.value = newMoneyRequest
    },
    { immediate: true },
)

const save = async () => {
    emit("save", editingMoneyRequest.value)
}

const cancel = () => {
    emit("cancel")
}

</script>

<template>
    <h3 class="mt-5 mb-3">Request Money</h3>
    <div>
        <hr />
        <form @submit.prevent="save">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="to_vcard">Request money to...</label>
                        <input v-model="editingMoneyRequest.to_vcard" type="text" id="to_vcard" class="form-control"
                            required />
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="value">Amount:</label>
                    <input v-model="editingMoneyRequest.value" type="text" id="value" class="form-control"
                        autocomplete="off" required />
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="confirmation_code">Description:</label>
                    <input v-model="editingMoneyRequest.description" type="text" id="transaction_description"
                        class="form-control" />
                </div>
            </div>
            <div class="mb-3 d-flex justify-content-start" style="margin-top: 25px">
                <button type="button" class="btn btn-primary px-5" @click="save">Save</button>
                <button type="button" class="btn btn-light px-5" @click="cancel">Cancel</button>
            </div>
        </form>
    </div>
</template>
