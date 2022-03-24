<template>
   <v-container ma-0  style="max-width: 100%">
    <v-row>
        <v-col>
          <h1>Массовая проверка номеров</h1>
        </v-col>
    </v-row> 

    <v-form ref = "massAlertNumber">
        <v-row>
            <v-col>
                <v-textarea
                v-model = "allNumbers"
                solo
                name="input-7-4"
                label="Введите номера пропусков для проверки"
                :rules="requiredRules"
                ></v-textarea>
            </v-col>
        </v-row>
        <v-row>
            <v-col>
                <v-btn
                depressed
                color="success"
                @click="sendAlerts"
                >
                    Начать проверку
                </v-btn>
            </v-col>
        </v-row>
        <v-row>
            <v-col>
                <v-alert
                border="right"
                colored-border
                v-bind:type = errorMsgOk
                elevation="2"
                v-show="errorMsgVisible"
                >{{errorMsg}}</v-alert>
            </v-col>
        </v-row>
        <v-row>
            <v-col>
                <table class = "mainTable"> 
                    <tbody>
                        <tr v-for="(item, i) in numbers" :key="i" >
                            <td>{{item.carr_number}}</td>
                            <td>{{item.pass_number}}</td>
                            <td>{{item.email}}</td>
                            <td>{{item.result}}</td>
                        </tr>
                    </tbody>
                </table>
            </v-col>
        </v-row>        
    </v-form>

    </v-container>
</template>

<script>
import axios from 'axios';
import {mapGetters} from 'vuex'
export default {

    data() {
        return {
            allNumbers:"",
            numbers: [],
            requiredRules:[
                value => !!value || 'Должно быть заполнено.'
            ],

            errorMsg:"Заполните все обязательные поля помеченные *",
            errorMsgOk: "error",
            errorMsgVisible:false
        }
    },

    computed: {
        ...mapGetters (["REST_API_PREFIX"])
    },

    methods: {


        sendAlerts() {
            if (this.$refs.massAlertNumber.validate()) { 
               let mainnumbers = this.allNumbers.split(/(?:\r\n|\r|\n)+/g)
               console.log(mainnumbers);
               this.numbers = []
               mainnumbers.forEach((elem) => {
                   axios.get(this.REST_API_PREFIX + 'mass_check',
                {
                        params: {
                            number: elem,
                        }
                    })
                    .then( (resp) => {
                        this.numbers.push(resp.data)
                        console.log(this.numbers)
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
                    
                //     console.log(this.numbers);
               })
            }
        }
    }
}
</script>

<style>
    .mainTable {
        width: 100%;
        border-top: 1px solid lightgray;
        border-left: 1px solid lightgray;
        border-spacing: 0;
    }

    .mainTable td{
        padding: 5px 15px;
        border-bottom: 1px solid lightgray;
        border-right: 1px solid lightgray;
    }
</style>