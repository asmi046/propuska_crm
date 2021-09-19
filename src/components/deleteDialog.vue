<template>
      <v-dialog
      v-model="deleteDialogParam.showDialog"
      max-width="290"
    >
      <v-card>
        <v-card-title class="text-h5">
          Удаление номера <br/>{{deleteDialogParam.number}}
        </v-card-title>

        <v-card-text>
          Вы действительно хотите удалить номер {{deleteDialogParam.number}}
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>

          <v-btn
            color="green darken-1"
            text
            @click="deleteDialogParam.closeDialog()"
          >
            Отмена
          </v-btn>

          <v-btn
            color="red darken-1"
            text
            @click="deleteDialogParam.closeDialog()"
          >
            Удалить
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
</template>

<script>
export default {
     props: ['deleteDialogParam'],
}
</script>

<style>

</style>