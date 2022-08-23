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
                    placeholder="X###XX###"
                    v-model="autoNumber"
                    v-mask = "'X###XX##N'"
                    label = "Номер авто*"
                    outlined
                    :rules="requiredRules"
                    />
                </v-col>
            </v-row>
        </v-form>
    </v-container>
</template>

<script>
export default {
    data() {
        return {
            autoNumber:"",
            requiredRules:[
                value => !!value || 'Должно быть заполнено.'
            ], 
        }
    }
}
</script>

<style>

</style>