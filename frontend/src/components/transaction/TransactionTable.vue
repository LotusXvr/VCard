<script setup>
import { ref, onMounted, computed } from "vue"
import axios from "axios"

const props = defineProps({
    transactions: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
})
const emit = defineEmits(['edit'])

const editClick = (transaction, categories) => {
  emit('edit', transaction, categories)
}

const transactionsRef = ref([])
const categoriesRef = ref([])
const wasSent = (transaction) => {
    return transaction.type == "D" ? true : false
}

const formatDateTime = (dateTimeString) => {
    const [date, time] = dateTimeString.split(" ")
    const options = { month: "short", day: "numeric" }
    const formattedDate = new Date(date).toLocaleDateString("en-GB", options)
    return { date: formattedDate, time: time.slice(0, 5) } // Extract only hours and minutes
}

const transactionsByYearMonth = computed(() => {
    const groupedTransactions = {}
    transactionsRef.value.forEach((transaction) => {
        const year = new Date(transaction.datetime).getFullYear()
        const monthName = new Date(transaction.datetime).toLocaleString("en-GB", { month: "long" })
        const key = `${monthName} ${year}`

        if (!groupedTransactions[key]) {
            groupedTransactions[key] = []
        }

        groupedTransactions[key].push(transaction)
    })
    return groupedTransactions
})


const fetchCategoryNames = async () => {
  try {
    const response = await axios.get("category");
    const categories = response.data;
    for (const category of categories) {
      categoriesRef.value[category.id] = category.name;
    } 
  } catch (error) {
    console.error("Error fetching category names:", error);
  }
};

const getCategoryNameById = (categoryId) => {
  return categoriesRef.value[categoryId] || "Undefined";
};

const getCategoryNameForTransaction = (transaction) => {
  const categoryId = transaction.category_id;
  return getCategoryNameById(categoryId);
};

const categoryColorMap = {};

const getCategoryColor = (categoryId) => {
  // Ensure categoryId is a string
  const categoryIdString = String(categoryId);

  if (!categoryColorMap[categoryIdString]) {
    // If not, generate a color based on the category ID
    const hash = categoryIdString.split('').reduce((acc, char) => char.charCodeAt(0) + acc, 0);
    const hue = (hash % 160) + 100;
    categoryColorMap[categoryIdString] = `hsl(${hue}, 70%, 80%)`;
  }
  
  return categoryColorMap[categoryIdString];
};

const getCategoryColorForTransaction = (transaction) => {
  const categoryId = transaction.category_id;
  return getCategoryColor(categoryId);
};

const truncateDescription = (description) => {
    const maxLength = 27;
    if (description.length <= maxLength) {
      return description;
    } else {
      return description.substring(0, maxLength) + "..."
    }
};

onMounted(async () => {
    transactionsRef.value = props.transactions
    categoriesRef.value = props.categories
    await fetchCategoryNames();
})

</script>

<template>
    <div>
        <h1>Transactions</h1>
        <hr />
        <table class="table table-striped">
            <thead>
                <tr>
                    <th style="width: 100px">Date</th>
                    <th style="width: 100px">Time</th>
                    <th style="width: 100px">Value</th>
                    <th style="width: 100px">Balance</th>
                    <th style="width: 100px">Reference</th>
                    <th style="width: 100px">Description</th>
                    <th style="width: 100px">Category</th>
                </tr>
            </thead>
            <tbody v-for="(transactions, key) in transactionsByYearMonth" :key="key">
                <h4 style="margin-top: 10px">{{ key }}</h4>
                <tr v-for="transaction in transactions" :key="transaction.id">
                    <td>{{ formatDateTime(transaction.datetime).date }}</td>
                    <td>{{ formatDateTime(transaction.datetime).time }}</td>
                    <td :style="{ color: wasSent(transaction) ? 'red' : 'green' }">
                        {{ wasSent(transaction) ? "-" : "+" }}{{ transaction.value }}
                    </td>
                    <td>{{ transaction.new_balance }}</td>
                    <td>{{ transaction.payment_reference }}</td>
                    <td>{{ transaction.description ? truncateDescription(transaction.description) : '' }}</td>
                    <td :style="{ backgroundColor: getCategoryColorForTransaction(transaction) }" class="d-flex justify-content-between align-items-center">
                        <div>
                            {{ getCategoryNameForTransaction(transaction) }}
                        </div>
                        <button class="btn btn-xs btn-light" @click="editClick(transaction, categories)">
                            <i class="bi bi-xs bi-pencil"></i>
                        </button>
                    </td>


                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
button {
    margin-left: 3px;
    margin-right: 3px;
}
</style>
