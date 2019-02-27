<template>
    <div class="flex justify-center items-center w-full">
        <div class="flex flex-col justify-center items-center flex-1">
            <router-link :to="`${channelSlug}/post/${post.id}`"
                         class="h-24 w-full p-2 |
                    flex justify-between my-2 |
                    text-grey-darker border border-1-grey rounded cursor-pointer |
                    hover:bg-blue-light hover:text-white"
                         :key="post.title"
                         v-for="post in posts"
            >
                <div class="flex justify-center items-center p-2">
                    <ion-icon class="opacity-25 text-3xl" title="chatbubbles"></ion-icon>
                </div>
                <div class="flex flex-col justify-center flex-1 p-2">
                    <div class="text-lg">{{ post.title }}</div>
                    <div class="text-sm opacity-50">{{ post.description }}</div>
                </div>
                <div class="flex p-2 justify-center items-center w-24 text-center">
                    {{ post.reply_count }} Posts
                </div>
            </router-link>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                posts: [
                    {
                        title: 'Welcome to general',
                        id: 'general',
                        description: 'Talk about anything an everything!',
                        reply_count: 1,
                    }
                ]
            };
        },

        computed: {
            channelSlug() {
                return this.$route.params.channelSlug;
            }
        },

        mounted() {
            console.log('Component mounted.')
        }
    }
</script>
