<template>
    <div class="flex justify-end items-center text-blue-lighter">
        <modal v-if="showModal" @close="showModal = false">
            <div class="flex" slot="body">
                <input class="form-control | mr-2" placeholder="username" />
                <input class="form-control" placeholder="password" type="password" />
            </div>
        </modal>
        <a @click="showModal = true;" class="text-sm cursor-pointer hover:text-blue-lightest">Register</a>
        <a
            @click="showModal = true;"
            class="ml-2 py-2 px-3 border border-blue-lighter rounded cursor-pointer hover:text-blue-lightest
                hover:border-blue-lightest"
        >
            Login
        </a>
    </div>
</template>

<script>
    import Modal from './Modal.vue';

    export default {
        data() {
            return {
                showModal: false,
            }
        },

        components: {
            Modal,
        },
    }
</script>
