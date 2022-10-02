<template>
    <v-container ma-0  style="max-width: 100%">
        <v-row>
            <v-col>
                <h1>Проверить номер</h1>
            </v-col>
        </v-row>
        
        <v-row>
            <v-col sm = "10" cols = "12">
                <v-text-field
                                    v-model="number"
                                    append-icon="mdi-numeric"
                                    label="Введите номер"
                                    solo
                                    hide-details
                                    
                ></v-text-field>

                

            </v-col>

            <v-col sm = "2" cols = "12" >
                <v-btn @click="getNumberInfo" class = "checkBtn" depressed color="success">
                    Проверить
                </v-btn>
            </v-col>

        </v-row>

        <v-row>
            <v-col>
                <v-progress-linear
                    v-show="loadingShow"
                    indeterminate
                    color="green"
                ></v-progress-linear>
            </v-col>
        </v-row>

        <v-row v-show = "resultShow">
            <v-col>
            <v-data-table
                :headers="headers"
                :items="numberData"
                hide-default-footer
                :item-class="itemRowBackground"
            ></v-data-table>
            </v-col>
        </v-row>

    </v-container>
</template>

<script>
import axios from 'axios';
import {mapGetters} from 'vuex'

export default {
    data(){
        return {
           number: "Х424ЕТ777",
           loadingShow:false,
           resultShow:false,
           numberData:[
               {truck_num: "111"}
            ],
           headers: [
               {text: "Номер авто", value:"truck_num"},
               {text: "Тип пропуска", value:"pass_zone"},
               {text: "Серия пропуска", value:"series"},
               {text: "Номер пропуска", value:"pass_number"},
               {text: "Дата начала", value:"valid_from"},
               {text: "Дата окончания", value:"valid_to"},
               {text: "Время", value:"type_pass"},
               {text: "Статус", value:"sys_status"},
               {text: "Осталось дней", value:"deycount"},
           ]
        }
    },
    
    computed: {
        ...mapGetters (["REST_API_PREFIX"])
    },

    methods: {

        itemRowBackground: function (item) {
            if (item.sys_status == "Начинается сегодня")
                return "start_today";
            if (item.sys_status == "Начинается завтра")
                return "start_tomorrow";
            if (item.sys_status == "Действует")
                return "deistvuet";
            if (item.sys_status == "Закончился")
                return "end";
            if (item.sys_status == "Заканчивается завтра")
                return "end_tomorrow";
            if (item.sys_status == "Заканчивается сегодня")
                return "end_today";
            if (item.sys_status == "Анулирован")
                return "anul";
        },

        getNumberInfo () {
            if (this.number == undefined) return;

            this.loadingShow = true;
            this.resultShow = false;

            axios.get(this.REST_API_PREFIX + 'number_info',
            {
                    params: {
                        // number: this.number.replace(/\s/g,''),
                        number: this.number,
                    }
            })
            
            .then( (resp) => {
                    console.log(resp)
                    this.numberData = resp.data 
                    this.loadingShow = false
                    this.resultShow = true
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
                    console.log(rezText);
                    this.loadingShow = false;
                    this.resultShow = false;
                });            
        }
    },

    created: function () {
        this.number = this.$route.params.number
        this.getNumberInfo();
    }
}
</script>

<style>
.checkBtn {
    width: 100%;
    min-height: 48px;
}

 .deistvuet {
    background-color: #cff4bd;
 }

 .start_today {
    background-color: #aff58e;
 }

 .start_tomorrow {
    background-color: #bdf6a2;
 }

.end {
    background-color: #f3c769;
}

.end_today {
    background-color: #f4d082;
}

.end_tomorrow {
    background-color: #f5d99d;
}

.anul {
    background-color: #f78964
}

</style>