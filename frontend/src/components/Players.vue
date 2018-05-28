<template>
    <div>
        <Panel title="获奖名单" description="全场下来能够拿到奖金的玩家">
            <players-ranking>
                <player-row v-for="(player,index) of honorPlayers" :sort="index+1" :playerName="player.name" :playerId="player.id">{{"¥"+player.prize}}</player-row>
            </players-ranking>
            <statistics>
                <statistics-item title="平均分" :data="honorPlayersStatistic.averageScore+'分'"></statistics-item>
                <statistics-item title="平均准确率" :data="honorPlayersStatistic.averageCorrectRate*100+'%'"></statistics-item>
                <statistics-item title="最低分" :data="honorPlayersStatistic.minScore+'分'"></statistics-item>
                <statistics-item title="最低准确率" :data="honorPlayersStatistic.minCorrectRate*100+'%'"></statistics-item>
            </statistics>
        </Panel>
        <Panel title="最终分数排行榜" description="根据玩家的游戏分数进行排序">
            <players-ranking>
                <player-row v-for="(player,index) of highestScorePlayers" :sort="index+1" :playerName="player.name" :playerId="player.id">{{player.score+"分"}}</player-row>
            </players-ranking>
            <statistics>
                <statistics-item title="全场平均分" :data="averageScore+'分'"></statistics-item>
            </statistics>
        </Panel>
        <Panel title="玩家分数分布图" description="">
            <div id="scoreChart" class="chart-container"></div>
            <statistics>
                <statistics-item title="零分玩家占有率" :data="zeroScorePlayersRate*100+'%'"></statistics-item>
            </statistics>
        </Panel>
        <Panel title="参与准确率排行" description="玩家答题的准确率排行榜，计算方式是 玩家答对题目的数量÷玩家参与题目的数量">
            <players-ranking>
                <player-row v-for="(player,index) of highestCorrectRatePlayers" :sort="index+1" :playerName="player.name" :playerId="player.id">{{(player.correctRate*100).toFixed(2)+"%"}}</player-row>
            </players-ranking>
            <statistics>
                <statistics-item title="平均准确率" :data="averageCorrectRate*100+'%'"></statistics-item>
            </statistics>
        </Panel>
        <Panel title="题目参与数最多" description="根据玩家参与的题目数进行排序">
            <players-ranking>
                <player-row v-for="(player,index) of JoinMostQuestionCountPlayers" :sort="index+1" :playerName="player.name" :playerId="player.id">{{player.joinQuestionCount+"题"}}</player-row>
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
    import utils from '@/lib/utils';

    // 玩家分数分布图
    let scoreChart;


    export default {
        name: "Players",
        components:{PlayersRanking, Panel,PlayerRow,Statistics,StatisticsItem},
        data(){
            return {
                // 奖金数量
                prize: 1000,
                // 全场平均准确率
                averageCorrectRate: 0,
                // 玩家平均分
                averageScore: 0,
                // 所有玩家的数据
                players:[],
                // 最高分数玩家
                highestScorePlayers:[],
                // 最高准确率玩家
                highestCorrectRatePlayers:[],
                // 题目参与数最多玩家
                JoinMostQuestionCountPlayers:[],
                // 零分玩家占有率
                zeroScorePlayersRate: 0,
                // 获奖玩家
                honorPlayers:[],
                // 获奖玩家数据
                honorPlayersStatistic:{
                    averageScore:0,
                    averageCorrectRate:0,
                    minCorrectRate:0,
                    minScore:0
                }
            }
        },
        methods:{
            initCharts(){
                // 默认图表样式
                let chartOption = {
                    grid:{
                        top:20,
                        bottom:20,
                        left:36,
                        right:36
                    },
                    xAxis: {
                        axisLine:{show:false},
                        axisTick:{show:false},
                        data:[]
                    },
                    yAxis: {
                        axisLine:{show:false},
                        axisTick:{show:false},
                        splitLine:{
                            lineStyle:{
                                color:['#efefef']
                            }
                        }
                    },
                    color:['#d93c50']
                };

                // 初始化 准确率图表
                scoreChart = echarts.init(document.getElementById('scoreChart'));
                // 绘制图表
                scoreChart.setOption(merge(chartOption,{
                    tooltip: {
                        formatter: function(data)
                        {
                            return `${data.name.replace('+','')+"-"+(parseInt(data.name.replace('+',''))+9)} 分<br/>
                                    ${data.seriesName}：${data.value} 人`;
                        }
                    }
                }));
            },
            getPageFill (){
                let self = this;
                axios.get(process.env.API_HOST + api.getPlayersPageFill)
                .then(function (response) {
                    self.players = response.data.players;

                    // 所有玩家的准确率总和，用来计算平均准确率
                    let correctRateSum = 0;
                    // 所有玩家的分数综合，用来计算平均分
                    let scoreSum = 0;

                    // 玩家分数分布情况
                    let scoreDistributeArray = [];
                    // 分数分布X轴数组
                    let scoreXaxisArray = [];
                    // 零分玩家数量
                    let zeroPlayerCount = 0;

                    self.players.forEach((player)=>{
                        correctRateSum+= player.correctRate;
                        scoreSum+=player.score;
                        // 统计分数分布
                        if(scoreDistributeArray[Math.floor(player.score/10)] == undefined){
                            scoreDistributeArray[Math.floor(player.score/10)] = 1;
                        }else{
                            scoreDistributeArray[Math.floor(player.score/10)] += 1;
                        }
                        if(player.score==0){
                            zeroPlayerCount++;
                        }
                    });

                    self.zeroScorePlayersRate = Math.round(zeroPlayerCount / response.data.playerAmount * 100)/100;

                    for(let i=0;i<scoreDistributeArray.length;i++){
                        scoreXaxisArray[i] = `${i*10}+`;
                        if(scoreDistributeArray[i] == undefined){
                            scoreDistributeArray[i] = 0;
                        }
                    }

                    // 计算玩家平均准确率
                    self.averageCorrectRate = (Math.round(correctRateSum/response.data.playerAmount*10000)/10000).toFixed(2);
                    self.averageScore = (Math.round(scoreSum/response.data.playerAmount*10000)/10000).toFixed(2);

                    // 计算最高分数玩家列表
                    self.highestScorePlayers = self.players.sort((a, b)=>{
                        if (a.score < b.score ) {
                            return 1;
                        }
                        if (a.score > b.score ) {
                            return -1;
                        }
                        // 同分时根据谁先达到这个分数排序
                        if(a.score==b.score){
                            var timeA = new Date(a.achievetime).getTime();
                            var timeB = new Date(b.achievetime).getTime();
                            if(timeA < timeB){
                                return 1;
                            }else{
                                return -1;
                            }
                        }
                    }).slice(0,10);

                    // 取得获奖玩家列表
                    let leftPrize = self.prize;
                    for(let i =0;i<self.highestScorePlayers.length;i++){
                        let player = self.highestScorePlayers[i];
                        if(leftPrize <= 0){
                            break;
                        }
                        if(leftPrize-player.score <= 0){
                            player.prize = leftPrize;
                            leftPrize = 0;
                        }else{
                            player.prize = player.score;
                            leftPrize -= player.score;
                        }
                        self.honorPlayers.push(player);
                    }
                    // 计算获奖玩家数据
                    let honorPlayerScoreSum = 0;
                    let honorPlayerCorrectRateSum = 0;
                    self.honorPlayersStatistic.minCorrectRate = self.honorPlayers[0].correctRate;
                    self.honorPlayers.forEach((player,index)=>{
                        if(index == self.honorPlayers.length-1){
                            self.honorPlayersStatistic.minScore = player.score;
                        }
                        if(player.correctRate < self.honorPlayersStatistic.minCorrectRate){
                            self.honorPlayersStatistic.minCorrectRate = player.correctRate;
                        }
                        honorPlayerScoreSum += player.score;
                        honorPlayerCorrectRateSum += player.correctRate;
                    });
                    self.honorPlayersStatistic.averageScore = (Math.round(honorPlayerScoreSum/self.honorPlayers.length*10000)/10000).toFixed(2);
                    self.honorPlayersStatistic.averageCorrectRate = (Math.round(honorPlayerCorrectRateSum/self.honorPlayers.length*10000)/10000).toFixed(2);

                    // 玩家分数分布图表
                    scoreChart.setOption({
                        xAxis: {
                            data: scoreXaxisArray,
                        },
                        series: [{
                            type:'bar',
                            name:"人数",
                            data: scoreDistributeArray,
                            itemStyle:{
                                color:['#d93c50'],
                                barBorderRadius: [100,100,0,0]
                            },
                            barMaxWidth:10
                        }]
                    });

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
        mounted (){
            this.initCharts();
            window.onresize = utils.throttle(()=>{
                scoreChart.resize();
            },50,200);
        }
    }
</script>

<style scoped lang="less">
    @import "../assets/styles/var";
    .chart-container{
        width: 100%;
        height: 250px;
    }
</style>