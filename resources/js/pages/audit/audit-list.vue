<script setup>
import { useAuditStore } from '@/stores/useAuditStore'
import { requiredValidator } from '@validators'
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { useToast } from "vue-toastification"

const toast = useToast()

const headers = ref([
  { title: "SN", key: "serialNum" },
  { title: "Auditable Type", key: "auditableType" },
  { title: "Event", key: "event" },
  { title: "User", key: "user" },
  { title: "Created At", key: "createdAt" },

  { title: 'Actions', key: 'actions', sortable: false },
])
 
const audits = ref([])

const auditStore = useAuditStore()

const pagination =  ref(
  {
    currentPage: 1,
    from: 0,
    lastPage: 0,
    perPage: 0,
    to: 0,
    totalItems: 0,
  })

const search = ref('')
const loading = ref(true)

const getAudits = async() => {
  loading.value = true

  await auditStore.getAudits({ search: search.value, pagination: pagination.value }).then(response => {
    if (response.status == 200) {
      if (audits.value.length != 0) {
        audits.value = []
      }

      response.data.data.forEach((audit, index) => {
        audit.serialNum = index + 1
        audits.value.push(audit)
      })
      
      pagination.value.currentPage = response.data.meta.current_page
      pagination.value.lastPage = response.data.meta.last_page
      pagination.value.perPage = response.data.meta.per_page
      pagination.value.to = response.data.meta.to == null ? 0 : response.data.meta.to
      pagination.value.from = response.data.meta.from == null ? 0 : response.data.meta.from
      pagination.value.totalItems = response.data.meta.total
    }
    loading.value = false
  }).catch(error => {
    console.error(error)
    loading.value = false
  }).finally(()=>{
    loading.value = false
  })
}

const exportData = async() => {
  loading.value = true

  await auditStore.exportData({ search: search.value })
    .then(response => {
      if (response.status == 200) {
        let link = document.createElement("a")
        link.href = window.URL.createObjectURL(new Blob([response.data]))
        link.download = "auditData.xlsx"
        link.click()
      }
    })
    .catch(function (error) {
      console.log(error)
    })
    .finally(function () {
      loading.value = false
    })
}

onMounted(() => {
  getAudits()
})

const onPageChange = ({ page, itemsPerPage, sortBy, sortDesc }) =>{
  pagination.value.currentPage = page
  getAudits()
}
</script>

<template>
  <VDataTableServer
    :headers="headers"
    :items="audits"
    :items-per-page="pagination.perPage"
    :items-length="pagination.totalItems"
    :loading="loading"
    loading-text="Loading..."
    class="elevation-1"
    @update:options="onPageChange"
  >
    <template #top>
      <VToolbar flat>
        <VToolbarTitle>Audit Logs</VToolbarTitle>
        <VDivider
          class="mx-4"
          inset
          vertical
        />
        <VSpacer />
        <VTextField
          v-model="search"
          append-icon="mdi-magnify"
          label="Search"
          single-line
          hide-details
          @keyup.enter="getAudits"
        />
        <VSpacer />
        <VBtn
          v-if="$can('view', 'Audit Log')"
          color="primary"
          dark             
          class="mb-2" 
          :disabled="loading"
          v-bind="props"
          @click="exportData"
        >
          Export
        </VBtn>
      </VToolbar>
    </template>
    <template #item.actions="{ item }">
      <VIcon
        v-if="$can('view', 'Audit Log')"
        size="small"
        class="me-2"
        :disabled="loading"
        @click="$router.push({ name: 'audit-id', params: { id: item.raw.id } })"
      >
        mdi-eye
      </VIcon>
    </template>
  </VDataTableServer>
</template>

<route lang="yaml">
  meta:
    action: view
    subject: Audit Log
  </route>
