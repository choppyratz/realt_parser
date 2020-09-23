import Vue from 'vue'
import VueRouter from 'vue-router'
import BootstrapVue from 'bootstrap-vue'
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'

Vue.use(VueRouter)
Vue.use(BootstrapVue)

import App from './components/Main'
import Home from './components/Home'
import PosterPage from './components/PosterPage'


const router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/',
            redirect: '/home/page-1/'
        },
        {
            path: '/poster/:id',
            component: PosterPage
        },
        {
            path: '/home/*',
            component: Home
        },
    ],
});

const app = new Vue({
    el: '#app',
    components: { App },
    router,
});