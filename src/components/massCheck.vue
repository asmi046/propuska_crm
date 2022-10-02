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
                    <thead>
                        <tr>
                            <th>Номер</th>
                            <th>Статус</th>
                            <th>Тип пропуска</th>
                            <th>Серия пропуска</th>
                            <th>Номер пропуска</th>
                            <th>Дата начала</th>
                            <th>Дата окончания</th>
                            <th>Время</th>
                        </tr>
                    </thead> 
                    
                    <tbody>
                        <tr v-for="(item, i) in numbers" :key="i" :class = "{isChectdNow: item.data.length != 0}" >
                            <td>{{item.number}}</td>
                            <td>{{item.status}}</td>
                            <td>{{ (item.data.length != 0)?item.data.pass_zone:""}}</td>
                            <td>{{ (item.data.length != 0)?item.data.series:""}}</td>
                            <td>{{ (item.data.length != 0)?item.data.pass_number:""}}</td>
                            <td>{{ (item.data.length != 0)?item.data.valid_from:""}}</td>
                            <td>{{ (item.data.length != 0)?item.data.valid_to:""}}</td>
                            <td>{{ (item.data.length != 0)?item.data.type_pass:""}}</td>
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
                            number: elem.replace(/\s/g,''),
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

    /* .mainTable tbody tr:nth-child(2n-1) td */
    .isChectdNow td
     {
        background-color: lightgreen;
    }

    .mainTable th,
    .mainTable td{
        padding: 5px 15px;
        border-bottom: 1px solid lightgray;
        border-right: 1px solid lightgray;
    }

    @media screen and (max-width:920px) { 
        .mainTable thead{
            display: none;
        }

        .mainTable tbody tr{
            width: 100%;
            display: flex;
            flex-direction: column;
        }
        .mainTable tbody tr td,
        .mainTable tbody tr th{
            width: 100%;
        }
    }
</style>