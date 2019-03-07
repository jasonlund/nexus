<template>
    <div class="absolute flex flex-col w-full min-h-screen overflow-x-hidden">
        <div class="flex items-center flex-col  flex-1">
            <nav class="w-full py-3 bg-blue-darker">
                <div class="flex justify-between items-center w-full px-4 max-w-5xl mx-auto">
                    <div>
                        <a class="text-2xl text-white" href="/#/">
                            The Pylon Forum
                        </a>
                    </div>
                    <auth></auth>
                </div>
            </nav>
            <portal-target name="banner-portal" class="flex justify-center items-center bg-grey-light w-screen h-48">
            </portal-target>
            <div class="flex justify-center items-center w-full mt-8 mb-8 px-4 max-w-5xl mx-auto">
                <slot name="content"></slot>
            </div>
        </div>
        <div class="h-48 bg-grey-darkest">
            test
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                channels: []
            };
        },

        mounted() {

        }
    }
</script>
