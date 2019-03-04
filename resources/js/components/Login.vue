<template>
    <div class="flex items-center nav-link">
        <modal v-if="showModal" @close="showModal = false">
            <div class="flex" slot="body">
                <input class="form-control | mr-2" placeholder="username" />
                <input class="form-control" placeholder="password" type="password" />
            </div>
        </modal>
        <a @click="showModal = true;">Login</a>
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
