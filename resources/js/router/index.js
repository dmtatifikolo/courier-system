import { setupLayouts } from 'virtual:generated-layouts'
import { createRouter, createWebHistory } from 'vue-router'
import routes from '~pages'
import { isUserLoggedIn } from './utils'
import { canNavigate } from '@layouts/plugins/casl'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: to => {
        return { name: 'index' }
      },
    },
   
    // {
    //   path: '/pages/user-profile',
    //   redirect: () => ({ name: 'pages-user-profile-tab', params: { tab: 'profile' } }),
    // },
    // {
    //   path: '/pages/account-settings',
    //   redirect: () => ({ name: 'pages-account-settings-tab', params: { tab: 'account' } }),
    // },
    ...setupLayouts(routes),
  ],
})


router.beforeEach(to => {
  const isLoggedIn = isUserLoggedIn()

  if (canNavigate(to)) {
    if (to.meta.redirectIfLoggedIn && isLoggedIn)
      return '/'
  }
  else {
    if (isLoggedIn)
      return { name: 'not-authorized' }
    else
      return { name: 'login', query: { to: to.name !== 'index' ? to.fullPath : undefined } }
  }
})

export default router
