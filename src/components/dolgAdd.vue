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
                        >
                        Добавить список номеров
                    </v-btn>
                </v-form>
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
    .coll_border {
        border-right: 1px solid black;   
    }
</style>