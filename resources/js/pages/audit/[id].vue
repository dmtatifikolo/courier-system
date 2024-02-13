<script setup>
import { useAuditStore } from '@/stores/useAuditStore'
import { requiredValidator } from '@validators'
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useToast } from "vue-toastification"

const toast = useToast()
const route = useRoute()

const audit = ref({
  id: "",
  userType: "",
  event: "",
  auditableType: "",
  oldValues: [],
  newValues: [],
  ipAddress: "",
  userAgent: "",
  createdAt: "",
  updatedAt: "",
  user: "",
})

const auditStore = useAuditStore()

const loading = ref(true)

const getAudit = async() => {
  loading.value = true

  await auditStore.getAudit(route.params.id).then(response => {
    audit.value = response.data.data

    console.log(response.data.data)
    
    loading.value = false
  }).catch(error => {
    console.error(error)
    loading.value = false
  }).finally(()=>{
    loading.value = false
  })
}
 
onMounted(() => {
  getAudit()
})
</script>

<template>
  <VContainer>
    <VRow>
      <VCol
        cols="12"
        xl="6"
      >
        <table class="mt-2 mt-xl-0 w-100">
          <tr>
            <th class="pb-50">
              <span class="font-weight-bold">Id</span>
            </th>
            <td class="pb-50">
              {{ audit.id }}
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <span class="font-weight-bold">User Type</span>
            </th>
            <td class="pb-50 text-capitalize">
              {{ audit.userType }}
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <span class="font-weight-bold">Event</span>
            </th>
            <td class="pb-50">
              {{ audit.event }}
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <span class="font-weight-bold">Auditable Type</span>
            </th>
            <td class="pb-50 text-capitalize">
              {{ audit.auditableType }}
            </td>
          </tr>
          <tr>
            <th>
              <span class="font-weight-bold">IP Address</span>
            </th>
            <td>
              {{ audit.ipAddress }}
            </td>
          </tr>
          <tr>
            <th>
              <span class="font-weight-bold">User Agent</span>
            </th>
            <td>
              {{ audit.userAgent }}
            </td>
          </tr>
          <tr>
            <th>
              <span class="font-weight-bold">Created At</span>
            </th>
            <td>
              {{ audit.createdAt }}
            </td>
          </tr>
          <tr>
            <th>
              <span class="font-weight-bold">Updated At</span>
            </th>
            <td>
              {{ audit.updatedAt }}
            </td>
          </tr>
          <tr>
            <th>
              <span class="font-weight-bold">User</span>
            </th>
            <td>
              {{ audit.user }}
            </td>
          </tr>
          <tr>
            <td>
              <span class="font-weight-bold">Old Values</span>
            </td>
            <td>
              <tr
                v-for="item in Object.entries(audit.oldValues)"
                :key="item"
              >
                {{ item[0] }} : {{ item[1] }}
              </tr>
            </td>
          </tr>
          <tr>
            <td>
              <span class="font-weight-bold">New Values</span>
            </td>
            <td>
              <tr
                v-for="item in Object.entries(audit.newValues)"
                :key="item"
              >
                {{ item[0] }} : {{ item[1] }}
              </tr>
            </td>
          </tr>
        </table>
      </VCol>
    </VRow>
  </VContainer>
</template>

<style scoped>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
}
</style>

<route lang="yaml">
  meta:
    action: view
    subject: Audit Log
  </route>
