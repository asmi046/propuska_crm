import Vue from 'vue'
import VueRouter from 'vue-router'

import mainPage from '../components/mainPage'
import addNumber from '../components/addNumber'
import addNumbers from '../components/addNumbers'
import autoriseComponent from '../components/autoriseComponent'
import checkNumber from '../components/checkNumber'
import chengeNumber from '../components/chengeNumber'

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
                component: mainPage
            },
            {
                path: '/login',
                name: 'login',
                meta: {title: "Авторизация в системе"},
                component: autoriseComponent
            },
            {
                path: '/add_number',
                name: 'addone',
                meta: {title: "Добавить номер"},
                component: addNumber
            },
            {
                path: '/add_numbers',
                name: 'addfull',
                meta: {title: "Пакетное добавление номеров"},
                component: addNumbers
            },
            {
                path: '/check_number/:number?',
                name: 'checknumber',
                meta: {title: "Проверка номеров"},
                component: checkNumber
            },
            {
                path: '/update_number/:number?',
                name: 'updatenumber',
                meta: {title: "Изменить номер"},
                component: chengeNumber
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
        store.dispatch('showedPanel',  true);
    }
    else {
        store.dispatch('chengeAutorise',  false);
        if (to.name !== "login") 
           allLibs.reloginUser();

           store.dispatch('showedPanel',  false);
    } 

    if ((!store.getters.AUTORISE) && (to.name !== "login")) {
        router.push({ name: 'login' })
    }   
    
    next();
});

export default router;