<template>
  <v-app>
    <v-main>
      <v-app-bar v-show="SHOW_PANEL"> 
        <v-app-bar-nav-icon @click="showMenu = !showMenu"></v-app-bar-nav-icon>
        <v-toolbar-title>Портал по проверке пропусков</v-toolbar-title>
        
        <v-spacer></v-spacer>
        
        <v-btn icon @click.prevent="appExit">
          <v-icon>mdi-exit-to-app</v-icon>
        </v-btn>
      </v-app-bar>

      <v-navigation-drawer
        v-model="showMenu"
        absolute
        temporary
      >
        <v-list
          nav
          dense
        >
          <v-list-item-group
            @change = "showMenu = false"
            v-model="selectedItem"
            active-class="deep-purple--text text--accent-4"
          >
            <v-list-item v-for="(item, i) in itemsMeny" :key="i" :to = "item.to">
              <v-list-item-icon>
                <v-icon>{{item.icon}}</v-icon>
              </v-list-item-icon>
              <v-list-item-title>{{item.text}}</v-list-item-title>
            </v-list-item>

          </v-list-item-group>
        </v-list>
      </v-navigation-drawer>

      <router-view/>
    </v-main>
  </v-app>
</template>

<script>
import {mapGetters} from 'vuex'
import allLibs from './lib/libs'

export default {
  name: 'App',

  computed: {
    ...mapGetters (["REST_API_PREFIX", "SHOW_PANEL"])
  },

  data: () => ({
    showMenu:false,
    selectedItem:0,
    itemsMeny: [
        {text: 'Главная', icon: 'mdi-home', to: "/"},
        {text: 'Добавить номер', icon: 'mdi-card-plus-outline', to: {name:'addone'}},
        {text: 'Добавить номера', icon: 'mdi-cards-outline', to: {name:'addfull'}},
        {text: 'Проверить номер', icon: 'mdi-checkbox-multiple-marked-outline', to: "/check_number/"},
        {text: 'Экстренное оповещение', icon: 'mdi-alert-outline', to: {name:'massAlert'}},
    ],
  }),

  methods: {
    appExit() {
      allLibs.reloginUser();
    }
  }
};
</script>
