<script setup></script>

<template>
    <h2>Create a VCard</h2>
    <form @submit.prevent="createVCard">
        <div class="form-group">
            <label for="name">Name:</label>
            <input v-model="vcard.name" type="text" id="name" class="form-control" required />
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input v-model="vcard.email" type="email" id="email" class="form-control" required />
        </div>

        <div class="form-group">
            <label for="phone_number">Phone Number:</label>
            <input
                v-model="vcard.phone_number"
                type="text"
                id="phone_number"
                class="form-control"
                required
            />
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input
                v-model="vcard.password"
                type="password"
                id="password"
                class="form-control"
                required
            />
        </div>

        <div class="form-group">
            <label for="confirmation_code">Confirmation Code:</label>
            <input
                v-model="vcard.confirmation_code"
                type="text"
                id="confirmation_code"
                class="form-control"
                required
            />
        </div>

        <button type="submit" class="btn btn-primary">Create VCard</button>
    </form>
</template>
