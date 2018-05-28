<template>
    <div class="container">
        <div :class="{'sort':true,'sort-1':sort==1,'sort-2':sort==2,'sort-3':sort==3}">{{sort}}</div>
        <router-link  tag="div" class="name" :to="'player-profile/'+playerId">{{playerName}}</router-link>
        <div class="info"><slot></slot></div>
    </div>
</template>

<script>
    export default {
        name: "PlayerRow",
        props:{
            sort:{
                require:true
            },
            playerName:{
                require:true
            },
            playerId:{
                require:true
            }
        }
    }
</script>

<style scoped lang="less">
    @import "../assets/styles/var";
.container{
    display: flex;
    justify-content: flex-start;
    width: 100%;
    padding: 12px 20px;
    box-sizing: border-box;
    margin: 0;
    border-top: 1px dashed #efefef;
    border-bottom: 1px dashed #efefef;

    &:hover{
        background: #fafafa;
    }

    .sort{
        display: inline-block;
        background: @main-color;
        border-radius:100px;
        color: #fff;
        width: 22px;
        height: 22px;
        margin-right: 8px;
        text-align: center;
        font-size: 12px;
        line-height: 24px;
        font-weight: bold;
    }

    .sort-1{
        background: #fac900;
    }
    .sort-2{
        background: #d8d8d8;
    }
    .sort-3{
        background: #ff9950;
    }

    .info{
        display: flex;
        flex: 1;
        justify-content: flex-end;
        align-self: flex-end;
    }

    .name{
        cursor: pointer;

        &:hover{
            text-decoration: underline;
        }
    }
}
</style>