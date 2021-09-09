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
                    :items="desserts"
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
export default {
    data() {
        return {
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
                {text:'Госномер', value: 'gosnumber'},
                {text:'e-mail', value: 'mail'},
                {text:'Время', value: 'austime'},
                {text:'Статус', value: 'status'},
                {text:'Системный статус', value: 'sysstatus'},
                {text:'Проверен', value: 'lastchec'},
                {text:'Дата начала', value: 'startdata'},
                {text:'Дата окончания', value: 'okdata'},
                {text:'Анулирован', value: 'onul'},
                {text:'Серия', value: 'seria'},
                {text:'Тип', value: 'type'},
                {text:'Осталось дней', value: 'deycount'},
            ]
        }
    }
}
</script>

<style>

</style>