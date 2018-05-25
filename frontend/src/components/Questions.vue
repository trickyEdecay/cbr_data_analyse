<template>
    <div>
        <Panel title="题目准确率" description="每一道题目的答题准确率">
            <div id="correctRateChart" class="chart-container"></div>
        </Panel>
        <Panel title="题目参与人数" description="统计了每一个题目有多少人参与">
            <div id="playerAmountChart" class="chart-container"></div>
                <statistics>
                    <statistics-item title="账号注册数" :data="playerAmount"></statistics-item>
                    <statistics-item title="最多参与数" :data="questionMostPlay_playerAmount"></statistics-item>
                    <statistics-item title="最少参与数" :data="questionFewPlay_playerAmount"></statistics-item>
                </statistics>
        </Panel>
        <Panel title="题目加减分设定" description="每一道题目的加分与减分设定">
            <div id="scoreSettingChart" class="chart-container"></div>
                <statistics>
                    <statistics-item title="全部答对总分" :data="scoreIfAllCorrect"></statistics-item>
                    <statistics-item title="平均加分" :data="averageAddScore"></statistics-item>
                    <statistics-item title="平均扣分" :data="averageMinusScore"></statistics-item>
                </statistics>
        </Panel>
        <Panel title="题目答题时间设定" description="每一个题目都有一个答题时限，如果玩家没能在答题时限内把题目完成了，则视为超时，相关指标还有一道题目的总字数，影响用户的阅读时间。">
            <div id="timeSettingChart" class="chart-container"></div>
            <statistics>
                <statistics-item title="平均超时人数" :data="averageTimeOutPlayerAmount"></statistics-item>
                <statistics-item tips="完全没有超时情况的题目数量" title="完美时设" :data="perfectTimeSettingCount"></statistics-item>
            </statistics>
        </Panel>
        <Panel title="题目幸运儿设定" description="每道题目都规定了能被加分的人数上限">
            <div id="luckyPlayerChart" class="chart-container"></div>
        </Panel>
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
    import Statistics from '@/components/Statistics';
    import StatisticsItem from '@/components/StatisticsItem';
    import echarts from 'echarts';
    import merge from 'webpack-merge';
    import utils from '@/lib/utils';


    // 准确率图表
    let correctRateChart ;
    // 题目参与人数图表
    let playerAmountChart;
    // 加减分设定图表
    let scoreSettingChart;
    // 题目答题时间设定图表
    let timeSettingChart;
    // 题目幸运儿设定图表
    let luckyPlayerChart;


    export default {
        name: "Questions",
        components: {Panel,Statistics,StatisticsItem},
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
                averageMinusScore:0,
                // 平均超时人数
                averageTimeOutPlayerAmount:0,
                // 完全没有超时情况的题目数量
                perfectTimeSettingCount:0
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
                // 初始化 加减分设定图表
                timeSettingChart = echarts.init(document.getElementById('timeSettingChart'));
                // 绘制图表
                timeSettingChart.setOption(merge(chartOption,{
                    grid:{
                        top:36,
                        bottom:20,
                        left:36,
                        right:80
                    },
                    tooltip: {
                        trigger: 'axis',
                        formatter: function(datas) {
                            let res = `题目 ${datas[0].name} <br/>`;
                            let val;
                            for(var i = 0, length = datas.length; i < length; i++) {
                                val = `${datas[i].value}`;
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
                // 初始化 加减分设定图表
                luckyPlayerChart = echarts.init(document.getElementById('luckyPlayerChart'));
                // 绘制图表
                luckyPlayerChart.setOption(merge(chartOption,{
                    grid:{
                        top:36,
                        bottom:20,
                        left:36,
                        right:36
                    },
                    tooltip: {
                        trigger: 'axis',
                        formatter: function(datas) {
                            let res = `题目 ${datas[0].name} <br/>`;
                            let val;
                            for(var i = 0, length = datas.length; i < length; i++) {
                                val = `${datas[i].value}`;
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
                    // 题目时间设定
                    let timeSettingArray = [];
                    // 答题超时用户数量数组
                    let timeoutPlayerAmountArray = [];
                    // 题目字数统计数组
                    let questionWordCountArray = [];
                    // 题目幸运儿人数数组
                    let luckyPlayerAmountArray = [];
                    // 每一道题的答对人数数组
                    let correctPlayerAmountArray = [];
                    self.questionsPack.forEach((question)=>{
                        questionSortArray.push(question.sort);
                        correctRateArray.push( Math.round(question.correctPlayerAmount / question.playerAmount * 10000)/10000);
                        playerAmountArray.push(question.playerAmount);
                        addScoreArray.push(question.addscore);
                        minusScoreArray.push(-question.minusscore);
                        timeSettingArray.push(question.availabletime);
                        timeoutPlayerAmountArray.push(question.timeoutPlayerAmount);
                        questionWordCountArray.push(question.question.length+question.a.length+question.b.length+question.c.length+question.d.length);
                        luckyPlayerAmountArray.push(question.peoplelimit);
                        correctPlayerAmountArray.push(question.correctPlayerAmount);
                    });

                    // 计算最多参与人数与最少参与人数
                    self.questionMostPlay_playerAmount = utils.max(playerAmountArray);
                    self.questionFewPlay_playerAmount = utils.min(playerAmountArray);

                    // 计算题目设定的一个人全对能拿多少分
                    for(let score of addScoreArray){
                        self.scoreIfAllCorrect += score;
                    }
                    // 计算题目设定的一个人全错会被扣多少分
                    for(let score of minusScoreArray){
                        self.scoreIfAllWrong += score;
                    }
                    // 平均加扣分
                    self.averageAddScore = Math.round(self.scoreIfAllCorrect / addScoreArray.length * 100) /100;
                    self.averageMinusScore = Math.round(self.scoreIfAllWrong / minusScoreArray.length * 100) /100;

                    // 没有超时情况的题目数
                    let noTimeOutQuestionsCount = 0;
                    // 所有题目超时人数总和
                    let timeoutPlayerSum = 0;
                    for(let questionTimeOutCount of timeoutPlayerAmountArray){
                        timeoutPlayerSum+=questionTimeOutCount;
                        if(questionTimeOutCount === 0){
                            noTimeOutQuestionsCount++;
                        }
                    }

                    // 平均超时人数
                    self.averageTimeOutPlayerAmount = Math.round(timeoutPlayerSum / timeoutPlayerAmountArray.length * 100) /100;
                    // 没有超时情况的题目数
                    self.perfectTimeSettingCount = noTimeOutQuestionsCount;


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
                    // 绘制图表
                    timeSettingChart.setOption({
                        legend:{
                            show:true
                        },
                        xAxis: {
                            data: questionSortArray,
                            axisPointer:{
                                type:"shadow",
                                shadowStyle:{
                                    color:'rgba(233,233,233,.3)'
                                }
                            }
                        },
                        yAxis:[
                            {
                                type: 'value',
                                name: '答题允许时间',
                                min: 0,
                                max: 'dataMax',
                                position: 'left',
                                axisLine: {
                                    lineStyle: {

                                    }
                                },
                                axisLabel: {
                                    formatter: '{value}s'
                                }
                            },{
                                type: 'value',
                                name: '超时',
                                min: 0,
                                max: 'dataMax',
                                splitLine:{
                                    show:false
                                },
                                position: 'right',
                                // axisLine:{show:false},
                                // axisTick:{show:false},
                                axisLabel: {
                                    show: true
                                }
                            },{
                                type: 'value',
                                name: '字数',
                                nameTextStyle:{
                                    align:'center'
                                },
                                min: 0,
                                max: 'dataMax',
                                splitLine:{
                                    show:false
                                },
                                offset: 40,
                                position: 'right',
                                // axisLine:{show:false},
                                // axisTick:{show:false},
                                axisLabel: {
                                    show: true
                                }
                            }
                        ],
                        series: [{
                            type:'bar',
                            name:"答题允许时间",
                            data: timeSettingArray,
                            itemStyle:{
                                color:['#d93c50'],
                                barBorderRadius: [100,100,0,0]
                            },
                            barMaxWidth:10
                        },{
                            type:'bar',
                            name:"超时",
                            yAxisIndex: 1,
                            data: timeoutPlayerAmountArray,
                            itemStyle:{
                                color:['#8e5ab4'],
                                barBorderRadius: [100,100,0,0]
                            },
                            barMaxWidth:10
                        },{
                            type:'bar',
                            name:"字数",
                            yAxisIndex: 2,
                            data: questionWordCountArray,
                            itemStyle:{
                                color:['#de66a2'],
                                barBorderRadius: [100,100,0,0]
                            },
                            barMaxWidth:10
                        }]
                    });
                    // 绘制图表
                    luckyPlayerChart.setOption({
                        legend:{
                            show:true
                        },
                        xAxis: {
                            data: questionSortArray,
                            axisPointer:{
                                type:"shadow",
                                shadowStyle:{
                                    color:'rgba(233,233,233,.3)'
                                }
                            }
                        },
                        yAxis:[
                            {
                                type: 'value',
                                name: '幸运儿设定',
                                min: 0,
                                max: 'dataMax',
                                position: 'left',
                                axisLine: {
                                    lineStyle: {

                                    }
                                },
                                axisLabel: {
                                    formatter: '{value}'
                                }
                            }
                        ],
                        series: [{
                            type:'bar',
                            name:"幸运儿人数",
                            data: luckyPlayerAmountArray,
                            itemStyle:{
                                color:['#d93c50'],
                                barBorderRadius: [100,100,0,0]
                            },
                            barMaxWidth:10
                        },{
                            type:'bar',
                            name:"答对人数",
                            data: correctPlayerAmountArray,
                            itemStyle:{
                                color:['#8e5ab4'],
                                barBorderRadius: [100,100,0,0]
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

            window.onresize = utils.throttle(()=>{
                correctRateChart.resize();
                playerAmountChart.resize();
                scoreSettingChart.resize();
                timeSettingChart.resize();
                luckyPlayerChart.resize();
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
</style>