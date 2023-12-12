<script setup>
import { ref, watch, computed } from "vue"

const props = defineProps({
    category: {
        type: Object,
        required: true,
    },
    inserting: {
        type: Boolean,
        default: false,
    },
    errors: {
        type: Object,
        required: false,
    },
})

const editedCategory = ref(props.category)

const emit = defineEmits(["save", "cancel"])

watch(
    () => props.category,
    (newCategory) => {
        editedCategory.value = newCategory
    },
    { immediate: true },
)

const categoryTitle = computed(() => {
    if (!editedCategory.value) {
        return ""
    }
    return "Category #" + editedCategory.value.name
})

const save = async () => {
    console.log("a"+editedCategory.value)
    emit("save", editedCategory.value)
}

const cancel = () => {
    emit("cancel", editedCategory.value)
}
</script>

<template>
    <div>
      <h2>{{ categoryTitle }}</h2>
      <form @submit.prevent="save" class="needs-validation" novalidate>
        <div class="mb-3">
          <label for="name" class="form-label">Category Name</label>
          <input
            v-model.lazy="editedCategory.name"
            type="text"
            class="form-control"
            id="name"
            required
          />
          <div class="invalid-feedback">Please enter a category name.</div>
        </div>
  
        <div class="mb-3 form-check">
        <input
            v-model="editedCategory.type"
            type="radio"
            value="D"
            class="form-check-input"
            id="debitRadio"
        />
            <label class="form-check-label" for="debitRadio">Debit Category</label>
        </div>
        <div class="mb-3 form-check">
            <input
                v-model="editedCategory.type"
                type="radio"
                value="C"
                class="form-check-input"
                id="creditRadio"
            />
            <label class="form-check-label" for="creditRadio">Credit Category</label>
        </div>

  
        <button type="button" class="btn btn-primary px-5 mx-2" @click="save">Save</button>
        <button type="button" @click="cancel" class="btn btn-secondary">Cancel</button>
      </form>
    </div>
  </template>
  
  