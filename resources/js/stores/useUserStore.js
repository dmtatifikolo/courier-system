import { defineStore } from 'pinia'
import axios from '@axios'

export const useUserStore = defineStore('UserStore', {
  actions: {
    getUsers(params = { search: undefined, pagination: undefined }) { 
      if(params.search != null && params.pagination != null){
        return axios.get('/api/user?search=' +
          params.search +
          '&page=' +
          params.pagination.currentPage)
      }
      else if(params.search != null ){
        return axios.get('/api/user?search=' +
          params.search)
      }
      else if(params.pagination != null){
        return axios.get('/api/user?page=' +
          params.pagination.currentPage)
      }
      else {
        return axios.get('/api/user' )
      }
    },

    addUser(user) {
      return new Promise((resolve, reject) => {
        axios.post('/api/user', user).then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    async updateUser(user) {
      return await new Promise((resolve, reject) => {
        axios.put('/api/user/'+user.id, user).then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    getUser(id) {
      return new Promise((resolve, reject) => {
        axios.get('/api/user/' + id).then(response => resolve(response)).catch(error => reject(error))
      })
    },

    deleteUser(id) {
      return new Promise((resolve, reject) => {
        axios.delete('/api/user/' + id).then(response => resolve(response)).catch(error => reject(error))
      })
    },
  },
})
