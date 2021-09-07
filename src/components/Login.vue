<template>
    <div class = "loginBlk">
        
        <form action="" id="loginForm">
            <v-text-field
            label="Логин (e-mail)"
            placeholder="введите e-mail"
            v-model="autinfo.mail"
            type = "email"
            ></v-text-field>
            
            <v-text-field
            label="Пароль"
            placeholder="введите свой пароль"
            v-model="autinfo.pass"
            type = "password"
            ></v-text-field>
            

            <v-checkbox
            v-model = "savePassword"
            label = "Запомнить пароль"
            color = "info"
            >
            </v-checkbox>


            <v-btn
            color="success"
            @click.prevent="getAutorisation"
            >Войти</v-btn>
        </form>
        

        <v-alert
        border="right"
        colored-border
        v-bind:type = errorMsgOk
        elevation="2"
        v-show="errorMsgVisible"
        >{{errorMsg}}</v-alert>

        <!-- <form-msg :error-msg = "errorMsg" :error-msg-ok = "errorMsgOk"  :error-msg-visible = "errorMsgVisible"></form-msg> -->
        
        <a @click.prevent="toRegister" href="#" class="controlLnk">Регистрация в системе</a><br/>
        <a @click.prevent="toPassRec" href="#" class="controlLnk">Восстановить пароль</a>
      
    </div>
</template>

<script>
    import axios from 'axios';
    import allLibs from '../lib/libs';

    import {mapGetters} from 'vuex'

    export default {
        
        data() {
      
            return {
                autinfo: {
                    mail:"",
                    pass: ""
                },

                savePassword:false,

                errorMsg:"Заполните все обязательные поля помеченные *",
                errorMsgOk: "error",
                errorMsgVisible:false
            }
        },
        
        computed: {
            ...mapGetters (["REST_API_PREFIX"])
        },

        mounted: function() {
            if (allLibs.getCookie("userlogin") != undefined)
            {
                this.savePassword = true;
                this.autinfo.mail = allLibs.getCookie("userlogin");
                this.autinfo.pass = allLibs.getCookie("userpass");
            }
        },

        methods: {
            toRegister() {
                this.$store.dispatch('chengeLoginState',  "register");
                
            }, 

            toPassRec() {
                this.$store.dispatch('chengeLoginState',  "passrec");
            },

            getAutorisation() {
                 if ((this.autinfo.mail == "") || (this.autinfo.pass == "")) {
                    this.errorMsg = "Заполните все обязательные поля";
                    this.errorMsgVisible = true;
                    return;
                }


                axios.get(this.REST_API_PREFIX + 'userautorization',
                {
                    params: {
                        autinfo: this.autinfo
                    }
                })
                .then( (response) => {
                     allLibs.loginUser(response, this.savePassword,this.autinfo.pass);

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
                    this.errorMsg = rezText;
                    this.errorMsgVisible = true;
                });
            }


        }
    }
</script>

<style scoped>
    .loginBlk {
        margin-bottom: 20px;
    }
</style>