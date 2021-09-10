<template>
  <v-container ma-0  style="max-width: 100%">
    <v-row>
        <v-col>
          <h1>Добавить номер</h1>
        </v-col>
    </v-row>
    <v-form>
      <v-row>
        <v-col md = "6" xl = "6" cols="12">
          <v-text-field
          placeholder="X###XX###"
          v-model="dataOfNumber.number"
          v-mask = "'X###XX##N'"
          label = "Номер авто"
          outlined
           />

          <v-text-field
          v-model="dataOfNumber.email"
          label = "e-mail"
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

            <v-btn
              depressed
              color="success"
            >
            Добавить номер
          </v-btn>
        </v-col>
      </v-row>
    </v-form>
  </v-container>
</template>

<script>
export default {
  data() {
    return {
      dataOfNumber: {
        name:"",
        email:"",
        phone:"",
        sts:""
      }
    }
  }
}
</script>

<style>

</style>