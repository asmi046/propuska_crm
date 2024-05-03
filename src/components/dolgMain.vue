<template>
    <v-container ma-0  style="max-width: 100%">
        <v-row>
            <v-col>
                <h1>Должники</h1>
            </v-col>
        </v-row>
        
        <v-form ref = "searchNumber">
            <v-row>
                <v-col>
                    <v-text-field
                    placeholder="Введите запрос"
                    v-model="searchStr"
                    label = "Введите запрос номер авто, e-mail"
                    outlined
                    append-icon="mdi-magnify"
                    />
                </v-col>
            </v-row>
        </v-form>


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
                <v-col>
                    <v-data-table 
                    locale="ru-RU"
                    :headers="[
                        {text:'Имя', value: 'name'},
                        {text:'e-mail', value: 'email'},
                        {text:'Госномер', value: 'number'},
                        {text:'Дата добавления', value: 'adding_data'},
                        {text:'Просрочено дней', value: 'deys'},
                        {text:'Управление', value: 'action'},
                    ]"
                    :items="allDolgList"
                    :search="searchStr"
                    :itemsPerPage = "20"
                    :footer-props="{
                        itemsPerPageText:'Записей на странице',
                        pageText: '{0}-{1} из {2}',
                        itemsPerPageOptions: [20, 60, 100, 300, -1]
                    }"
                    >
                    
                    <template v-slot:[`item.action`]="{ item }">
                        <v-btn height = "28" class = "ma-1 pl-2 pr-2 action-button" @click="deleteDolgNumber(item)" color="error" depressed>
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
            searchStr:"",
            allDolgList:[],
            requiredRules:[
                value => !!value || 'Должно быть заполнено.'
            ], 

            errorMsg:"Заполните все обязательные поля помеченные *",
            errorMsgOk: "error",
            errorMsgVisible:false,

            deleteDialogParam: {
                showDialog:false,
                number:"",
                recordId:0,
                closeDialog: () => {
                    this.deleteDialogParam.showDialog = false;
                },

                deleteNumber: () => {
                        axios.get(this.REST_API_PREFIX + 'dell_dolgnik',
                        {
                            params: {
                                id: this.deleteDialogParam.recordId,
                                mail: allLibs.getCookie("servautorise"),
                                token: allLibs.getCookie("servtoken"),
                            }
                        })
                        .then( (resp) => {
                            console.log(resp);
                            this.getAllDolgList();
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
        }

        
    },

    computed: {
      ...mapGetters (["REST_API_PREFIX"])
    },

    mounted: function() { 
        this.getAllDolgList();
    },

    methods:{ 
        deleteDolgNumber(item){
            this.deleteDialogParam.recordId = item.id;
            this.deleteDialogParam.number = item.number;
            this.deleteDialogParam.showDialog = true;
        },

        getAllDolgList() {
            this.errorMsgVisible = false;

             axios.get(this.REST_API_PREFIX + 'get_all_dolgnik',
                {
                    params: {
                        search: this.searchStr
                    }
                })
                .then( (resp) => {

                    this.allDolgList = resp.data
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
        }
    }
}
</script>

<style>

</style>