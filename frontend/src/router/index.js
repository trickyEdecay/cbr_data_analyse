import Vue from 'vue'
import Router from 'vue-router'
import HelloWorld from '@/components/HelloWorld'
import Questions from '@/components/Questions'

Vue.use(Router)

export default new Router({
    mode:'history',
  routes: [
    {
      path: '/',
      name: 'HelloWorld',
      component: HelloWorld
    },
    {
      path: '/questions',
      name: 'Questions',
      component: Questions
    }
  ]
})
