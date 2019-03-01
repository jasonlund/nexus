<template>
    <div class="flex justify-center items-center w-full">
        <div class="flex flex-col justify-center items-center flex-1" v-if="thread">
            <div
                class="h-24 w-full p-2 |
                    flex justify-between my-2 |
                    text-grey-darker border border-1-grey rounded cursor-pointer |
                    hover:bg-blue-light hover:text-white"
            >
                <div class="flex justify-center items-center p-2">
                    <ion-icon class="opacity-25 text-3xl" title="chatbubbles"></ion-icon>
                </div>
                <div class="flex flex-col justify-center flex-1 p-2">
                    <div class="text-lg">{{ thread.title }}</div>
                    <div class="text-sm opacity-50">{{ thread.description }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                thread: null,
            }
        },

        computed: {
            threadId() {
                return this.$route.params.threadId || null
            },

            channelSlug() {
                return this.$route.params.channelSlug || null
            }
        },

        watch: {
            threadId: {
                immediate:true,
                handler(newValue) {
                    this.fetchThread(newValue);
                }
            }
        },

        methods: {
            fetchThread(threadId) {
                axios.get(`channels/${this.channelSlug}/${this.threadId}`).then((response) => {
                    this.thread = response.data;
                })
                .catch(() => {
                    alert('fuck');
                })
            }
        }
    }
</script>
