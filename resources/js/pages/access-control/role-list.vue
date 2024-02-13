<script setup>
import { usePermissionStore } from '@/stores/usePermissionStore'
import { useRoleStore } from '@/stores/useRoleStore'
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
const roles = ref([])
const editedIndex = ref(-1)
 
const editedItem = ref({
  id: '',
  name: '',
  permissions: [],
})

const defaultItem = ref({
  id: '',
  name: '',
  permissions: [],
})

const formTitle = computed(() => editedIndex.value === -1 ? 'New Role' : 'Edit Role')

watch(dialog, val => {
  val || close()
})

watch(dialogDelete, val => {
  val || close()
})

const permissionStore = usePermissionStore()
const roleStore = useRoleStore()

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
  permissions: undefined,
})

const resetErrors = () => {
  errors.value = {
    name: undefined,
    permissions: undefined,
  }
}

const getPermissions = async() => {
  loading.value = true

  var response = await permissionStore.getPermissions().catch(error => {
    console.error(error)
    loading.value = false
  }).finally(()=>{
    loading.value = false
  })

  if (response.status == 200) {
    response.data.data.forEach(item => {
      permissions.value.push(item)
    })

    var totalPages = response.data.meta.last_page

    for (let i = 2; i <= totalPages; i++) {
      var response = await permissionStore.getPermissions({ pagination: { currentPage: i } }).catch(error => {
        console.error(error)
        loading.value = false
      }).finally(()=>{
        loading.value = false
      })

      if (response.status == 200) {
        response.data.data.forEach(item => {
          permissions.value.push(item)
        })
      }
    }
  }
}

const getRoles = async() => {
  loading.value = true

  await roleStore.getRoles({ search: search.value, pagination: pagination.value }).then(response => {
    if (response.status == 200) {
      if (roles.value.length != 0) {
        roles.value = []
      }

      response.data.data.forEach((role, index) => {
        roles.value.push({
          id: role.id,
          name: role.name,
          permissions: role.permissions,
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

const addRole = async() => {
  loading.value = true

  await roleStore.addRole(editedItem.value).then(response => {
    //console.log(response.data)

    roles.value.push(editedItem.value)
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

const updateRole = async() => {
  loading.value = true

  await roleStore.updateRole(editedItem.value).then(response => {
    //console.log(response.data)

    Object.assign(roles.value[editedIndex.value], editedItem.value)
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

const deleteRole = async id => {
  loading.value = true

  await roleStore.deleteRole(id).then(response => {
    let i = roles.value.map(data => data.id).indexOf(id)
    roles.value.splice(i, 1)
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
  getRoles()
  getPermissions()
})


const editItem = item => {
  editedIndex.value = roles.value.indexOf(item)
  editedItem.value = Object.assign({}, item)
  dialog.value = true
}

const deleteItem = item => {
  editedIndex.value = roles.value.indexOf(item)
  editedItem.value = Object.assign({}, item)
  dialogDelete.value = true
}

const deleteItemConfirm = async() => {
  await deleteRole(editedItem.value.id)
  loading.value = true
  await getRoles()
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
        await updateRole()
      } else {
        await addRole()
      }
      await getRoles()
      loading.value = false
    }
  })  
}

const onPageChange = ({ page, itemsPerPage, sortBy, sortDesc }) =>{
  pagination.value.currentPage = page
  getRoles()
}
</script>

<template>
  <VDataTableServer
    :headers="headers"
    :items="roles"
    :items-per-page="pagination.perPage"
    :items-length="pagination.totalItems"
    :loading="loading"
    loading-text="Loading..."
    class="elevation-1"
    @update:options="onPageChange"
  >
    <template #top>
      <VToolbar flat>
        <VToolbarTitle>Roles</VToolbarTitle>
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
          @keyup.enter="getRoles"
        />
        <VSpacer />
        <VDialog
          v-model="dialog"
          max-width="500px"
        >
          <template #activator="{ props }">
            <VBtn
              v-if="$can('create', 'Role')"
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
                  <VTextField
                    v-model="editedItem.name"
                    class="my-1"
                    label="Name"
                    :rules="[requiredValidator]"
                    :error-messages="errors.name"
                  />

                  <VAutocomplete
                    v-model="editedItem.permissions"
                    class="my-1"
                    :error-messages="errors.permissions"
                    :items="permissions"
                    item-value="id"
                    item-title="name"
                    label="Permissions"
                    multiple
                    chips
                    return-object
                  />
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
        v-if="$can('update', 'Role')"
        size="small"
        class="me-2"
        :disabled="loading"
        @click="editItem(item.raw)"
      >
        mdi-pencil
      </VIcon>
      <VIcon
        v-if="$can('delete', 'Role')"
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
    subject: Role
  </route>
