<template>
    <div class="search-box-container">
        <label for="inputbox">
        </label>
        <input v-model="search" id="inputbox" class="search-input" :placeholder="placeholder">
        <div class="result-container">
            <ul>
                <router-link tag="li" v-for="item of resultList" :to="'player-profile/'+item.id">
                    <span class="name">{{item.name}}</span><span class="tel">{{item.tel}}</span>
                </router-link>
            </ul>
        </div>
    </div>
</template>

<script>
    import utils from "../lib/utils";
    import axios from 'axios';
    import qs from 'qs';
    import api from '../api'

    export default {
        name: "SearchBox",
        props:{
            placeholder:{
                default:""
            }
        },
        data (){
            return{
                resultList:[
                    {
                        id:5,
                        name:"不知道",
                        tel:"18814549845"
                    }
                ],
                search:''
            }
        },
        methods:{
            // 搜索玩家
            searchPlayers (){
                let self = this;

                    console.log("Asdad");
                    axios.get(process.env.API_HOST + api.searchPlayers,qs.stringify(self.search));

            }
        },
        created(){
            
        },
        watch:{
            search:function(newValue,oldValue){
                utils.throttle(()=>{
                    this.searchPlayers();
                },500,700)();
            }
        }
    }
</script>

<style scoped lang="less">
    .search-box-container{
        position: relative;
        width: 100%;
        height: 30px;
        margin-bottom: 20px;
    }
    .search-input{
        box-sizing: border-box;
        width: 100%;
        height: 30px;
        line-height: 30px;
        padding-left: 8px;
        border-radius: 5px;
        border: 1px solid #ccc;
        outline: none;

        &:focus{
            border: 1px solid #d93c50;
            box-shadow: 0 0 20px 5px rgba(217, 60, 80, .08);
        }
    }
    .search-icon{
        font-size: 14px;
        fill: red;
    }
    .result-container{
        position:absolute;
        top: 32px;
        width: 100%;
        background: #fff;
        box-shadow: 0 0 20px 5px rgba(217, 60, 80, .18);
        border-radius: 5px;
        
        ul{
            list-style: none;
            padding: 0;
            margin: 0;

            li{
                box-sizing: border-box;
                padding: 12px 20px;
                .name{
                    font-weight: bold;
                    color: #333;
                    margin-right: 8px;
                }
                .tel{
                    color: #ccc;
                }
            }
        }
    }
</style>