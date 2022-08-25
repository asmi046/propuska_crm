<template>
  <v-container ma-0  style="max-width: 100%">
        <v-row>
            <v-col>
                <h1>Должники - Добавление</h1>
            </v-col>
        </v-row>
        
        <v-row v-show="errorMsgVisible">
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
        
        <v-row >     
            <v-col class="coll_border"  md = "6" xl = "6" cols="12">
                <v-form ref="searchNumber">
                    <v-text-field
                    placeholder="X###XX###"
                    v-model="addingNumber"
                    v-mask = "'X###XX##N'"
                    label = "Номер авто*"
                    outlined
                    :rules="requiredRules"
                    />

                    <v-btn
                        depressed
                        color="success"
                        @click="search_number"
                        >
                        Найти номер в базе
                    </v-btn>

                    <p style="margin-top: 20px;"><strong>e-mail владельца:</strong> {{addingEmail}} </p>
                    
                    <v-text-field
                    placeholder="Введите имя ответсвенного"
                    v-model="addingName"
                    label = "Имя ответственного"
                    outlined
                    :disabled="addingEmail==''"
                    />

                    <v-btn
                        depressed
                        color="success"
                        :disabled="addingEmail==''"
                        @click="add_dolg"
                        >
                        Добавить в должники
                    </v-btn>
                </v-form>
            </v-col>
            

            
            <v-col  md = "6" xl = "6" cols="12">
                <v-form ref = "massAddNumber">
                    <v-textarea
                    v-model = "addingNumbersList"
                    outlined
                    name="mass-adding"
                    label="Введите номера для массового добавления"
                    :rules="requiredRules"
                    ></v-textarea>

                    <v-btn
                        depressed
                        color="success"
                        :disabled="addingNumbersList==''"
                        @click="mass_add_blk"
                        >
                        Добавить список номеров
                    </v-btn>
                </v-form>
                <h2 style = "margin:20px 0;">Результаты добавления</h2>
                <table class = "mainTable"> 
                    <thead>
                        <tr>
                            <th>Номер</th>
                            <th>e-mail</th>
                            <th>Статус</th>
                        </tr>
                    </thead> 
                    <tbody>
                        <tr v-for="(item, i) in addingNumbersListResult" :key="i" >
                            <td>{{item.number}}</td>
                            <td>{{item.email}}</td>
                            <td><span :class="{addet:item.adding}">{{item.msg}}</span></td>
                        </tr>
                    </tbody>
                </table>
            </v-col>
                
        </v-row>
        
    </v-container>
</template>

<script>
import axios from 'axios';
import {mapGetters} from 'vuex'

export default {
    data() {
        return {
            addingNumber:"",
            addingName:"",
            addingEmail:"",
            addingNumbersList:"",
            addingNumbersListResult:[],
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

    methods:{
        mass_add_blk() {
            this.errorMsgVisible = false;

             axios.get(this.REST_API_PREFIX + 'mass_add_dolgnik',
                {
                    params: {
                        list: this.addingNumbersList
                    }
                })
                .then( (resp) => {

                    this.addingNumbersListResult = resp.data
                    console.log(resp)
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
        },
        add_dolg() {
            this.errorMsgVisible = false;

             axios.get(this.REST_API_PREFIX + 'add_dolgnik',
                {
                    params: {
                        number: this.addingNumber, 
                        name: this.addingName, 
                        email: this.addingEmail, 
                    }
                })
                .then( (resp) => {

                    if (resp.data <= 0) {
                        this.errorMsg = "Номер не добавлен к должникам"
                        this.errorMsgVisible = true
                    } else {
                        this.errorMsg = "Номер не добавлен в базу должников"
                        this.errorMsgOk = "success"
                        this.errorMsgVisible = true
                    }

                    console.log(resp)
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
   
        },
        search_number() {
            this.errorMsgVisible = false;
            console.log(this.addingNumber);
            if (this.$refs.searchNumber.validate()) {
                axios.get(this.REST_API_PREFIX + 'search_number_in_base',
                {
                    params: {
                        number: this.addingNumber, 
                    }
                })
                .then( (resp) => {

                    if (resp.data.length == 0) {
                        this.errorMsg = "Номер не найден"
                        this.errorMsgVisible = true
                    } else {
                        this.addingEmail = resp.data.email
                    }

                    console.log(resp)
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
            } else {
                this.errorMsg = "введите номер авто для поиска";
                this.errorMsgVisible = true;
            }
        }
    }


}
</script>

<style>

    .mainTable span{
        color:red;    
    }

    .mainTable span.addet{
        color:green;    
    }

    .coll_border {
        border-right: 1px solid black;   
    }

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