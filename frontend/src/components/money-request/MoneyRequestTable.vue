<script setup>
import { ref } from "vue"
import { useToast } from "vue-toastification"

const toast = useToast()

const props = defineProps({
    moneyRequests: {
        type: Array,
        default: () => [],
    },
    pendingRequests: {
        type: Array,
        default: () => [],
    },
    vcard: {
        type: Object,
        required: false,
    },
})

const emit = defineEmits(["acceptRequest", "rejectRequest"])

const moneyRequests = ref(props.moneyRequests)
const pendingRequests = ref(props.pendingRequests)

const statusName = (requestStatus) => {
    if (requestStatus === null) {
        return "Pending"
    }
    return requestStatus == 1 ? "Accepted" : "Rejected"
}

// TODO: PEDIR CODIGO DE CONFIRMAÇAO AO CLICAR NO BOTAO DE ACEITAR!! REJEITAR NAO É PRECISO (como está neste momento está 🤢🤮)
// TODO: ATUALIZAR A LISTA QUANDO SE CLICA NO BOTAO DE ACEITAR OU REJEITAR
// TODO: VER OS MEUS PEDIDOS PENDENTES! TALVEZ TENHA DE SER NOUTRA TABELA OU WTV

const confirmationCode = ref("")

const acceptRequest = (moneyRequest) => {
    if (confirmationCode.value == "") {
        toast.error("Please enter the confirmation code")
        return
    }
    emit("acceptRequest", moneyRequest, confirmationCode.value)
}

const rejectRequest = (moneyRequest) => {
    emit("rejectRequest", moneyRequest)
}
</script>

<template>
    <div>
        <p><b>Account Balance:</b> {{ props.vcard.balance }}€</p>
        <h3>Your pending Requests</h3>
        <table class="table table-stripped">
            <thead>
                <tr>
                    <th scope="col">To</th>
                    <th scope="col">Amount</th>
                    <th scope="col">Description</th>
                    <th scope="col">Status</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="moneyRequest in pendingRequests" :key="moneyRequest.id">
                    <th scope="row">{{ moneyRequest.to_vcard }}</th>
                    <td>{{ moneyRequest.amount }}€</td>
                    <td>{{ moneyRequest.description }}</td>
                    <td
                        :class="{
                            'text-success': statusName(moneyRequest.status) === 'Accepted',
                            'text-danger': statusName(moneyRequest.status) === 'Rejected',
                            'font-weight-bold': statusName(moneyRequest.status) === 'Pending',
                        }"
                    >
                        {{ statusName(moneyRequest.status) }}
                    </td>
                    <td v-if="statusName(moneyRequest.status) == 'Pending'">
                        <button class="btn btn-danger" @click="rejectRequest(moneyRequest)">
                            Cancel
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <hr />

        <h2>Your requests</h2>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">From</th>
                    <th scope="col">Amount</th>
                    <th scope="col">Description</th>
                    <th scope="col">Status</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="moneyRequest in moneyRequests" :key="moneyRequest.id">
                    <th scope="row">{{ moneyRequest.from_vcard }}</th>
                    <td>{{ moneyRequest.amount }}€</td>
                    <td>{{ moneyRequest.description }}</td>
                    <td
                        :class="{
                            'text-success': statusName(moneyRequest.status) === 'Accepted',
                            'text-danger': statusName(moneyRequest.status) === 'Rejected',
                            'font-weight-bold': statusName(moneyRequest.status) === 'Pending',
                        }"
                    >
                        {{ statusName(moneyRequest.status) }}
                    </td>
                    <td v-if="statusName(moneyRequest.status) == 'Pending'">
                        <input v-model.lazy="confirmationCode" placeholder="Confirmation Code" />
                        <button class="btn btn-success" @click="acceptRequest(moneyRequest)">
                            Accept
                        </button>
                        <button class="btn btn-danger" @click="rejectRequest(moneyRequest)">
                            Reject
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
