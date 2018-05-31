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

        <Panel title="进入答题时延曲线" description="记录了该玩家提交验证码的时间与手速最快的玩家之间的对比">
            <div id="captchaDelayChart" class="chart-container"></div>
            <statistics>
                <statistics-item title="最慢手速" :data="slowestDelay"></statistics-item>
                <statistics-item title="平均手速" :data="averageDelay"></statistics-item>
            </statistics>
        </Panel>

        <Panel title="答题用时曲线" description="记录了该玩家答每一道题时从阅读题目到选择提交选项所花费的时间">
            <div id="answerQuestionDurationChart" class="chart-container"></div>
            <statistics>
                <statistics-item title="用时最长" :data="longestDuration"></statistics-item>
                <statistics-item title="平均用时" :data="averageDuration"></statistics-item>
            </statistics>
        </Panel>

        <Panel title="答题情况统计">
            <ul class="question-list">
                <li v-for="question of questions">
                    <div class="question-container">
                        <div class="question-sort">{{question.sort}}</div><div class="question">{{question.question}}</div>
                    </div>
                    <div class="question-info">
                        <!--<div>{{question.deltaScore}}</div>-->
                        <div :class="{'answer-state':true ,
                                      'correct':question.playerStateText == '答对了',
                                      'wrong':question.playerStateText == '答错了',
                                      'timeout':question.playerStateText == '已超时',
                                      'unjoin':question.playerStateText == '未参与',
                                      }">{{question.playerStateText}}</div>
                        <img class="icon" title="最强黑马" src="../assets/honor_blackHorse.png" v-show="question.isBlackHorse"></img>
                        <img class="icon" title="手速最快" src="../assets/honor_fast.png" v-show="question.isFastPlayer"></img>
                        <img class="icon" title="最先答对" src="../assets/honor_firstCorrect.png" v-show="question.isFirstCorrectPlayer"></img>
                        <img class="icon" title="弹无虚发" src="../assets/honor_highHitRate.png" v-show="question.isHighHitRatePlayer"></img>
                    </div>
                </li>
            </ul>
            <statistics>
                <statistics-item title="答对题数" :data="rightCount"></statistics-item>
                <statistics-item title="答错题数" :data="wrongCount"></statistics-item>
                <statistics-item title="超时题数" :data="timeoutCount"></statistics-item>
                <statistics-item title="未参与题数" :data="unjoinCount"></statistics-item>
                <statistics-item title="成就取得数" :data="achievementCount"></statistics-item>
                <statistics-item title="错题选项偏好" tips="答错题目的时候最喜欢选的选项" :data="mostLikeChosen"></statistics-item>
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
    let captchaDelayChart;
    let answerQuestionDurationChart;

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
                lowestRanking:0,
                averageRanking:0,
                historyRanking:[],
                //问题列表
                questions:[],
                timeoutCount:0,
                unjoinCount:0,
                achievementCount:0,
                // 答错题目中，最喜欢选的选项
                mostLikeChosen:'',
                // 最慢手速
                slowestDelay:0,
                // 平均手速
                averageDelay:0,
                // 用时最久
                longestDuration: 0,
                // 平均用时
                averageDuration: 0
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
                        trigger: 'axis',
                        formatter: function(datas) {
                            let res = `题目 ${datas[0].name} <br/>`;
                            let val;
                            for(var i = 0, length = datas.length; i < length; i++) {
                                val = `${datas[i].value}分`;
                                res += `${datas[i].seriesName}: ${val} <br/>`;
                            }
                            return res;
                        },
                    }
                }));
                // 初始化 排名变化图表
                rankingChangeChart = echarts.init(document.getElementById('rankingChangeChart'));
                // 绘制图表
                rankingChangeChart.setOption(merge(chartOption,{
                    tooltip: {
                        trigger: 'axis',
                        formatter: function(datas) {
                            let res = `题目 ${datas[0].name} <br/>`;
                            let val;
                            for(var i = 0, length = datas.length; i < length; i++) {
                                val = `${datas[i].value}名`;
                                res += `${datas[i].seriesName}: ${val} <br/>`;
                            }
                            return res;
                        },
                    }
                }));
                // 初始化 验证码输入时延图表
                captchaDelayChart = echarts.init(document.getElementById('captchaDelayChart'));
                // 绘制图表
                captchaDelayChart.setOption(merge(chartOption,{
                    tooltip: {
                        trigger: 'axis',
                        formatter: function(datas) {
                            let res = `题目 ${datas[0].name} <br/>`;
                            let val;
                            for(var i = 0, length = datas.length; i < length; i++) {
                                val = `比手速最快的玩家慢 ${Math.abs(datas[i].value)}秒`;
                                res += `${datas[i].seriesName}: ${val} <br/>`;
                            }
                            return res;
                        },
                    }
                }));
                // 初始化 答题用时图表
                answerQuestionDurationChart = echarts.init(document.getElementById('answerQuestionDurationChart'));
                // 绘制图表
                answerQuestionDurationChart.setOption(merge(chartOption,{
                    tooltip: {
                        trigger: 'axis',
                        formatter: function(datas) {
                            let res = `题目 ${datas[0].name} <br/>`;
                            let val;
                            for(var i = 0, length = datas.length; i < length; i++) {
                                val = `${datas[i].value}秒`;
                                res += `${datas[i].seriesName}: ${val} <br/>`;
                            }
                            return res;
                        },
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
                    self.historyScore = response.data.player.historyscore.split(",").filter((score,index)=>{
                        // index > 0 是为了筛选掉玩家刚来到教室时候的默认数据
                        if(score != "" && index>0){
                            return true;
                        }
                    });
                    self.historyRanking = response.data.player.historyranking.split(",").filter((ranking,index)=>{
                        if(ranking != "" && index>0){
                            return true;
                        }
                    });
                    self.highestScore = utils.max(self.historyScore);
                    self.averageScore = (Math.round(utils.average(self.historyScore)*100)/100).toFixed(2);
                    self.lowestRanking = utils.max(self.historyRanking);
                    self.averageRanking = (Math.round(utils.average(self.historyRanking)*100)/100).toFixed(2);
                    // 用来记录玩家答错时选了ABCD哪个选项多少次
                    let chooseCount = [0,0,0,0];
                    // 题目的限制时间数组
                    let questionAvailableTimeArray = [];
                    self.questions = response.data.questions.filter((question)=>{
                        questionAvailableTimeArray.push(question.availabletime);
                        if(question.playerAmount>0){
                            // 计算玩家的状态文本
                            switch (question.playerState){
                                case "unjoin":
                                    question.playerStateText = "未参与";
                                    self.unjoinCount++;
                                    break;
                                case "timeout":
                                case "joined":
                                    question.playerStateText = "已超时";
                                    self.timeoutCount++;
                                    break;
                                case "done":
                                    if(question.isCorrect){
                                        question.playerStateText = "答对了";
                                    }else {
                                        question.playerStateText = "答错了";
                                    }
                                    break;
                            }
                            if(question.isBlackHorse || question.isFastPlayer || question.isFirstCorrectPlayer || question.isHighHitRatePlayer){
                                self.achievementCount++;
                            }

                            if(!question.isCorrect && question.playerState == "done"){
                                let playerChoose = question.playerChoose;
                                chooseCount[playerChoose.charCodeAt()-65]++;
                            }

                            return true;
                        }
                    });
                    // 验证码时延数组
                    let captchaDelayArray = [];
                    // 答题用时数组
                    let answerQuestionDurationArray = [];
                    // 场均分数数组
                    let allPlayersAverageScoreArray = [];
                    // 场均排名数组
                    let allPlayersAverageRankingArray = [];
                    // 场均输入验证码时延数组
                    let allPlayersAverageCaptchaDelayArray = [];
                    // 场均答题用时数组
                    let allPlayersAverageAnswerTimeArray = [];
                    // 初始化数组，避免出现有些时候没有数据导致bug
                    for(let i =0;i<self.historyScore.length;i++){
                        captchaDelayArray.push('-');
                        answerQuestionDurationArray.push('-');
                        allPlayersAverageScoreArray.push('-');
                        allPlayersAverageRankingArray.push('-');
                        allPlayersAverageCaptchaDelayArray.push('-');
                        allPlayersAverageAnswerTimeArray.push('-');
                    }
                    response.data.stageInfo.forEach((stage,index)=>{
                        captchaDelayArray[index]=stage.captcha_input_delay;
                        if(stage.question_state != 'timeout' && stage.question_state != 'joined'){
                            let joinQuestionTime = new Date(stage.join_question_time).getTime()/1000;
                            let questionDoneTime = new Date(stage.question_done_time).getTime()/1000;
                            answerQuestionDurationArray[index]=questionDoneTime-joinQuestionTime;
                        }


                    });
                    response.data.questionsStatistic.forEach((questionsStatistic,index)=>{
                        allPlayersAverageScoreArray[index] = questionsStatistic.average_player_score;
                        allPlayersAverageRankingArray[index] = questionsStatistic.average_player_ranking;
                        allPlayersAverageCaptchaDelayArray[index] = questionsStatistic.average_player_captcha_delay;
                        allPlayersAverageAnswerTimeArray[index] = questionsStatistic.average_player_answer_time;
                    });
                    self.slowestDelay = Math.abs(utils.min(captchaDelayArray)).toFixed(2);
                    self.averageDelay = Math.abs(utils.average(captchaDelayArray)).toFixed(2);
                    self.longestDuration = utils.max(answerQuestionDurationArray);
                    self.averageDuration = utils.average(answerQuestionDurationArray).toFixed(2);




                    // 计算玩家在答错的情况下最喜欢选的选项
                    self.mostLikeChosen = String.fromCharCode(((chooseCount)=>{
                        let max = chooseCount[0];
                        let maxIndex = 0;
                        chooseCount.forEach((item,index)=>{
                            if(item>max){
                                maxIndex = index;
                            }
                        });
                        return maxIndex;
                    })(chooseCount)+65);

                    // 玩家分数变化图表
                    scoreChangeChart.setOption({
                        legend:{
                            show:true
                        },
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
                        },{
                            type:'line',
                            name:"场均分数",
                            data: allPlayersAverageScoreArray,
                            itemStyle:{
                                color:['#8e5ab455']
                            }
                        }]
                    });

                    // 玩家排名变化图表
                    rankingChangeChart.setOption({
                        legend:{
                            show:true
                        },
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
                            name:"排名",
                            data: self.historyRanking,
                            itemStyle:{
                                color:['#d93c50']
                            }
                        },{
                            type:'line',
                            name:"场均排名",
                            data: allPlayersAverageRankingArray,
                            itemStyle:{
                                color:['#8e5ab455']
                            }
                        }]
                    });

                    // 验证码输入时延图表
                    captchaDelayChart.setOption({
                        legend:{
                            show:true
                        },
                        xAxis: {
                            data: utils.rangeArray(0,self.historyScore.length-1),
                        },
                        yAxis:[
                            {
                                type: 'value',
                                max: 0,
                            }
                        ],
                        series: [{
                            type:'line',
                            name:"时延",
                            data: captchaDelayArray,
                            itemStyle:{
                                color:['#d93c50']
                            }
                        },{
                            type:'line',
                            name:"场均时延",
                            data: allPlayersAverageCaptchaDelayArray,
                            itemStyle:{
                                color:['#8e5ab455']
                            }
                        }]
                    });
                    // 答题用时图表
                    answerQuestionDurationChart.setOption({
                        legend:{
                            show:true
                        },
                        xAxis: {
                            data: utils.rangeArray(0,self.historyScore.length-1),
                        },
                        yAxis:[
                            {
                                type: 'value',
                                min: 0
                            }
                        ],
                        series: [{
                            type:'line',
                            name:"用时",
                            data: answerQuestionDurationArray,
                            itemStyle:{
                                color:['#d93c50']
                            }
                        },{
                            type:'line',
                            name:"题目规定时长",
                            data: questionAvailableTimeArray,
                            itemStyle:{
                                color:['#d93c5033']
                            },
                            lineStyle:{
                            }
                        },{
                            type:'line',
                            name:"场均用时",
                            data: allPlayersAverageAnswerTimeArray,
                            itemStyle:{
                                color:['#8e5ab455']
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
                captchaDelayChart.resize();
                answerQuestionDurationChart.resize();
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

    .question-list{
        user-select: none;
        padding-left: 0;
        list-style: none;

        li{
            display: flex;
            padding: 12px;
            align-items: center;
            justify-content: space-between;
            border-top: 1px dashed #efefef;

            &:last-of-type{
                border-bottom: 1px dashed #efefef;
            }

            &:hover{
                background: #fafafa;
            }

            .question-container{
                display: flex;
                align-items: center;
                flex: 1;

                .question-sort{
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
                .question{
                    flex: 1;
                }
            }
            .question-info{
                display: flex;
                justify-content: flex-end;
                justify-self: flex-end;
                width: 200px;
            }

            // 问题回答状态
            .answer-state{
                padding: 4px 8px;
                border-radius: 100px;
                color: #fff;
                font-size: 10px;
                margin-right: 4px;
                &.correct{
                    background: #39a551;
                }
                &.wrong{
                    background: @main-color;
                }
                &.timeout{
                    background: #fac900;
                }
                &.unjoin{
                    background: #888;
                }
            }

            .icon{
                height: 20px;
                margin-right: 4px;

                &:last-of-type{
                    margin-right: 0;
                }
            }

            @media(max-width: 600px){
                &{
                    display: block;

                    .question-container{
                        width: 100%;
                    }

                    .question-info{
                        width: 100%;
                        margin-top: 20px;
                        justify-content: flex-start;
                    }
                }
            }
        }


    }
</style>