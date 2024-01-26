<template>
  <v-container ma-0  style="max-width: 100%">
    <v-row>
        <v-col>
          <h1>Добавить номер</h1>
        </v-col>
    </v-row>
    <v-form ref = "addOneNumber">
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
          v-model="dataOfNumber.dopemail"
          label = "e-mail (Дополнительный)*"
          outlined
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

          <v-alert
            border="right"
            colored-border
            v-bind:type = errorMsgOk
            elevation="2"
            v-show="errorMsgVisible"
            >{{errorMsg}}</v-alert>

            <v-btn
              depressed
              color="success"
              @click="addNumber"
            >
            Добавить номер
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
        number:"",
        email:"",
        dopemail:"",
        phone:"",
        sts:""
      },

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
    addNumber() {
      this.errorMsgVisible = false;
      if (this.$refs.addOneNumber.validate()) {         
                axios.get(this.REST_API_PREFIX + 'add_one_number',
                {
                    params: {
                        number: this.dataOfNumber.number,
                        mail: this.dataOfNumber.email,
                        dopemail: this.dataOfNumber.dopemail,
                        phone: this.dataOfNumber.phone,
                        sts: this.dataOfNumber.sts
                    }
                })
                .then( (resp) => {
                    this.$refs.addOneNumber.reset()
                    this.errorMsg = "Вы успешно добавили номер."
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
  }
}
</script>

<style>

</style>