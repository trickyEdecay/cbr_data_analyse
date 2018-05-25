<template>
    <div class="statistic-item">
        <h4 :title="tips">{{title}}</h4>
        <h1>{{data}}</h1>
    </div>
</template>

<script>
    export default {
        name: "Statistics",
        props:{
            tips:{
                default:""
            },
            title:{
                required:true,
                default:""
            },
            data:{
                required:true
            }
        }
    }
</script>

<style scoped lang="less">


    .statistic-item {
        transition: all .14s ease-in-out;
        min-width: 120px;
        height: 100px;
        text-align: center;
        border-radius: 10px;
        /*background: #fff;*/
        padding: 20px;

        &:hover {
            box-shadow: 0 0 20px 5px rgba(217, 60, 80, .08);
        }
        h4 {
            user-select: none;
            color: #bbb;
            font-weight: normal;
            margin: 0px;
        }

        h1 {
            margin: 20px;
        }
    }

</style>