<template>
    <div>
        <modal v-if="showModal" @close="showModal = false">
            <div class="flex" slot="body">
                <input class="form-control | mr-2" placeholder="username" />
                <input class="form-control" placeholder="password" type="password" />
            </div>
        </modal>
        <span @click="showModal = true;">Login</span>
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
