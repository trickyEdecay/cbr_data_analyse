<template>
    <div class="statistic">
        <slot></slot>
    </div>
</template>

<script>
    export default {
        name: "Statistics"
    }
</script>

<style scoped lang="less">
    .statistic {

        margin: 30px 0;
        box-sizing: border-box;
        display: flex;
        width: 100%;
        flex-wrap: wrap;

        align-items: center;
        justify-content: center;

    }
</style>