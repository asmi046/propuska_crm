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
                                :items="filterSelectItems"
                                label="Выберите фильтр"
                                solo
                                ></v-select>
                            </v-col>
                            
                            <v-col sm = "3" cols = "12" >
                                <v-select
                                :items="typeSelectItems"
                                label="Выберите тип пропуска"
                                solo
                                ></v-select>
                            </v-col>

                            <v-col sm = "6" cols = "12" >
                                <v-text-field
                                    v-model="search"
                                    append-icon="mdi-magnify"
                                    label="Поиск"
                                    solo
                                    hide-details
                                ></v-text-field>
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
                    ></v-data-table>
                </v-col>
            </v-row>
            
        </v-container>
</template>

<script>
import {mapGetters} from 'vuex'
export default {
    data() {
        return {
            pageNumber:1,
            pageNumeratorLength:15,
            countInPage:50,
            showLoadData:false,
            search:'', 
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
            ]
        }
    },

    computed: {
        ...mapGetters (["REST_API_PREFIX", "NUMBER_LIST"])
    },

    mounted: function() {
        this.$store.dispatch('updateNumberList');
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