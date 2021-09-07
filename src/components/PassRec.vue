<template>
    <div class = "loginBlk">
        
        <form action="" class = "windowForm" id="passRecForm">
            <v-text-field
            label="Логин (e-mail)"
            placeholder="введите e-mail"
            type = "email"
            ></v-text-field>

            <v-btn
            @click.prevent="recoveryPass"
            color="success"
            >Восстановить</v-btn>
            <!-- <input v-model="mail" type = "email" placeholder="e-mail" name = "passrec_mail" />
            <button @click.prevent="recoveryPass" class = "passRecBtn">Восстановить</button> -->
        </form>
        
        <a @click.prevent="toAutorise" href="#" class="controlLnk">Войти в систему</a>
        
    </div>
</template>

<script>
    import axios from 'axios'

    export default {
        data() {
            return {
                mail:"",

                errorMsg:"Заполните все обязательные поля помеченные *",
                errorMsgOk: false,
                errorMsgVisible:false
            }
        },

        methods: {
            toAutorise() {
                this.$store.dispatch('chengeLoginState',  "autorise");
            },
            
            recoveryPass() {

                if (this.mail == "") {
                   
                    this.errorMsgVisible = true;
                    return;
                }

                axios.get(this.$store.getters.REST_API_PREFIX + 'passrec',
                {
                    params: {
                        mail: this.mail
                    }
                })
                .then( () => {
                    
                    this.errorMsg = "Ваш пароль успешно восстановлен, проверьте рабочий e-mail.";
                    this.errorMsgOk = true;
                    this.errorMsgVisible = true;

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