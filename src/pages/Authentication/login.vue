<template>
  <q-layout view="lHh lpr lFf" class="shadow-2 rounded-borders">
    <q-header bordered class="bg-white text-primary">
      <q-toolbar>
        <router-link to="/">
          <div class="q-py-sm q-mx-lg">
            <q-img :src="require('../../assets/images/logo.png')" style="width: 50px;" />
          </div>
        </router-link>
        <q-toolbar-title> </q-toolbar-title>
        <q-btn label="Login" color="green-8" flat :to="{name: 'Login'}"/>
        <q-btn label="Signup" color="green-8" flat :to="{name: 'Signup'}"/>
      </q-toolbar>
    </q-header>

    <div id="bgImage" style="display: flex">
      <div class="q-pa-md text-center" style="margin: auto">
        <q-form
          @submit="login"
          class="text-center q-pa-md shadow-3 bg-white q-mx-auto"
          style="max-width: 400px; border-radius: 10px;"
        >
          <div class="q-mt-lg text-center" style="font-size: 28px">Login to Seventh Root</div>
          <div class="row justify-between q-col-gutter-md" >
            <div class="col-12">
              <q-input outlined color="teal-10" v-model="userData.email" clearable label="Email Address" class="q-ml-none">
                <template v-slot:prepend>
                  <q-icon name="account_circle" />
                </template>
              </q-input>
            </div>
            <div class="col-12">
              <!--<q-input-->
                <!--outlined-->
                <!--color="teal-10"-->
                <!--label="Password"-->
                <!--v-model="userData.password"-->
                <!--type="password"-->
                <!--clearable-->
                <!--hide-show-password-->
              <!--&gt;-->
                <!--<template v-slot:prepend>-->
                  <!--<q-icon name="lock" />-->
                <!--</template>-->
              <!--</q-input>-->
              <base-text-field
                outlined
                color="teal-10"
                v-model="userData.password"
                normalize-bottom
                label="Password"
                icon="mdi-card"
                clearable
                class="q-ml-none"
                type="password"
                hide-show-password
              >
                <template v-slot:prepend>
                  <q-icon name="mdi-account-key" />
                </template>
              </base-text-field>
            </div>
          </div>
          <q-item class="q-mx-none q-px-none">
            <q-item-section>
              <q-checkbox v-model="remember" label="Remember me" color="teal" />
            </q-item-section>
            <q-item-section>
              <router-link to="/">
                Forgot your password?
              </router-link>
            </q-item-section>
          </q-item>
          <q-btn type="submit" class="full-width bg-cyan-7 text-white">
            Sign In
          </q-btn>
          <div class="row">
            <div class="col-5">
              <q-linear-progress :value="0" size="1px" class="q-mt-sm" />
            </div>
            <div class="col-2 text-center">
              <span>or</span>
            </div>
            <div class="col-5">
              <q-linear-progress :value="0" size="1px" class="q-mt-sm" />
            </div>
          </div>
          <q-btn class="full-width bg-orange-6 text-white" :to="{name: 'Signup'}">
            Join Seventh Root
          </q-btn>
        </q-form>
      </div>
    </div>
  </q-layout>
</template>

<style>
  #bgImage {
    background: url(../../assets/images/dark-material-bg.jpg) 50% 50% / cover no-repeat;
    overflow: auto;
    padding: 2.1rem 0 2.8rem;
    min-height: 100vh;
  }
</style>
<script>
import { Loading, LocalStorage } from 'quasar'
import { api } from 'src/boot/api'
export default {
  name: 'Login',
  data () {
    return {
      userData: {
        email: '',
        password: ''
      },
      user: {},
      accept: false,
      remember: false
    }
  },
  created () {
    // console.log('current Route', this.$router.currentRoute)
  },
  methods: {
    async login () {
      Loading.show()
      try {
        let res = await api.login(this.userData)
        console.log('res', res.data)
        Loading.hide()
        this.$q.notify({
          color: 'positive',
          textColor: 'white',
          position: 'top',
          message: 'User is logged in successfully'
        })
        LocalStorage.set('token', res.data.token)
        LocalStorage.set('user', res.data.user)
        this.$store.commit('auth/token', res.data.token)
        this.$store.commit('auth/user', res.data.user)
        this.$router.push('/dashboard')
      } catch (error) {
        console.log('error', error)
        Loading.hide()
      }
    }
  }
}
</script>
