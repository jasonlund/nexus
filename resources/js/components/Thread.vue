<template>
    <div class="flex justify-center items-center w-inherit pb-8">
        <div class="flex flex-col justify-center items-center w-full" v-if="thread">
            <div
                class="w-full p-2 |
                    flex justify-between my-2 |
                    text-grey-darker border border-1-grey rounded"
            >
                <div class="flex flex-col justify-center flex-1 p-2">
                    <div class="flex items-end">
                        <div class="font-bold text-sm text-grey">{{ thread.owner.name }}</div>
                        <div class="font-normal text-xs text-grey ml-2">{{ thread.created_at }}</div>
                    </div>
                    <p class="text-xl mb-4">{{ thread.title }}</p>
                    <p>{{ thread.body }}</p>
                </div>
            </div>
            <div class="flex justify-end w-full">reply</div>
            <div class="flex flex-col mt-8">
                <div class="border-l border-1-grey rounded p-4 mt-2" v-for="reply in thread.replies">
                    <div class="flex items-end">
                        <div class="font-bold text-sm text-grey">{{ reply.owner.username }}</div>
                        <div class="font-normal text-xs text-grey">{{ reply.created_at }}</div>
                    </div>
                    <p class="text-lg">{{ reply.body }}</p>
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
                meta: {},
            }
        },

        computed: {
            threadSlug() {
                return this.$route.params.threadSlug || null
            },

            channelSlug() {
                return this.$route.params.channelSlug || null
            },

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
                    this.meta = response.data.replies.meta;
                    delete response.data.replies.meta;
                    this.thread = response.data;
                })
                .catch(() => {
                    alert('fuck');
                })
            }
        }
    }
</script>
