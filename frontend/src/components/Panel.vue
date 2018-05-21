<template>
    <div class="container">

        <header>
            <h1>{{title}}</h1>
            <p>{{description}}</p>
        </header>
        <div>
            <slot></slot>
        </div>
    </div>
</template>

<script>
    export default {
        name: "Panel",
        props:{
            title:{
                required:true
            },
            description:{
                default:""
            }
        }
    }
</script>

<style scoped lang="less">
    @import "../assets/styles/var";
    .container{
        width: 100%;
        overflow: hidden;
        margin-bottom: 60px;
    }
    header{
        margin-left: 8px;
        
        p{
            margin: 0;
            color: #ccc;
        }
    }
    h1{
        font-size: @h1-font-size;
        margin-top: 8px;
        margin-bottom: 8px;
    }
</style>