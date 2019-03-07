<template>
    <div class="flex justify-center items-center w-inherit pb-8">
        <div class="flex flex-col justify-center items-center w-full">
            <router-link
                :to="{ name: 'thread', params: { channelSlug, threadSlug: thread.slug }}"
                v-show="channelSlug"
                 class="h-24 w-full p-2 |
                    flex justify-between my-2 |
                    text-grey-darker border border-1-grey rounded cursor-pointer |
                    hover:bg-blue hover:text-white"
                 :key="thread.title"
                 v-for="thread in channel.threads"
            >
                <div class="flex justify-center items-center p-2">
                    <span class="far fa-comments | opacity-25 text-3xl"></span>
                </div>
                <div class="flex flex-col justify-center flex-1 p-2">
                    <div class="text-xl">{{ thread.title }}</div>
                    <div class="text-sm opacity-50">{{ thread.body }}</div>
                </div>
                <div class="flex p-2 justify-center items-center text-center text-uppercase text-grey text-xs">
                    {{ thread.reply_count }} Replies
                </div>
            </router-link>
            <div @click="nextPage">next</div>
        </div>
        <portal to="banner-portal">
           <div
               class="bg-blue h-full w-full bg-cover bg-center"
               style="background-image: url('images/background-dark.jpg')"
           ></div>
        </portal>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                channel: {},
                meta: {}
            };
        },

        computed: {
            channelSlug() {
                return this.$route.params.channelSlug;
            }
        },

        watch: {
            channelSlug: {
                immediate:true,
                handler(newValue) {
                    this.fetchChannel(newValue);
                }
            }
        },

        methods: {
            fetchChannel(channelSlug) {
                axios.get(`/channels/${channelSlug}`).then((response) => {
                    this.meta = response.data.threads.meta;
                    delete response.data.threads.meta;
                    this.channel = response.data;
                })
                .catch((e) => {
                    alert(e);
                })
            },

            nextPage() {
                axios.get(this.meta.pagination.links.next).then((response) => {
                    console.log(response);
                })
                .catch((e) => {
                    alert(e);
                })
            },

            previousPage() {
                axios.get(this.meta.pagination.links.previous).then((response) => {
                    console.log(response);
                })
                .catch((e) => {
                    alert(e);
                })
            },

            goToPage(page) {
                axios.get(`/channels/${channelSlug}?page=${page}`).then((response) => {
                    console.log(response);
                })
                .catch((e) => {
                    alert(e);
                })
            }
        },

        mounted() {
            console.log('Component mounted.')
        }
    }
</script>
