<template>
    <div class="flex justify-center items-center w-full">
        <div class="flex flex-col justify-center items-center flex-1">
            <router-link :to="`${channelId}/thread/${thread.id}`"
                         class="h-24 w-full p-2 |
                    flex justify-between my-2 |
                    text-grey-darker border border-1-grey rounded cursor-pointer |
                    hover:bg-blue-light hover:text-white"
                         :key="thread.title"
                         v-for="thread in threads"
            >
                <div class="flex justify-center items-center p-2">
                    <ion-icon class="opacity-25 text-3xl" title="chatbubbles"></ion-icon>
                </div>
                <div class="flex flex-col justify-center flex-1 p-2">
                    <div class="text-lg">{{ thread.title }}</div>
                    <div class="text-sm opacity-50">{{ thread.body }}</div>
                </div>
                <div class="flex p-2 justify-center items-center w-24 text-center">
                    {{ thread.replies.length ?? 0 }} Replies
                </div>
            </router-link>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                threads: []
            };
        },

        computed: {
            channelId() {
                return this.$route.params.channelId;
            }
        },

        watch: {
            channelId: {
                immediate:true,
                handler(newValue) {
                    this.fetchThread(newValue);
                }
            }
        },

        methods: {
            fetchThread(channelId) {
                axios.get(`/threads`).then((response) => {
                    this.threads = response.data;
                })
                .catch(() => {
                    alert('fuck');
                })
            }
        },

        mounted() {
            console.log('Component mounted.')
        }
    }
</script>
