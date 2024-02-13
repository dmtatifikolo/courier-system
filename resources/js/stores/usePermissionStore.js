import { defineStore } from 'pinia'
import axios from '@axios'

export const usePermissionStore = defineStore('PermissionStore', {
  actions: {
    getPermissions(params = { search: undefined, pagination: undefined }) { 
      if(params.search != null && params.pagination != null){
        return axios.get('/api/access/get-all-permissions?search=' +
          params.search +
          '&page=' +
          params.pagination.currentPage)
      }
      else if(params.search != null ){
        return axios.get('/api/access/get-all-permissions?search=' +
          params.search)
      }
      else if(params.pagination != null){
        return axios.get('/api/access/get-all-permissions?page=' +
          params.pagination.currentPage)
      }
      else {
        return axios.get('/api/access/get-all-permissions' )
      }
    },

    async addPermission(permission) {
      return await new Promise((resolve, reject) => {
        axios.post('/api/access/create-permission', permission).then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    

    async updatePermission(permission) {
      return await new Promise((resolve, reject) => {
        axios.put('/api/access/update-permission', permission).then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    async getPermission(id) {
      return await new Promise((resolve, reject) => {
        axios.get('/api/access/get-permission/' + id).then(response => resolve(response)).catch(error => reject(error))
      })
    },

    async deletePermission(id) {
      return await new Promise((resolve, reject) => {
        axios.delete('/api/access/delete-permission/' + id).then(response => resolve(response)).catch(error => reject(error))
      })
    },
  },
})
