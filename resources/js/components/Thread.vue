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
                    <span class="far fa-comments | opacity-25 text-3xl"></span>
                </div>
                <div class="flex flex-col justify-center flex-1 p-2">
                    <div class="text-lg">{{ thread.title }}</div>
                    <div class="text-sm opacity-50">{{ thread.description }}</div>
                </div>
            </div>
        </div>
        <portal to="banner-portal">
            This is the header for {{ thread.title || '' }}
        </portal>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                thread: {},
            }
        },

        computed: {
            threadSlug() {
                return this.$route.params.threadSlug || null
            },

            channelSlug() {
                return this.$route.params.channelSlug || null
            }
        },

        watch: {
            threadSlug: {
                immediate:true,
                handler(newValue) {
                    this.fetchThread(newValue);
                }
            }
        },

        methods: {
            fetchThread(threadSlug) {
                axios.get(`channels/${this.channelSlug}/${this.threadSlug}`).then((response) => {
                    this.thread = response.data;
                })
                .catch(() => {
                    alert('fuck');
                })
            }
        }
    }
</script>
