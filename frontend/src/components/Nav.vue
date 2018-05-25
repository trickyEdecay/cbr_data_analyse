<template>
    <div class="container">
        <template v-for="item of nav">
            <router-link tag="div" :to="item.link" :class="{'nav-button':true,'active':$route.name == item.routeName}">{{item.title}}</router-link>
        </template>
    </div>
</template>

<script>
    export default {
        name: "Nav",
        data (){
            return {
                nav:[
                    {
                        title:"题目统计",
                        link:"/questions",
                        routeName:"Questions"
                    },
                    {
                        title:"玩家统计",
                        link:"/players",
                        routeName:"Players"
                    }
                ]
            }
        }
    }
</script>

<style scoped lang="less">
    @import "../assets/styles/var.less";
    .container {
        width: 100%;
        height: 60px;
        box-sizing: border-box;
        border-top: 4px solid @main-color;
        text-align: center;
        display: flex;
        justify-content: center;
    }

    .nav-button {
        display: flex;
        padding: 24px;
        font-weight: bold;
        align-items: center;
        cursor: pointer;
        user-select: none;
    }
    .nav-button.active {
        display: flex;
        padding: 24px;
        color: white;
        background: @main-color;
        font-weight: bold;
        align-items: center;
    }
</style>