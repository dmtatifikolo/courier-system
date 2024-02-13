import { createVuetify } from 'vuetify'
import { VBtn } from 'vuetify/components/VBtn'
import defaults from './defaults'
import { icons } from './icons'
import theme from './theme'
import * as components from 'vuetify/components'
import * as labsComponents from 'vuetify/labs/components'

// Styles
import '@core-scss/template/libs/vuetify/index.scss'
import 'vuetify/styles'

export default createVuetify({
  components: {
    ...components,
    ...labsComponents,
  },
  aliases: {
    IconBtn: VBtn,
  },
  defaults,
  icons,
  theme,
})
