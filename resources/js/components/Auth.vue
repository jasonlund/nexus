<template>
    <div class="flex justify-end items-center text-blue-lighter">
        <login show-login="showLogin"></login>
        <register show-register="showRegister"></register>
        <a @click="showRegister = true;" class="text-sm cursor-pointer hover:text-blue-lightest">Register</a>
        <a
            @click="showLogin = true;"
            class="ml-2 py-2 px-3 border border-blue-lighter rounded cursor-pointer hover:text-blue-lightest
                hover:border-blue-lightest"
        >
            Login
        </a>
    </div>
</template>

<script>
    import Register from './Register.vue';
    import Login from './Login.vue';

    export default {
        data() {
            return {
                showRegister: false,
                showLogin: false,
            }
        },

        components: {
            Register,
            Login,
        },
    }
</script>
