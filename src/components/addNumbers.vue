<template>
  <v-container ma-0  style="max-width: 100%">
    <v-row>
        <v-col>
          <h1>Добавить номеров из файла</h1>
        </v-col>
    </v-row>

        <v-form ref = "multiaddForm">
    <v-row>
      <v-col>

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

            <!-- <v-checkbox
              v-model="checAfterAdd"
              label="Проверить после добавления"
              color="success"
              value="chec"
              hide-details
              class = "mb-4"
            ></v-checkbox> -->
      
      </v-col>
    </v-row>
    
    <v-row>
      <v-col>
        <strong>Прочитано из файла</strong> {{this.readetCSV.length}}<br/> 
        <strong>Добавлено в базу</strong> {{this.totalCount}}<br/> 
        <strong>Проверено пропусков</strong> {{this.calcCount}}<br/> 
      </v-col>
    </v-row>

    <v-row>
      <v-col>
        <v-btn
              depressed
              color="success"
              @click="readCSV"
            >
            Загрузить номера из файла
        </v-btn>
      </v-col>
      
      <v-col>
          <v-btn
              depressed
              color="success"
              @click="checElement()"
            >
          Проверить элементы
          </v-btn>
      </v-col>
    </v-row>
  </v-form>

    <v-row>
      <v-col>
        <v-progress-linear
          v-show="loadingShow"
          :indeterminate = "infineProghes"
          :value="procent"
          color="green"
        ></v-progress-linear>
      </v-col>
    </v-row>
    
    <v-row>

      <v-col>
        <div class="loadetNumberList">
          <div v-for="(item, i) in resultAdding" :key="i" class="element">
            <span>{{item.number}}</span>
            <span>{{item.type}} {{item.seria}}</span>
            <span >{{item.status}}</span>
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
      totalCount:0,
      calcCount:0,
      procent:0,

      exit:false,

      filename:null,
      loadingShow:false,
      infineProghes:false,
      
      resultAdding:[],
      readetCSV:[]
    }
},

 computed: {
    ...mapGetters (["REST_API_PREFIX"])
  },


  methods:{
    sleep(ms) {
      return new Promise(resolve => setTimeout(resolve, ms));
    },

    readCSV() {
      if (this.filename !== null) {
        this.readetCSV = [];
        this.$papa.parse(this.filename, {
          delimiter: ";",
          newline: "\r\n",
          encoding: "WINDOWS-1251",
          complete: (results) => {
            this.readetCSV = results.data.replace(/\s/g,'')
            this.addNumbersToBase()
          }
        });
      }

    },

    addNumbersToBase() {
      this.loadingShow = true
      this.infineProghes = true

      axios.post(this.REST_API_PREFIX + 'add_one_numbers', 
              {
                  "element": this.readetCSV,
                  "chec": this.checAfterAdd
              })
              .then(response => {
                  console.log("Success!")
                  console.log({ response })
                 
                  this.totalCount = response.data.count
                  this.loadingShow = false
                  this.infineProghes = false
              })
              .catch(error => {
                  
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
                      console.log(rezText);
                  
                  this.loadingShow = false
                  this.infineProghes = false
                  
              });

    },

    checElement() {
      this.calcCount = 0;

      this.loadingShow = true
      this.infineProghes = false

       let interval = setInterval(() => {
         console.log("DO!");
         console.log(this.readetCSV[this.calcCount][1]);
        
        
        
        axios.get(this.REST_API_PREFIX + 'ch_number_after_add', 
              
              {
                    params: {
                        number: this.readetCSV[this.calcCount][1]
                    }
                }
              )
              .then(response => {
                  console.log("Success!")
                  console.log({ response })

                  this.calcCount++
                  this.procent = this.calcCount / (this.readetCSV.length / 100)
                  
                  this.resultAdding.push({number: response.data.nb, seria:response.data.result.seria, type:response.data.result.pass_number, status:response.data.result.sys_status } )
              })
              .catch(error => {
                  
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
                  console.log(rezText);
                  
                  
              });

         
         this.calcCount++;
         if (this.calcCount == this.readetCSV.length-1)
          clearTimeout(interval);
       } , 1000);
    },

    addNumbers() {
      if (this.$refs.multiaddForm.validate()) {
        let fd =  new FormData();



        fd.append('numbersfile', this.filename)
        fd.append('chec', this.checAfterAdd)
        // fd.append('chec', (this.checAfterAdd == true)?"1":"0")
       
       console.log(fd);


        this.loadingShow = true;
        this.resultAdding = [];
        
      this.$papa.parse(this.filename, {
        delimiter: ";",
        newline: "\r\n",
        encoding: "WINDOWS-1251",
        complete: (results) => {
      
          this.calcCount = 0;
          this.totalCount = results.data.length;

          for (let i = 0; i<results.data.length; i++) {
              let element = results.data[i];
              if (this.exit) return false;

              if (element.length !== 1)
              
              axios.post(this.REST_API_PREFIX + 'add_one_numbers', 
              {
                  "element": element,
                  "chec": this.checAfterAdd
              })
              .then(response => {
                  console.log("Success!")
                  console.log({ response })
                  console.log(element)

                  this.calcCount++
                  this.procent = this.calcCount / (this.totalCount / 100)
                  console.log(this.procent)
                  this.resultAdding.push(response.data)
                  
              })
              .catch(error => {
                  
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
                  console.log(element)
                  this.resultAdding.push({number: element[1], email: element[0]})

                      console.log(error.config);
                      console.log(rezText);
                  
              });

          

          
          }
      
        }
      });
        
         // axios.post(this.REST_API_PREFIX + 'add_one_numbers', fd , {
        
          //        headers: {
          //          'Content-Type': 'multipart/form-data'
          //        }
          //    })
          //   .then(response => {
          //       console.log("Success!")
          //       console.log({ response })
          //       this.resultAdding = response.data
          //       this.loadingShow = false
          //   })
          //   .catch(error => {
          //       let rezText = "";
          //           if (error.response)
          //           {
          //               rezText = error.response.data.message;
          //           } else 
          //           if (error.request) {
          //               rezText = error.message;
          //           } else {
          //               rezText = error.message;
          //           }
                    
          //           console.log(error.config);
          //           console.log(rezText);
          //       this.loadingShow = false;
          //   });
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
