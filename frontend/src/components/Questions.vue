<template>
    <div>
        <Panel title="题目准确率" description="每一道题目的答题准确率">
            <div id="correctRateChart" class="chart-container"></div>
        </Panel>
        <Panel title="题目参与人数" description="统计了每一个题目有多少人参与">
            <div id="playerAmountChart" class="chart-container"></div>
            <div class="chart-statistic">
                <div class="chart-statistic-item">
                    <h4>账号注册数</h4>
                    <h1>{{playerAmount}}</h1>
                </div>

                <div class="chart-statistic-item">
                    <h4>最多参与数</h4>
                    <h1>{{questionMostPlay_playerAmount}}</h1>
                </div>

                <div class="chart-statistic-item">
                    <h4>最少参与数</h4>
                    <h1>{{questionFewPlay_playerAmount}}</h1>
                </div>
            </div>
        </Panel>
        <Panel title="题目加减分设定" description="每一道题目的加分与减分设定">
            <div id="scoreSettingChart" class="chart-container"></div>
            <div class="chart-statistic">
                <div class="chart-statistic-item">
                    <h4>全部答对总分</h4>
                    <h1>{{scoreIfAllCorrect}}</h1>
                </div>

                <div class="chart-statistic-item">
                    <h4>平均加分</h4>
                    <h1>{{averageAddScore}}</h1>
                </div>

                <div class="chart-statistic-item">
                    <h4>平均扣分</h4>
                    <h1>{{averageMinusScore}}</h1>
                </div>
            </div>
        </Panel>
        <!--<Panel title="题目答题时间设定" description="每一道题目的答题准确率">-->
            <!--<div id="correctRateChart" class="chart-container"></div>-->
        <!--</Panel>-->
        <Panel title="题目列表">
            <ul class="question-list">
                <li v-for="question of questionsPack"><span><span class="question-sort">{{question.sort}}</span>{{question.question}}</span></li>
            </ul>
        </Panel>
    </div>
</template>

<script>
    import api from "../api";
    import qs from 'qs';
    import axios from 'axios';
    import Panel from "./Panel";
    import echarts from 'echarts';
    import merge from 'webpack-merge';


    // 准确率图表
    let correctRateChart ;
    // 题目参与人数图表
    let playerAmountChart;
    // 加减分设定图表
    let scoreSettingChart;

    // 防抖函数
    const throttle = function(fn, delay, mustRunDelay){
        var timer = null;
        var t_start;
        return function(){
            var context = this, args = arguments, t_curr = +new Date();
            clearTimeout(timer);
            if(!t_start){
                t_start = t_curr;
            }
            if(t_curr - t_start >= mustRunDelay){
                fn.apply(context, args);
                t_start = t_curr;
            }
            else {
                timer = setTimeout(function(){
                    fn.apply(context, args);
                }, delay);
            }
        };
    };

    const max = function (arr){
        let max=arr[0];
        for(let item of arr){
            if(max<item){
                max = item;
            }
        }
        return max;
    };
    const min = function (arr){
        let min=arr[0];
        for(let item of arr){
            if(min>item){
                min = item;
            }
        }
        return min;
    };

    export default {
        name: "Questions",
        components: {Panel},
        data(){
            return{
                // 问题包
                questionsPack:[],
                // 最多参与玩家数
                questionMostPlay_playerAmount:0,
                // 最少参与玩家数
                questionFewPlay_playerAmount:0,
                // 今年的玩家人数
                playerAmount:0,
                // 假设一个人全部答对并且都能获得分数的总分
                scoreIfAllCorrect:0,
                // 假设一个人全部答错会被扣多少分
                scoreIfAllWrong:0,
                // 平均加分
                averageAddScore:0,
                // 平均减分
                averageMinusScore:0
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
                correctRateChart = echarts.init(document.getElementById('correctRateChart'));
                // 绘制图表
                correctRateChart.setOption(merge(chartOption,{
                    tooltip: {
                        formatter: function(data)
                        {
                            return `题目 ${data.name}<br/>
                                    ${data.seriesName}：${(data.value * 100).toFixed(2)}%`;
                        }
                    },
                    series: [{
                        type: 'bar',
                        name: '准确率',
                        data:[],
                        itemStyle:{
                            barBorderRadius: 100
                        },
                        barMaxWidth:10
                    }]
                }));

                // 初始化 题目参与人数图表
                playerAmountChart = echarts.init(document.getElementById('playerAmountChart'));
                // 绘制图表
                playerAmountChart.setOption(merge(chartOption,{
                    tooltip: {
                        formatter: function(data)
                        {
                            return `题目 ${data.name}<br/>
                                    ${data.seriesName}：${data.value}人`;
                        }
                    },
                    series: [{
                        type: 'bar',
                        name: '参与人数',
                        itemStyle:{
                            barBorderRadius: 100
                        },
                        data:[],
                        barMaxWidth:10
                    }]
                }));
                // 初始化 加减分设定图表
                scoreSettingChart = echarts.init(document.getElementById('scoreSettingChart'));
                // 绘制图表
                scoreSettingChart.setOption(merge(chartOption,{
                    tooltip: {
                        trigger: 'axis',
                        formatter: function(datas) {
                            let res = `题目 ${datas[0].name} <br/>`;
                            let val;
                            for(var i = 0, length = datas.length; i < length; i++) {
                                val = `${datas[i].value}分`;
                                res += `${datas[i].seriesName}: ${val} <br/>`;
                            }
                            return res;
                        }

                        // formatter: function(data)
                        // {
                        //     return `题目 ${data.name}<br/>
                        //             ${data.seriesName}：${data.value}分`;
                        // }
                    }
                }));
            },

            // 获取页面填充内容
            getQuestionsPack (){
                let self = this;
                axios.get(process.env.API_HOST + api.getQuestionsPack, qs.stringify({
                    hello:"adas"
                }))
                .then(function (response) {
                    // 筛选掉没有人玩的
                    self.questionsPack = response.data.questionsPack.filter((question)=>{
                        if(question.playerAmount>0){
                            return question
                        }
                    });

                    // 设置全场玩家账号注册数
                    self.playerAmount = response.data.playerAmount;


                    // 题目编号数组
                    let questionSortArray = [];
                    // 准确率数组，依照题目编号顺序给出
                    let correctRateArray = [];
                    // 题目参与人数数组
                    let playerAmountArray = [];
                    // 题目加了多少分数组
                    let addScoreArray = [];
                    // 题目扣了多少分数组
                    let minusScoreArray = [];
                    self.questionsPack.forEach((question)=>{
                        questionSortArray.push(question.sort);
                        correctRateArray.push( Math.round(question.correctPlayerAmount / question.playerAmount * 10000)/10000);
                        playerAmountArray.push(question.playerAmount);
                        addScoreArray.push(question.addscore);
                        minusScoreArray.push(-question.minusscore);
                    });

                    // 计算最多参与人数与最少参与人数
                    self.questionMostPlay_playerAmount = max(playerAmountArray);
                    self.questionFewPlay_playerAmount = min(playerAmountArray);

                    // 计算题目设定的一个人全对能拿多少分
                    for(let score of addScoreArray){
                        self.scoreIfAllCorrect += score;
                    }
                    // 计算题目设定的一个人全错会被扣多少分
                    for(let score of minusScoreArray){
                        self.scoreIfAllWrong += score;
                    }
                    self.averageAddScore = Math.round(self.scoreIfAllCorrect / addScoreArray.length * 100) /100;
                    self.averageMinusScore = Math.round(self.scoreIfAllWrong / minusScoreArray.length * 100) /100;


                    // 绘制图表
                    correctRateChart.setOption({
                        xAxis: {
                            data: questionSortArray
                        },
                        series: [{
                            data: correctRateArray
                        }]
                    });
                    // 绘制图表
                    playerAmountChart.setOption({
                        xAxis: {
                            data: questionSortArray
                        },
                        yAxis:{
                            min:'dataMin'
                        },
                        series: [{
                            type:'line',
                            symbol:'circle',
                            symbolSize:5,
                            data: playerAmountArray
                        }]
                    });
                    // 绘制图表
                    scoreSettingChart.setOption({
                        xAxis: {
                            data: questionSortArray
                        },
                        yAxis:{
                            min:'dataMin'
                        },
                        series: [{
                            type:'bar',
                            name:"加分",
                            stack:'hi',
                            data: addScoreArray,
                            itemStyle:{
                                color:['#1bb279'],
                                barBorderRadius: [100,100,0,0]
                            },
                            barMaxWidth:10

                        },{
                            type:'bar',
                            name:'减分',
                            stack:'hi',
                            data: minusScoreArray,
                            itemStyle:{
                                barBorderRadius: [0,0,100,100]
                            },
                            barMaxWidth:10

                        }]
                    });




                });

            }
        },
        created (){
            this.getQuestionsPack();
        },
        watch:{
            $route:function () {
                this.getQuestionsPack();
            }
        },
        mounted (){
            this.initCharts();

            window.onresize = throttle(()=>{
                correctRateChart.resize();
                playerAmountChart.resize();
                scoreSettingChart.resize();
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
.question-list{
    user-select: none;
    padding-left: 0;
    list-style: none;

    li{
        display: flex;
        padding: 12px;
        align-items: center;
        border-top: 1px dashed #efefef;
        border-bottom: 1px dashed #efefef;
        
        &:hover{
            background: #fafafa;
        }
    }

    .question-sort{
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
}

    .chart-statistic{


        margin: 30px 0;
        box-sizing: border-box;
        display: flex;
        width: 100%;
        flex-wrap: wrap;

        align-items: center;
        justify-content: center;

        .chart-statistic-item{
            transition: all .14s ease-in-out;
            min-width: 120px;
            height: 100px;
            text-align: center;
            border-radius: 10px;
            /*background: #fff;*/
            padding: 20px;


            &:hover{
                box-shadow: 0 0 20px 5px rgba(217,60,80,.08);
            }
            h4{
                user-select: none;
                color: #bbb;
                font-weight: normal;
                margin: 0px;
            }

            h1{
                margin: 20px;
            }
        }

    }
</style>