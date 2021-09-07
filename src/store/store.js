import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store ({
    state: {
        rest_api_prefix:"https://lightsnab.ru/wp-json/lscrm/v2/",
        // Поля авторизации
        autorise: false,
        userName: "",
        userEmail: "",
        userPodrazdelenie: "",
        userDolgnost: "",

        loginState:"autorise", //Окно которое отображаетс на странице авторизации / регистрации

    },

    actions: {
        // Экшкны авторизации
        chengeAutorise(ctx, value){
            ctx.commit('updateAutorise', value);
        },

        chengeUserName(ctx, value){
            ctx.commit('updateUserName', value);
        },

        chengeLoginState(ctx, kpi){
            ctx.commit('updateLoginState', kpi);
        }
    },

    mutations: {
        // Мутации авторизации
        updateAutorise(state, newVal) {
            state.autorise = newVal;
        },

        updateUserName(state, newVal) {
            state.userName = newVal;
        },
        
        updateLoginState(state, newVal) {
            state.loginState = newVal;
        }

    },
    
    getters: {
        REST_API_PREFIX (state) {
            return state.rest_api_prefix;
        },

        AUTORISE (state) {
            return state.autorise;
        },

        USER_NAME (state) {
            return state.userName;
        },

        getLoginState(state){
            return state.loginState;
        },
       
    }
})