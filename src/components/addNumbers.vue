<template>
  <v-container ma-0  style="max-width: 100%">
    <v-row>
        <v-col>
          <h1>Добавить номеров из файла</h1>
        </v-col>
    </v-row>

    <v-row>
      <v-col>
        <v-form ref = "multiaddForm">
          <v-file-input
            label="Выберите файл"
            outlined
            dense
            ref="mFiles"
            v-model="filename"
          ></v-file-input>

          <v-btn
              depressed
              color="success"
              @click="addNumbers"
            >
            Добавить номера
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
      filename:null
    }
},

 computed: {
    ...mapGetters (["REST_API_PREFIX"])
  },


  methods:{
    addNumbers() {
      if (this.$refs.multiaddForm.validate()) {
        let fd =  new FormData();
         fd.append('numbersfile', this.filename)
       
        // var xhr = new XMLHttpRequest();
        // xhr.open('POST', this.REST_API_PREFIX + 'add_one_numbers', true);
        // xhr.setRequestHeader("Content-Type","multipart/form-data")
        // xhr.onload = function(e) {
        //   console.log(e);
        // };
        
        // xhr.onerror = function(e) {
        //   console.log(e);
        // };

        // xhr.send(fd);

        axios.post(this.REST_API_PREFIX + 'add_one_numbers', fd, {
                headers: {
                  'Content-Type': 'multipart/form-data'
                }
            })
            .then(response => {
                console.log("Success!");
                console.log({ response });
            })
            .catch(error => {
                console.log({ error });
            });
      }
    }
  }
}
</script>

<style>

</style>