<template>
    <div>
      <div>
        <button @click="showUsers" class="btn btn-primary">Show Users</button>
        <button @click="showVCards" class="btn btn-primary">Show vCards</button>
      </div>
  
      <!-- Display Users -->
      <div v-if="showingUsers">
        <h2>Users</h2>
        <ul>
          <li v-for="(user, index) in users" :key="index">
            {{ user.name }} - {{ user.email }}
          </li>
        </ul>
      </div>
  
      <!-- Display vCards -->
      <div v-if="showingVCards">
        <h2>vCards</h2>
        <ul>
          <li v-for="(vcard, index) in vcards" :key="index">
            <!-- Customize how you display vCards here -->
            {{ vcard.name }} - {{ vcard.phone_number }}
          </li>
        </ul>
      </div>
    </div>
  </template>
  
  <script>
  import axios from '../plugins/axios';
  
  export default {
    data() {
      return {
        users: [],       // Store the fetched users
        vcards: [],      // Store the fetched vCards
        showingUsers: false,  // Control whether to show users
        showingVCards: false, // Control whether to show vCards
      };
    },
    methods: {
      showUsers() {
        // Set the flag to show users and hide vCards
        this.showingUsers = true;
        this.showingVCards = false;
      },
      showVCards() {
        // Set the flag to show vCards and hide users
        this.showingUsers = false;
        this.showingVCards = true;
      },
      fetchUsers() {
        axios.get('users')
          .then(response => {
            this.users = response.data;
          })
          .catch(error => {
            console.error('Error fetching Users data:', error);
          });
      },
      fetchVCards() {
        axios.get('vcards')
            .then((response) => {
            console.log('vCards response:', response.data);
            this.vcards = response.data;
            })
            .catch((error) => {
            console.error('Error fetching vCards data:', error);
            });
        }

    },
    mounted() {
      // Fetch users and vCards when the component is mounted
      this.fetchUsers();
      this.fetchVCards();
    },
  };
  </script>
  