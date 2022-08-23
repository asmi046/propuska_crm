import Vue from 'vue'
import axios from 'axios';
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store ({
    state: {
        rest_api_prefix:"https://back2.propuska-mkad-ttk-sk.ru/wp-json/lscrm/v2/",
        // Поля авторизации
        autorise: false,
        userName: "",
        userEmail: "",
        userPodrazdelenie: "",
        userDolgnost: "",

        loginState:"autorise", //Окно которое отображаетс на странице авторизации / регистрации
        // Поля для меню приложения
        showPanel:false,
        // Список номеров
        numberList:[],
        statuses:[],
        curentStatus:""
    },

    actions: {
        updateNumberList(ctx, param){
            axios.get(ctx.state.rest_api_prefix + 'get_number_table',
            {
                params: {
                    status: param.status, 
                    type: param.type, 
                    searchstr: param.searchstr, 
                }
            })
            .then( (resp) => {
                ctx.commit('updateNumberList', resp.data.result);
                ctx.commit('updateStatusesList', resp.data.statuses);
                console.log(resp.data.result)
            })
            .catch((error) => {
                        let rezText = "";
                        if (error.response)
                        {
                            rezText = error.response.data.message;
                        } else 
                        if (error.request) {
                            rezText = error.message;
                        } else {
                            rezText = error.message;
                        }
                        
                        console.log(error.config);
                        console.log(rezText);
            });

            
        },
        showedPanel(ctx, value){
            ctx.commit('showedPanel', value);
        },
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
        updateNumberList(state, newVal) {
            state.numberList = newVal;
        },

        updateStatusesList(state, newVal) {
            state.statuses = newVal;
        },

        showedPanel(state, newVal) {
            state.showPanel = newVal;
        },

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
        STATUSES(state) {
            return state.statuses;
        },

        NUMBER_LIST(state) {
            return state.numberList;
        },

        SHOW_PANEL(state) {
            return state.showPanel;
        },
        
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