import Vue from 'vue'
import VueRouter from 'vue-router'

import HelloWorld from '../components/HelloWorld'
import autoriseComponent from '../components/autoriseComponent'

import store from '../store/store';
import allLibs from '../lib/libs'

Vue.use(VueRouter);

let router = new VueRouter ( {
    mode: 'history',    
    routes: [
            {
                path: '/',
                name: 'service',
                meta: {title: "Панель управления"},
                component: HelloWorld
            },
            {
                path: '/login',
                name: 'login',
                meta: {title: "Авторизация в LS CRM"},
                component: autoriseComponent
            }

            
        ]
    }
);

router.beforeEach((to, from, next) => {
    document.title = to.meta.title;
       
     let autorise = allLibs.getCookie("servautorise"); 
    if (autorise != undefined) 
    {
        store.dispatch('chengeAutorise',  true);
        store.dispatch('chengeUserName',   localStorage.getItem('fio'));
    }
    else {
        store.dispatch('chengeAutorise',  false);
        if (to.name !== "login") 
           allLibs.reloginUser();
    } 

    if ((!store.getters.AUTORISE) && (to.name !== "login")) {
        router.push({ name: 'login' })
    }   
    
    next();
});

export default router;