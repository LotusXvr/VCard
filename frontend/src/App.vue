<template>
    <div class="container mt-4">
      <h1 class="text-center">vCard Details</h1>
      <div v-if="vcards.length > 0">
        <table class="table table-striped table-bordered">
          <thead class="thead-dark">
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Phone Number</th>
              <th>Balance</th>
              <th>Max Debit</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="vcard in vcards" :key="vcard.phone_number">
              <td>{{ vcard.name }}</td>
              <td>{{ vcard.email }}</td>
              <td>{{ vcard.phone_number }}</td>
              <td>{{ vcard.balance }}</td>
              <td>{{ vcard.max_debit }}</td>
            </tr>
          </tbody>
        </table>
        <hr>
      </div>
      <div v-else>
        <p class="text-center">No vCards found.</p>
      </div>
    </div>
  </template>
<script>
import axios from "axios"

export default {
    data() {
        return {
            vcards: [], // Ensure 'vcards' is defined in the 'data' section.
        }
    },
    mounted() {
        this.fetchVcards()
    },
    methods: {
        fetchVcards() {
            // Make an API request to fetch a list of vCards
            axios
                .get("http://backend.test/api/vcard")
                .then((response) => {
                    this.vcards = response.data
                })
                .catch((error) => {
                    console.error("Error fetching vCards:", error)
                })
        },
    },
}
</script>
