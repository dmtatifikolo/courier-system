import { defineStore } from 'pinia'
import axios from '@axios'

export const useAuditStore = defineStore('AuditStore', {
  actions: {
    getAudits(params = { search: undefined, pagination: undefined }) { 
      if(params.search != null && params.pagination != null){
        return axios.get('/api/audit?search=' +
          params.search +
          '&page=' +
          params.pagination.currentPage)
      }
      else if(params.search != null ){
        return axios.get('/api/audit?search=' +
          params.search)
      }
      else if(params.pagination != null){
        return axios.get('/api/audit?page=' +
          params.pagination.currentPage)
      }
      else {
        return axios.get('/api/audit' )
      }
    },

    getAudit(id) {
      return new Promise((resolve, reject) => {
        axios.get('/api/audit/' + id).then(response => resolve(response)).catch(error => reject(error))
      })
    },

    exportData(params = { search: '' }) {
      return new Promise((resolve, reject) => {
        axios.get("/api/audit/export-data?search=" + params.search, {
          responseType: "blob",
        }).then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
})
