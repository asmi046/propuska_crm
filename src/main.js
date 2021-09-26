import Vue from 'vue'
import App from './App.vue'

import axios from 'axios'
import VueAxios from 'vue-axios'

import store from './store/store'
import router from './router/router'

import vuetify from './plugins/vuetify'

import VuePapaParse from 'vue-papa-parse'
Vue.use(VuePapaParse)

import { VueMaskDirective } from 'v-mask'
Vue.directive('mask', VueMaskDirective);

Vue.use(VueAxios, axios)

Vue.config.productionTip = false

new Vue({
  store,
  router,
  vuetify,
  render: h => h(App)
}).$mount('#app')
