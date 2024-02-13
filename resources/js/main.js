/* eslint-disable import/order */
import '@/@iconify/icons-bundle'
import App from '@/App.vue'
import layoutsPlugin from '@/plugins/layouts'
import vuetify from '@/plugins/vuetify'
import { loadFonts } from '@/plugins/webfontloader'
import router from '@/router'
import ability from '@/plugins/casl/ability'
import { abilitiesPlugin } from '@casl/vue'
import '@core-scss/template/index.scss'
import '@styles/styles.scss'
import { createPinia } from 'pinia'
import { createApp } from 'vue'
import Toast from "vue-toastification"
import { VueSignaturePad } from 'vue-signature-pad'

// Import the CSS or use your own!
import "vue-toastification/dist/index.css"

loadFonts()


// Create vue app
const app = createApp(App)


// Use plugins
app.use(vuetify)
app.use(createPinia())
app.use(router)
app.use(layoutsPlugin)
app.use(abilitiesPlugin, ability, {
  useGlobalProperties: true,
})
app.use(Toast, {})
app.component("VueSignaturePad", VueSignaturePad)

// Mount vue app
app.mount('#app')
