<template>
    <div>
        <Panel title="参与准确率排行" description="玩家答题的准确率排行榜，计算方式是 玩家答对题目的数量÷玩家参与题目的数量">
            <players-ranking>
                <player-row v-for="(player,index) of highestCorrectRatePlayers" :sort="index+1" :playerName="player.name">{{(player.correctRate*100).toFixed(2)+"%"}}</player-row>
            </players-ranking>
            <statistics>
                <statistics-item title="平均准确率" :data="averageCorrectRate*100+'%'"></statistics-item>
            </statistics>
        </Panel>
        <Panel title="题目参与数最多" description="根据玩家参与的题目数进行排序">
            <players-ranking>
                <player-row v-for="(player,index) of JoinMostQuestionCountPlayers" :sort="index+1" :playerName="player.name">{{player.joinQuestionCount+"题"}}</player-row>
            </players-ranking>
        </Panel>
    </div>
</template>

<script>
    import api from "../api";
    import qs from 'qs';
    import axios from 'axios';
    import Panel from "./Panel";
    import PlayersRanking from '@/components/PlayersRanking';
    import PlayerRow from '@/components/PlayerRow';
    import Statistics from '@/components/Statistics';
    import StatisticsItem from '@/components/StatisticsItem';
    import echarts from 'echarts';
    import merge from 'webpack-merge';

    export default {
        name: "Players",
        components:{PlayersRanking, Panel,PlayerRow,Statistics,StatisticsItem},
        data(){
            return {
                // 全场平均准确率
                averageCorrectRate: 0,
                // 所有玩家的数据
                players:[],
                // 最高准确率玩家
                highestCorrectRatePlayers:[],
                // 题目参与数最多玩家
                JoinMostQuestionCountPlayers:[],
            }
        },
        methods:{
            getPageFill (){
                let self = this;
                axios.get(process.env.API_HOST + api.getPlayersPageFill)
                .then(function (response) {
                    self.players = response.data.players;

                    // 所有玩家的准确率总和，用来计算平均准确率
                    let correctRateSum = 0;

                    self.players.forEach((player)=>{
                        correctRateSum+= player.correctRate;
                    });

                    // 计算玩家平均准确率
                    self.averageCorrectRate = (Math.round(correctRateSum/response.data.playerAmount*10000)/10000).toFixed(2);

                    // 计算最高准确率玩家列表
                    self.highestCorrectRatePlayers = self.players.sort((a, b)=>{
                        if (a.correctRate < b.correctRate ) {
                            return 1;
                        }
                        if (a.correctRate > b.correctRate ) {
                            return -1;
                        }
                    }).slice(0,10);

                    // 计算参与最多题目的玩家列表
                    self.JoinMostQuestionCountPlayers = self.players.sort((a, b)=>{
                        if (a.joinQuestionCount < b.joinQuestionCount ) {
                            return 1;
                        }
                        if (a.joinQuestionCount > b.joinQuestionCount ) {
                            return -1;
                        }
                    }).slice(0,10);
                })
            }
        },
        created (){
            this.getPageFill();
        },
        watch:{
            $route:function () {
                this.getPageFill();
            }
        },
    }
</script>

<style scoped lang="less">
    @import "../assets/styles/var";
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