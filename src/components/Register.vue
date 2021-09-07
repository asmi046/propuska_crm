<template>
    <div class = "loginBlk">
        <h2>Регистрация</h2>
        <form class = "windowForm" action="" id="registerForm">
            <v-text-field
            v-model = "reginformation.fio"
            type = "text"
            label = "Ф.И.О."
            ></v-text-field>
            
            <v-text-field
            v-model = "reginformation.mail"
            type = "email"
            label = "e-mail"
            ></v-text-field>
            
            <v-text-field
            v-model = "reginformation.dolgnost"
            type = "text"
            label = "Должность"
            ></v-text-field>
            
            <v-text-field
            v-model = "reginformation.pass"
            type = "password"
            label = "Пароль"
            ></v-text-field>

            <v-btn
            color="success"
            @click.prevent="registerUser"
            >Регистрация</v-btn>
        </form>
        <v-alert
        border="right"
        colored-border
        v-bind:type = errorMsgOk
        elevation="2"
        v-show="errorMsgVisible"
        >{{errorMsg}}</v-alert>
        
        <!-- <form-msg :error-msg = "errorMsg" :error-msg-ok = "errorMsgOk"  :error-msg-visible = "errorMsgVisible"></form-msg> -->

        <a @click.prevent="toAutorise" href="#" class="controlLnk">Войти в систему</a>
       
    </div>
</template>

<script>
    import axios from 'axios'
    import {mapGetters} from 'vuex'

    export default {
        data() {
            return {  
                reginformation: {
                    fio: "",
                    mail: "",
                    podrazdelenie: "",
                    dolgnost: "",
                    pass: "",
                },
                errorMsg:"Заполните все обязательные поля помеченные *",
                errorMsgOk: "error",
                errorMsgVisible:false
            }
        },

        computed: {
            ...mapGetters (["REST_API_PREFIX"])
        },

        methods: {
            toAutorise() {
                this.$store.dispatch('chengeLoginState',  "autorise");
            },

            registerUser() {
                this.errorMsgVisible = false;
                this.errorMsgOk = "error";
                
                console.log(this.reginformation);

                if (
                    (this.reginformation.fio == "") ||
                    (this.reginformation.mail == "")  || 
                    (this.reginformation.dolgnost == "") || 
                    (this.reginformation.pass == "")
                    ) {this.errorMsgVisible = true; return;}
                
                axios.get(this.REST_API_PREFIX + 'getregister',
                {
                    params: {
                        reginfo: this.reginformation
                    }
                })
                .then( (resp) => {
                    this.reginformation.fio == "";
                    this.reginformation.mail == "";
                    this.reginformation.podrazdelenie == "";
                    this.reginformation.dolgnost == "";
                    this.reginformation.pass == "";
                    this.errorMsg = "Вы успешно зарегистрированны. Ждите подтверждения регистрации.";
                    this.errorMsgOk = "success";
                    this.errorMsgVisible = true;

                    console.log(resp);
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