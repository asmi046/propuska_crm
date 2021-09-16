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
          <p>
            Загрузка большого колличества номеров из svg файла. Выберите файл и нажмите кнопку - Добавить номера
          </p>
          <v-file-input
            label="Выберите файл"
            outlined
            dense
            ref="mFiles"
            v-model="filename"
            accept=".csv"
          ></v-file-input>

            <v-checkbox
              v-model="checAfterAdd"
              label="Проверить после добавления"
              color="success"
              value="chec"
              hide-details
              class = "mb-4"
            ></v-checkbox>

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
    
    <v-row>
      <v-col>
        <v-progress-linear
          v-show="loadingShow"
          indeterminate
          color="green"
        ></v-progress-linear>

        <div class="loadetNumberList">
          <div v-for="(item, i) in resultAdding" :key="i" class="element">
            <span>{{item.number}}</span>
            <span>{{item.email}}</span>
            <span :class = "(item.result == false)?'clRed':'clGreen'">{{(item.result == false)?"Уже есть в базе":"Добавлен" }}</span>
          </div>
        </div>

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
      filename:null,
      loadingShow:false,
      checAfterAdd:true,
      resultAdding:[]
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
         fd.append('chec', this.checAfterAdd)
       
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

        this.loadingShow = true;
        this.resultAdding = [];
        axios.post(this.REST_API_PREFIX + 'add_one_numbers', fd, {
                headers: {
                  'Content-Type': 'multipart/form-data'
                }
            })
            .then(response => {
                console.log("Success!")
                console.log({ response })
                this.resultAdding = response.data
                this.loadingShow = false
            })
            .catch(error => {
                console.log({ error });
                this.loadingShow = false;
            });
      }
    }
  }
}
</script>

<style>
.loadetNumberList {
  display: flex;
  width:100%;
  flex-direction: column;
}

.loadetNumberList .element {
  display: flex;
  width: 100%;
  justify-content: space-between;
}

.loadetNumberList .element .clRed{
  color:red;
}

.loadetNumberList .element .clGreen{
  color:green;
}
</style>
