<template>
    <div class="flex justify-center items-center flex-col">
        <header class="flex justify-center items-center bg-grey-lighter w-full h-32">
            this is a header, will put images here
        </header>
        <div class="flex justify-center items-center w-4/5 mt-8">
            <slot name="content"></slot>
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
            console.log('Component mounted.')
        }
    }
</script>
