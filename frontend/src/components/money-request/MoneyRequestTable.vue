<script setup>
import { ref, onMounted } from "vue"

const props = defineProps({
    moneyRequests: {
        type: Array,
        default: () => [],
    },
})

const moneyRequests = ref(props.moneyRequests)

const statusName = (requestStatus) => {
    console.log(requestStatus)
    if (requestStatus === null) {
        return "Pending"
    }
    return requestStatus == 1 ? "Accepted" : "Rejected"
}

</script>

<template>
    <div>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">From</th>
                    <th scope="col">Amount</th>
                    <th scope="col">Description</th>
                    <th scope="col">Status</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="moneyRequest in moneyRequests" :key="moneyRequest.id">
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
                        <button class="btn btn-success">Accept</button>
                        <button class="btn btn-danger">Reject</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
