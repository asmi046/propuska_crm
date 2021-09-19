<template>
  <v-container ma-0  style="max-width: 100%">
    <v-row>
        <v-col>
          <h1>Изменить номер </h1>
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
              >
              
              {{errorMsg}} 
              <router-link :to = "{name: 'service'}"> Перейдите на главную</router-link>
            </v-alert>
        </v-col>
    </v-row>
    
    <v-form v-show="showForm" ref = "addOneNumber">
      <v-row>
        <v-col md = "6" xl = "6" cols="12">
          <v-text-field
          placeholder="X###XX###"
          v-model="dataOfNumber.number"
          v-mask = "'X###XX##N'"
          label = "Номер авто*"
          outlined
          :rules="requiredRules"
           />

          <v-text-field
          v-model="dataOfNumber.email"
          label = "e-mail*"
          outlined
          :rules="requiredRules"
           />

           <v-text-field
          label = "Телефон"
          v-model="dataOfNumber.phone"
          placeholder="+_ (___) ___-__-__"
          v-mask="'+# (###) ###-##-##'"
          outlined
           />

           <v-text-field
           v-model="dataOfNumber.sts"
          label = "Номер СТС"
          outlined
           />



            <v-btn
              depressed
              color="success"
              @click="updateNumber"
            >
            Изменить номер
          </v-btn>
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
      dataOfNumber: {
        id:"",
        number:"",
        email:"",
        phone:"",
        sts:""
      },

      requiredRules:[
          value => !!value || 'Должно быть заполнено.'
      ],
            
      errorMsg:"Заполните все обязательные поля помеченные *",
      errorMsgOk: "error",
      errorMsgVisible:false,
      showForm:true
    }
  },

  computed: {
    ...mapGetters (["REST_API_PREFIX","NUMBER_LIST"])
  },

  methods:{
    updateNumber() {
      this.errorMsgVisible = false;
      if (this.$refs.addOneNumber.validate()) {         
                axios.get(this.REST_API_PREFIX + 'update_number_info',
                {
                    params: {
                        id: this.dataOfNumber.id,
                        number: this.dataOfNumber.number,
                        email: this.dataOfNumber.email,
                        phone: this.dataOfNumber.phone,
                        sts: this.dataOfNumber.sts
                    }
                })
                .then( (resp) => {
                
                    this.errorMsg = "Вы успешно изменили данные номера."
                    this.errorMsgOk = "success"
                    this.errorMsgVisible = true

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
      } else {
        this.errorMsg = "Заполните все обязательные поля";
        this.errorMsgVisible = true;
      }
    }
  },
  created: function() {


    let element = this.NUMBER_LIST.find((el) =>  el.number === this.$route.params.number )
    if (element === undefined) {
      this.errorMsg = "Данные не переданы ";
      this.errorMsgVisible = true;
      this.showForm = false;
    }

    this.dataOfNumber.id = element.id;
    console.log(element.id);
    this.dataOfNumber.number = element.number;
    this.dataOfNumber.email = element.email;
    this.dataOfNumber.phone = element.phone;
    this.dataOfNumber.sts = element.sts;

  }
}
</script>

<style>

</style>