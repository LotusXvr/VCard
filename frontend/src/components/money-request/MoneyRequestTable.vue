<script setup>
import { ref } from "vue"

const props = defineProps({
    moneyRequests: {
        type: Array,
        default: () => [],
    },
})

const emit = defineEmits(["acceptRequest", "rejectRequest"])

const moneyRequests = ref(props.moneyRequests)

const statusName = (requestStatus) => {
    if (requestStatus === null) {
        return "Pending"
    }
    return requestStatus == 1 ? "Accepted" : "Rejected"
}

// TODO: PEDIR CODIGO DE CONFIRMAÇAO AO CLICAR NO BOTAO DE ACEITAR!! REJEITAR NAO É PRECISO
// TODO: ATUALIZAR A LISTA QUANDO SE CLICA NO BOTAO DE ACEITAR OU REJEITAR
// TODO: VER OS MEUS PEDIDOS PENDENTES! TALVEZ TENHA DE SER NOUTRA TABELA OU WTV

const acceptRequest = (moneyRequest) => {
    emit("acceptRequest", moneyRequest)
}

const rejectRequest = (moneyRequest) => {
    emit("rejectRequest", moneyRequest)
}
</script>

<template>
    <div>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>id</th>
                    <th scope="col">From</th>
                    <th scope="col">Amount</th>
                    <th scope="col">Description</th>
                    <th scope="col">Status</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="moneyRequest in moneyRequests" :key="moneyRequest.id">
                    <th scope="row">{{ moneyRequest.id }}</th>
                    <th scope="row">{{ moneyRequest.from_vcard }}</th>
                    <td>{{ moneyRequest.amount }}</td>
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
