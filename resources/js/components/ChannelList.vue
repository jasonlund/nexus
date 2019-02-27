<template>
    <div class="flex justify-center items-center w-full">
        <div class="flex flex-col justify-center items-center flex-1">
            <router-link :to="`/${channel.slug}`"
                class="h-24 w-full p-2 |
                    flex justify-between my-2 |
                    text-grey-darker border border-1-grey rounded cursor-pointer |
                    hover:bg-blue-light hover:text-white"
                :key="channel.name"
                v-for="channel in channels"
            >
                <div class="flex justify-center items-center p-2">
                    <ion-icon class="opacity-25 text-3xl" name="chatbubbles"></ion-icon>
                </div>
                <div class="flex flex-col justify-center flex-1 p-2">
                    <div class="text-lg">{{ channel.name }}</div>
                    <div class="text-sm opacity-50">{{ channel.description }}</div>
                </div>
                <div class="flex p-2 justify-center items-center w-24 text-center">
                    {{ channel.post_count }} Posts
                </div>
            </router-link>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                channels: [
                    {
                        name: 'General',
                        slug: 'general',
                        description: 'Talk about anything an everything!',
                        post_count: 1,
                        thread_count: 1
                    },
                    {
                        name: 'Protoss',
                        slug: 'protoss',
                        description: 'Talk about Protoss strategy, how much you hate Zerg and how easy Terran is!',
                        post_count: 1337,
                        thread_count: 69
                    },
                    {
                        name: 'Zerg',
                        slug: 'zerg',
                        description: 'Talk about Zerg strategy, how much you hate Zerg and how easy Terran is!',
                        post_count: 1337,
                        thread_count: 69
                    },
                    {
                        name: 'Terran',
                        name: 'terran',
                        description: 'Talk about Terran strategy, how much you hate Zerg and how easy Terran is!',
                        post_count: 1337,
                        thread_count: 69
                    },
                ]
            };
        },

        mounted() {
            console.log('Component mounted.')
        }
    }
</script>
