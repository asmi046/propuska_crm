<template>
  <div id="autorise">
    <div class = "window">
        <div class = "logoline">
            <img src="../assets/logo.svg" alt="RubEx Trend" class="logo"> 
            <h1>Система контроля пропусков</h1>
        </div>
        <Login v-show="auteriseSatte == 'autorise'"></Login>
        <Register v-show="auteriseSatte == 'register'" ></Register>
        <PassRec v-show="auteriseSatte == 'passrec'"></PassRec>
      
        <span class = "snoska"> *Данная система предназначена для внутрикорпоративного использования. Все процессы обрабоки персональных данных регламентируются трудовыми договорами с сотрудниками.</span>
    </div>
  </div>
</template>

<script>
import Login from './Login.vue'
import PassRec from './PassRec.vue';
import Register from './Register.vue';



export default {
  name: 'Autorise',
  
  computed: {
    auteriseSatte() {
      console.log(this.$store.getters.getLoginState);
      return this.$store.getters.getLoginState;
    }

  },

  components: {
    Login,
    Register,
    PassRec
  },

  mounted: function() {
    if (this.$store.getters.AUTORISE)
      this.$router.push({ name: 'service' });

    // let autorise = allLibs.getCookie("servautorise"); 
    // if (autorise != undefined) 
    // {
    //     this.$store.dispatch('chengeAutorise',  true);
    //     this.$router.push({ name: 'service' });
    // }
    //   else this.$store.dispatch('chengeAutorise',  false);
  }
}
</script>

<style>

#autorise {
  width: 100%;
  min-height: 100vh;
  display: flex;
  background-color: #333;
  padding: 10px;
}

#autorise .window {
  width: 90%;
  max-width: 500px;
  min-height: 350px;
  background-color: white;
  margin: auto;
  border-radius: 10px;
  box-shadow: 0 0 10px rgba(0,0,0,0.5);
  display: flex;
  overflow: hidden;
  padding: 0 2%;
  flex-direction: column;
}

.windowForm,
#loginForm {
  margin-bottom: 20px;
}

.snoska {
  font-size: 12px;
  color: #6d6e72;
  display: inline-block;
  margin-bottom: 20px;
}

.logoline img{
  height: 100%;
  width: auto;
}

.logoline {
  margin: 30px 0;
  height: 45px;
  width: 100%;
  display: flex;
}

.logoline h1{
  font-size: 20px;
  margin: auto auto auto 20px;
  line-height: 1;
  text-transform: uppercase;
}

@media screen and (max-width:568px) { 
  #autorise .window {
        padding: 0 5%;
  }
 
}

</style>
