<template>
    <!-- Display vCards -->
    <div v-if="showingVCards">
        <h2>vCards</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone Number</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(vcard, index) in vcards" :key="index">
                    <td>{{ vcard.name }}</td>
                    <td>{{ vcard.phone_number }}</td>
                    <td>{{ vcard.email }}</td>
                    <td>
                        <button @click="showMoreInfoVCard(vcard.id)">More Info</button>
                        <button @click="editVCard(vcard)">Edit</button>
                        <button @click="deleteVCard(vcard)">Delete</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
