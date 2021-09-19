<template>
   
        <v-container  ma-0  style="max-width: 100%" >
            <v-row v-show="showLoadData">
                <v-col>
                    <h3>Загружаем данные...</h3>
                </v-col>
            </v-row>
            <v-form d-flex style="width: 100%">
                <v-row>
                    
                            <v-col sm = "3" cols = "12" >
                                <v-select
                                :items="STATUSES"
                                label="Выберите фильтр"
                                solo
                                :item-text="item => item.text +'  ('+ item.count+')'"
                                item-value="text"
                                @change="updateFilter"
                                v-model="status"
                                ></v-select>
                            </v-col>
                            
                            <v-col sm = "3" cols = "12" >
                                <v-select
                                :items="typeSelectItems"
                                label="Выберите тип пропуска"
                                solo
                                @change="updateFilter"
                                v-model="type"
                                ></v-select>
                            </v-col>

                            <v-col sm = "4" cols = "12" >
                                <v-text-field
                                    v-model="search"
                                    append-icon="mdi-magnify"
                                    label="Поиск"
                                    solo
                                    hide-details
                                    @keydown="updateFilter"
                                ></v-text-field>
                            </v-col>

                            <v-col sm = "2" cols = "12" >
                                <v-btn width = "100%" height="48" @click="clearFilter()" depressed>
                                    <v-icon class = "mr-2">mdi-close</v-icon>
                                    Сбросить                       
                                </v-btn>
                            </v-col>
                        
                </v-row>
            </v-form>

            <v-row >
                <v-col>
                    <v-data-table 
                    locale="ru-RU"
                    :headers="headers"
                    :items="this.NUMBER_LIST"
                    :search="search"
                    :footer-props="{
                        itemsPerPageText:'Записей на странице',
                        pageText: '{0}-{1} из {2}'
                    }"
                    >
                    
                    <template v-slot:item.action="{ item }">
                        <v-btn @click="$router.push({ name: 'checknumber', params: {number: item.number} })" height = "28" class = "ma-1 pl-2 pr-2 action-button"  color="info" depressed>
                            <v-icon class = "mr-2">mdi-information-outline</v-icon>
                            Подробнее                       
                        </v-btn>
                        
                        <v-btn height = "28" class = "ma-1 pl-2 pr-2 action-button" @click="updateNumber(item)" color="success" depressed>
                            <v-icon class = "mr-2">mdi-update</v-icon>
                            Обновить                       
                        </v-btn>
                        
                        <v-btn @click="$router.push({ name: 'updatenumber', params: {number: item.number} })" height = "28" class = "ma-1  pl-2 pr-2 action-button"  color="success" depressed>
                            <v-icon class = "mr-2">mdi-file-edit-outline</v-icon>
                            Изменить                       
                        </v-btn>
                        
                        <v-btn height = "28" class = "ma-1 pl-2 pr-2 action-button" @click="deleteNumber(item)" color="error" depressed>
                            <v-icon class = "mr-2">mdi-delete</v-icon>
                            Удалить                       
                        </v-btn>
                    
                    </template>
                    
                    </v-data-table>
                </v-col>
            </v-row>
            
            <delete-dialog :delete-dialog-param = "deleteDialogParam"></delete-dialog>
        </v-container>
</template>

<script>
import axios from 'axios';
import {mapGetters} from 'vuex'
import deleteDialog from './deleteDialog.vue';
import allLibs from '../lib/libs';
export default {
    components: { deleteDialog },
    data() {
        return {
  
        deleteDialogParam: {
            showDialog:false,
            number:"",
            closeDialog: () => {
                this.deleteDialogParam.showDialog = false;
            },

            deleteNumber: () => {
               
                axios.get(this.REST_API_PREFIX + 'dell_number',
                {
                    params: {
                        number: this.deleteDialogParam.number,
                        mail: allLibs.getCookie("userlogin"),
                        token: allLibs.getCookie("servtoken"),
                    }
                })
                .then( (resp) => {
                    console.log(resp);
                    this.$store.dispatch('updateNumberList',  {status: this.status, type: this.type, search: this.search});
                    this.deleteDialogParam.showDialog = false;
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
                    this.deleteDialogParam.showDialog = false;
                });
            }
        },     
            showLoadData:false,
            search:'', 
            status:'', 
            type:'', 
            filterSelectItems:[
                'Все (630)',
                'Действует (300)',
                'Не действует (230)',
                'Действует более 14 дней (30)'
            ],
            typeSelectItems:[
                'БА',
                'ББ',
            ],

            headers: [
                {text:'Госномер', value: 'number'},
                {text:'e-mail', value: 'email'},
                {text:'Время', value: 'time'},
                {text:'Статус', value: 'status'},
                {text:'Системный статус', value: 'sys_status'},
                {text:'Проверен', value: 'chec_time'},
                {text:'Дата начала', value: 'start_data'},
                {text:'Дата окончания', value: 'end_data'},
                {text:'Анулирован', value: 'anul_data'},
                {text:'Серия', value: 'seria'},
                {text:'Тип', value: 'type'},
                {text:'Номер', value: 'pass_number'},
                {text:'Осталось дней', value: 'dey_count'},
                {text:'Действие', value: 'action'},
            ]
        }
    },

    computed: {
        ...mapGetters (["REST_API_PREFIX", "NUMBER_LIST", "STATUSES"])
    },

    mounted: function() {
        this.$store.dispatch('updateNumberList',  {status: this.status, type: this.type, search: this.search});
    },

    methods:{
        clearFilter() {
            this.status = ""; 
            this.type = ""; 
            this.search = "";
            this.$store.dispatch('updateNumberList',  {status: this.status, type: this.type, search: this.search});
        },
        updateFilter() {
            this.$store.dispatch('updateNumberList', {status: this.status, type: this.type, search: this.search});
        },

        numberInfo() {

        },
        updateNumber(item) {
            axios.get(this.REST_API_PREFIX + 'update_number',
                {
                    params: {
                        number: item.number,
                    }
                })
                .then( (resp) => {
                    console.log(resp);
                    this.$store.dispatch('updateNumberList',  {status: this.status, type: this.type, search: this.search});
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
                });
        },
        chengeNumber() {

        },
        deleteNumber(item) {
            this.deleteDialogParam.number = item.number;
            this.deleteDialogParam.showDialog = true;

        }
    }

    

}
</script>

<style>

 .v-data-table-header {
     background-color: gray;
     color: white;
 }


.v-data-table-header th{
    vertical-align: top;
    padding-top: 3px!important;
}

 .v-data-table-header span{ 
     color: white;
     font-weight: normal;
 }

 @media (max-width: 640px) {
    .v-data-table-header {
     background-color: white;
    }
 }
</style>