import { defineStore } from 'pinia'
import axios from '@axios'

export const useRoleStore = defineStore('RoleStore', {
  actions: {
    getRoles(params = { search: undefined, pagination: undefined }) { 
      if(params.search != null && params.pagination != null){
        return axios.get('/api/access/get-all-roles?search=' +
          params.search +
          '&page=' +
          params.pagination.currentPage)
      }
      else if(params.search != null ){
        return axios.get('/api/access/get-all-roles?search=' +
          params.search)
      }
      else if(params.pagination != null){
        return axios.get('/api/access/get-all-roles?page=' +
          params.pagination.currentPage)
      }
      else {
        return axios.get('/api/access/get-all-roles' )
      }
    },

    async addRole(role) {
      return await new Promise((resolve, reject) => {
        axios.post('/api/access/create-role', role).then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    async updateRole(role) {
      return await new Promise((resolve, reject) => {
        axios.put('/api/access/update-role', role).then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    async getRole(id) {
      return await new Promise((resolve, reject) => {
        axios.get('/api/access/get-role/' + id).then(response => resolve(response)).catch(error => reject(error))
      })
    },

    async deleteRole(id) {
      return await new Promise((resolve, reject) => {
        axios.delete('/api/access/delete-role/' + id).then(response => resolve(response)).catch(error => reject(error))
      })
    },
  },
})
