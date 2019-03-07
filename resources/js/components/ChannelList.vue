<template>
    <div class="flex justify-center items-center w-inherit pb-8">
        <div class="flex flex-col justify-center items-center w-full">
            <router-link :to="{ name: 'channel', params: { channelSlug: channel.slug }}"
                class="h-24 w-full p-2 |
                    flex justify-between my-2 |
                    text-grey-darker border border-1-grey rounded cursor-pointer |
                    hover:bg-blue hover:text-white"
                :key="channel.name"
                v-for="channel in channels"
            >
                <div class="flex justify-center items-center p-2">
                    <span class="far fa-comments | opacity-25 text-3xl"></span>
                </div>
                <div class="flex flex-col justify-center flex-1 p-2">
                    <div class="text-lg">{{ channel.name }}</div>
                    <div class="text-sm opacity-50">{{ channel.description }}</div>
                </div>
                <div class="flex p-2 justify-center items-center text-center text-uppercase text-grey text-xs">
                    {{ channel.thread_count }} Threads
                </div>
            </router-link>
        </div>
        <portal to="banner-portal">
            This is the header for the main page
        </portal>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                channels: []
            };
        },

        methods: {
            loadChannels() {
                axios.get('channels')
                .then((response) => {
                    this.channels = response.data;
                })
                .catch((error) => {
                      console.log(error);
                })
            }
        },

        mounted() {
            this.loadChannels();
        }
    }
</script>
