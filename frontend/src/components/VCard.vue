<script setup>
import { ref } from "vue"
import TaskDetail from "./TaskDetail.vue"
// import axios from "axios"
// import config from "../utils/config"

const vcardEdit = ref(null)

// const props = defineProps({
//     vcard: Object,
//     readonly: Boolean,
// })

const emit = defineEmits(["requestRemoveVCardFromList", "requestUpdateVCard"])

const clickToDeleteTask = (vcard) => {
    emit("requestRemoveVCardFromList", vcard)
}

const editTask = (task) => {
    vcardEdit.value = task
}

const closeEdit = () => {
    vcardEdit.value = null
}

const detailRequestedUpdateTask = (task) => {
    vcardEdit.value = null
    emit("requestUpdateTask", task)
}
</script>

<template>
    <li class="list-group-item" :class="{ 'bg-light': readonly }">
        <span>{{ fullDescription }}</span>
        <div class="float-end" v-show="!readonly">
            <button class="btn btn-danger btn-xs" @click="clickToDeleteTask(task)">
                <i class="bi-trash" aria-hidden="true"></i>
            </button>

            <button class="btn btn-info btn-xs" @click="editTask(task)" v-if="!taskEdit">
                <i class="bi-pencil" aria-hidden="true"></i>
            </button>
            <button class="btn btn-warning btn-xs" @click="closeEdit" v-else>
                <i class="bi-arrow-up" aria-hidden="true"></i>
            </button>
        </div>
        <div v-if="taskEdit">
            <hr />
            <TaskDetail
                :task="taskEdit"
                @requestUpdateTask="detailRequestedUpdateTask"
                @hide="closeEdit"
            ></TaskDetail>
        </div>
    </li>
</template>

<style scoped>
.completed {
    text-decoration: line-through;
}

button.btn {
    margin-left: 5px;
}
</style>
