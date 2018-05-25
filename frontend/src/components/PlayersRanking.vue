<template>
    <div>
        <div :class="{'player-list-container':true,'all':isExpanded}">
            <slot></slot>
        </div>
        <div class="expand-btn" @click="isExpanded = !isExpanded">{{isExpanded==false?"↓ 展开列表":"↑ 收起列表"}}</div>
    </div>
</template>

<script>
    export default {
        name: "PlayersRanking",
        props:{

        },
        data(){
            return{
                isExpanded:false
            }
        }
    }
</script>

<style scoped lang="less">
    .player-list-container{
        transition: all .28s ease-in-out;
        margin-top: 20px;
        height: 170px;
        overflow: auto;

    & /deep/ .container:nth-of-type(n+2){
          border-top: 0;
      }

    &.all{
         height: 481px;
     }
    }
    .expand-btn{
        box-sizing: border-box;
        margin: 0 auto;
        padding: 8px 20px;
        border-radius: 0 0 10px 10px;
        border: 1px solid #efefef;
        color: #ccc;
        background: #fafafa;
        border-top: 0;
        cursor: pointer;
        user-select: none;
        text-align: center;

    &:hover{
         background: #efefef;
         color: #555;
     }
    }
</style>