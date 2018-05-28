<template>
    <div>
        <div class="profile">
            <div class="basic-info">
                <img src="../assets/headphoto.png">
                <div class="name-container">
                    <h1 class="name">{{name}}</h1>
                    <p class="tel">{{tel}}</p>
                    <p class="achieve-time">分数达成时间：{{achieveTime}}</p>
                </div>
            </div>
            <div class="basic-statistics">
                <div class="data-block red">
                    <h1>分数</h1>
                    <div>{{score}}<span class="unit">分</span></div>
                </div>
                <div class="data-block purple">
                    <h1>准确率</h1>
                    <div>{{correctRate}}<span class="unit">%</span></div>
                </div>
                <div class="data-block pink">
                    <h1>排名</h1>
                    <div>{{ranking}}</div>
                </div>
            </div>
        </div>


        <Panel title="分数变化曲线" description="记录了该玩家每一次分数的变化情况">
            <div id="scoreChangeChart" class="chart-container"></div>
            <statistics>
                <statistics-item title="最高分" :data="highestScore+'分'"></statistics-item>
                <statistics-item title="平均分" :data="averageScore+'分'"></statistics-item>
            </statistics>
        </Panel>

        <Panel title="排名变化曲线" description="记录了该玩家每一次排名的变化情况">
            <div id="rankingChangeChart" class="chart-container"></div>
            <statistics>
                <statistics-item title="最低排名" :data="lowestRanking"></statistics-item>
                <statistics-item title="平均排名" :data="averageRanking"></statistics-item>
            </statistics>
        </Panel>

    </div>
</template>

<script>
    import api from "../api";
    import qs from 'qs';
    import axios from 'axios';
    import Panel from "./Panel";
    import Statistics from '@/components/Statistics';
    import StatisticsItem from '@/components/StatisticsItem';
    import echarts from 'echarts';
    import merge from 'webpack-merge';
    import utils from '@/lib/utils';

    let scoreChangeChart;
    let rankingChangeChart;

    export default {
        name: "PlayerProfile",
        components:{Panel,Statistics,StatisticsItem},
        data (){
            return {
                name:"获取中",
                tel:"loading...",
                achieveTime:"2018-05-12 12:12:12",
                score:"0",
                correctRate:"0",
                ranking:"0",
                wrongCount:0,
                rightCount:0,
                historyScore:[],
                highestScore:0,
                averageScore:0,
                historyRanking:[]
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
                        right:0
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

                // 初始化 分数变化图表
                scoreChangeChart = echarts.init(document.getElementById('scoreChangeChart'));
                // 绘制图表
                scoreChangeChart.setOption(merge(chartOption,{
                    tooltip: {
                        formatter: function(data)
                        {
                            return `${data.name}题：${data.value} 分`;
                        }
                    }
                }));
                // 初始化 排名变化图表
                rankingChangeChart = echarts.init(document.getElementById('rankingChangeChart'));
                // 绘制图表
                rankingChangeChart.setOption(merge(chartOption,{
                    tooltip: {
                        formatter: function(data)
                        {
                            return `${data.name}题：${data.value} 名`;
                        }
                    }
                }));
            },
            getPageFill(){
                let self = this;
                axios.get(process.env.API_HOST + api.getPlayerPageFill+`/${self.$route.params.id}`)
                .then((response)=>{
                    self.name = response.data.player.name;
                    self.tel = response.data.player.tel;
                    self.achieveTime = response.data.player.achievetime;
                    self.score = response.data.player.score;
                    self.ranking = response.data.player.ranking;
                    self.wrongCount = response.data.player.wrongcount;
                    self.rightCount = response.data.player.rightcount;
                    self.correctRate = Math.round(self.rightCount/(self.rightCount+self.wrongCount)*100);
                    self.historyScore = response.data.player.historyscore.split(",").filter((score)=>{
                        if(score != ""){
                            return true;
                        }
                    });
                    self.historyRanking = response.data.player.historyranking.split(",").filter((ranking)=>{
                        if(ranking != ""){
                            return true;
                        }
                    });
                    self.highestScore = utils.max(self.historyScore);
                    self.averageScore = (Math.round(utils.average(self.historyScore)*100)/100).toFixed(2);
                    self.lowestRanking = utils.max(self.historyRanking);
                    self.averageRanking = (Math.round(utils.average(self.historyRanking)*100)/100).toFixed(2);

                    // 玩家分数变化图表
                    scoreChangeChart.setOption({
                        xAxis: {
                            data: utils.rangeArray(0,self.historyScore.length-1),
                        },
                        yAxis:[
                            {
                                type: 'value',
                                min: 0,
                            }
                        ],
                        series: [{
                            type:'line',
                            name:"分数",
                            data: self.historyScore,
                            itemStyle:{
                                color:['#d93c50']
                            }
                        }]
                    });

                    // 玩家排名变化图表
                    rankingChangeChart.setOption({
                        xAxis: {
                            data: utils.rangeArray(0,self.historyRanking.length-1),
                        },
                        yAxis:[
                            {
                                type: 'value',
                                min: 0,
                            }
                        ],
                        series: [{
                            type:'line',
                            name:"排名",
                            data: self.historyRanking,
                            itemStyle:{
                                color:['#d93c50']
                            }
                        }]
                    });

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
                scoreChangeChart.resize();
                rankingChangeChart.resize();
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
    .profile{
        transition: all .28s ease-in-out;
        display: flex;
        margin-top: 20px;
        margin-bottom: 60px;
        width: 100%;

        .basic-info{
            transition: all .28s ease-in-out;
            display: flex;
            width: 80%;
            img{
                width: 100px;
                height: 100px;
            }
            .name-container{

                padding-left: 12px;

                h1,p{
                    margin: 0;
                }
                .name{
                    margin-top: 4px;
                }
                .tel{

                }
                .achieve-time{
                    color: #888;
                    white-space: nowrap;
                }
            }
        }

        .basic-statistics{
            transition: all .28s ease-in-out;
            display: flex;
            width: 50%;
            height: 100px;
            box-sizing: border-box;
            padding-bottom: 6px;
            align-items: flex-end;
            justify-content: flex-end;

            // 数据色块
            .data-block{
                transition: all .28s ease-in-out;
                margin-left: 12px;
                height: 80px;
                flex: 1;
                color: #fff;
                border-radius: 10px;

                &:first-of-type{
                    margin: 0;
                }

                &.red{
                    background: #d93c50;
                }

                &.purple{
                    background: #8e5ab4;
                }

                &.pink{
                    background: #de66a2;
                }

                h1{
                    text-align: center;
                    font-size: 14px;
                    margin: 0;
                    margin-top: 12px;
                    opacity: .6;
                }

                div{
                    margin-top: 4px;
                    text-align: center;
                    font-size: 25px;

                    .unit{
                        font-size: 13px;
                    }
                }
            }
        }


        @media(max-width: 710px){
            // .profile
            &{
                display: block;

                .basic-info{
                    width: 100%;
                    justify-content: center;
                }
                .basic-statistics{
                    width: 100%;
                    margin-top: 25px;
                    justify-content: center;

                    .data-block{
                        flex: 1;
                    }
                }
            }
        }

        @media(max-width: 460px){
            &{
                .basic-info{
                    transform: scale(.8);
                }
            }
        }
    }
</style>