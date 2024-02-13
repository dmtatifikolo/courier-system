<script setup>
import { usePermissionStore } from '@/stores/usePermissionStore'
import { nextTick, ref, computed, watch, onMounted } from 'vue'
import { VDataTable } from 'vuetify/lib/labs/components'
import { requiredValidator } from '@validators'
import { useToast } from "vue-toastification"

const toast = useToast()

const refVForm = ref()
const dialog = ref(false)
const dialogDelete = ref(false)

const headers = ref([
  { title: "SN", key: "serialNum" },
  { title: "Name", key: "name" },
  { title: 'Actions', key: 'actions', sortable: false },
])
 
const permissions = ref([])
const editedIndex = ref(-1)
 
const editedItem = ref({
  id: '',
  name: '',
})

const defaultItem = ref({
  id: '',
  name: '',
})

const formTitle = computed(() => editedIndex.value === -1 ? 'New Permission' : 'Edit Permission')

watch(dialog, val => {
  val || close()
})

watch(dialogDelete, val => {
  val || close()
})

const permissionStore = usePermissionStore()

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

const errors = ref({
  name: undefined,
})

const resetErrors = () => {
  errors.value = {
    name: undefined,
  }
}

const getPermissions = async() => {
  loading.value = true

  await permissionStore.getPermissions({ search: search.value, pagination: pagination.value }).then(response => {
    if (response.status == 200) {
      if (permissions.value.length != 0) {
        permissions.value = []
      }

      response.data.data.forEach((permission, index) => {
        permissions.value.push({
          id: permission.id,
          name: permission.name,
          serialNum: index + 1,
        })
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

const addPermission = async() => {
  loading.value = true

  await permissionStore.addPermission(editedItem.value).then(response => {
    //console.log(response.data)

    permissions.value.push(editedItem.value)
    toast.success(response.data.message)
    loading.value = false

    close()

  }).catch(error => {
    //console.error(error)

    if (error.hasOwnProperty("response") && (error.response.status == 422)) {
      errors.value = error.response.data.errors

      toast.error(error.response.data.message)
    } else {
      toast.error("Error")
    }

    loading.value = false
  }).finally(()=>{
    loading.value = false
  })
}

const updatePermission = async() => {
  loading.value = true

  await permissionStore.updatePermission(editedItem.value).then(response => {
    //console.log(response.data)

    Object.assign(permissions.value[editedIndex.value], editedItem.value)
    toast.success(response.data.message)
    loading.value = false
    close()

  }).catch(error => {
    //console.error(error)

    if (error.hasOwnProperty("response") && (error.response.status == 422)) {
      errors.value = error.response.data.errors
      
      toast.error(error.response.data.message)
    }else if(error.response.status == 409){
      toast.error(error.response.data.message)
    }else {
      toast.error("Error")
    }

    loading.value = false
  }).finally(()=>{
    loading.value = false
  })
}

const deletePermission = async id => {
  loading.value = true

  await permissionStore.deletePermission(id).then(response => {
    let i = permissions.value.map(data => data.id).indexOf(id)
    permissions.value.splice(i, 1)
    toast.success(response.data.message)
    loading.value = false

    closeDelete()
    
  }).catch(error => {
    //console.error(error)
    
    if (error.hasOwnProperty("response") && (error.response.status == 422)) {
      errors.value = error.response.data.errors
      
      toast.error(error.response.data.message)
    }else if(error.response.status == 409){
      toast.error(error.response.data.message)
    }else {
      toast.error("Error")
    }

    loading.value = false
  }).finally(()=>{
    loading.value = false
  })
}

onMounted(() => {
  getPermissions()
})


const editItem = item => {
  editedIndex.value = permissions.value.indexOf(item)
  editedItem.value = Object.assign({}, item)
  dialog.value = true
}

const deleteItem = item => {
  editedIndex.value = permissions.value.indexOf(item)
  editedItem.value = Object.assign({}, item)
  dialogDelete.value = true
}

const deleteItemConfirm = async() => {
  await deletePermission(editedItem.value.id)
  loading.value = true
  await getPermissions()
  loading.value = false
}

const close = ()=> {
  dialog.value = false
  nextTick(() => {
    editedItem.value = Object.assign({}, defaultItem.value)
    editedIndex.value = -1
  })
  resetErrors()
}

const closeDelete = () => {
  dialogDelete.value = false
  nextTick(() => {
    editedItem.value = Object.assign({}, defaultItem.value)
    editedIndex.value = -1
  })
}

const save = () => {
  refVForm.value?.validate().then(async({ valid: isValid }) => {
    if (isValid)
    {
      loading.value = true
      if (editedIndex.value > -1) {
        await updatePermission()
      } else {
        await addPermission()
      }
      await getPermissions()
      loading.value = false
    }
  })  
}

const onPageChange = ({ page, itemsPerPage, sortBy, sortDesc }) =>{
  pagination.value.currentPage = page
  getPermissions()
}
</script>

<template>
  <VDataTableServer
    :headers="headers"
    :items="permissions"
    :items-per-page="pagination.perPage"
    :items-length="pagination.totalItems"
    :loading="loading"
    loading-text="Loading..."
    class="elevation-1"
    @update:options="onPageChange"
  >
    <template #top>
      <VToolbar flat>
        <VToolbarTitle>Permissions</VToolbarTitle>
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
          @keyup.enter="getPermissions"
        />
        <VSpacer />
        <VDialog
          v-model="dialog"
          max-width="500px"
        >
          <template #activator="{ props }">
            <VBtn
              v-if="$can('create', 'Permission')"
              color="primary"
              dark             
              class="mb-2"           
              :disabled="loading"
              v-bind="props"
            >
              New
            </VBtn>
          </template>
          <VCard>
            <VCardTitle>
              <span class="text-h5">{{ formTitle }}</span>
            </VCardTitle>

            <VCardText>
              <VContainer>
                <VForm
                  ref="refVForm"
                  @submit.prevent="save"
                >
                  <VRow>
                    <VCol cols="12">
                      <VTextField
                        v-model="editedItem.name"
                        label="Name"
                        :rules="[requiredValidator]"
                        :error-messages="errors.name"
                      />
                    </VCol>
                  </VRow>
                </VForm>
              </VContainer>
            </VCardText>

            <VCardActions>
              <VSpacer />
              <VBtn
                color="blue-darken-1"
                variant="text"
                @click="close"
              >
                Cancel
              </VBtn>
              <VBtn
                color="blue-darken-1"
                variant="text"
                @click="save"
              >
                Save
              </VBtn>
            </VCardActions>
          </VCard>
        </VDialog>
        <VDialog
          v-model="dialogDelete"
          max-width="500px"
        >
          <VCard>
            <VCardTitle class="text-h5">
              Are you sure you want to delete this item?
            </VCardTitle>
            <VCardActions>
              <VSpacer />
              <VBtn
                color="blue-darken-1"
                variant="text"
                @click="closeDelete"
              >
                Cancel
              </VBtn>
              <VBtn
                color="blue-darken-1"
                variant="text"
                :disabled="loading"
                @click="deleteItemConfirm"
              >
                OK
              </VBtn>
              <VSpacer />
            </VCardActions>
          </VCard>
        </VDialog>
      </VToolbar>
    </template>
    <template #item.actions="{ item }">
      <VIcon
        v-if="$can('update', 'Permission')"
        size="small"
        class="me-2"
        :disabled="loading"
        @click="editItem(item.raw)"
      >
        mdi-pencil
      </VIcon>
      <VIcon
        v-if="$can('delete', 'Permission')"
        size="small"
        :disabled="loading"
        @click="deleteItem(item.raw)"
      >
        mdi-delete
      </VIcon>
    </template>
  </VDataTableServer>
</template>

<route lang="yaml">
  meta:
    action: view
    subject: Permission
  </route>
