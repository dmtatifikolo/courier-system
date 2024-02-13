<script setup>
import { useUserStore } from '@/stores/useUserStore'
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
  { title: "Username", key: "username" },
  { title: "Email", key: "email" },
  { title: "Role", key: "roleName" },
  { title: 'Actions', key: 'actions', sortable: false },
])
 
const users = ref([])
const roles = ref([])
const editedIndex = ref(-1)
 
const editedItem = ref({
  id: '',
  name: '',
  username: '',
  email: '',
  password: '',
  roleId: '',
  role: '',
})

const defaultItem = ref({
  id: '',
  name: '',
  username: '',
  email: '',
  password: '',
  roleId: '',
  role: '',
})

const formTitle = computed(() => editedIndex.value === -1 ? 'New User' : 'Edit User')

watch(dialog, val => {
  val || close()
})

watch(dialogDelete, val => {
  val || close()
})

const userStore = useUserStore()
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
  username: undefined,
  email: undefined,
  password: undefined,

  //role: undefined,
  roleId: undefined,
})

const resetErrors = () => {
  errors.value = {
    name: undefined,
    username: undefined,
    email: undefined,
    password: undefined,

    //role: undefined,
    roleId: undefined,
  }
}

const getRoles = async() => {
  loading.value = true

  var response = await roleStore.getRoles().catch(error => {
    console.error(error)
    loading.value = false
  }).finally(()=>{
    loading.value = false
  })

  if (response.status == 200) {
    response.data.data.forEach(item => {
      roles.value.push(item)
    })

    var totalPages = response.data.meta.last_page

    for (let i = 2; i <= totalPages; i++) {
      var response = await roleStore.getRoles({ pagination: { currentPage: i } }).catch(error => {
        console.error(error)
        loading.value = false
      }).finally(()=>{
        loading.value = false
      })

      if (response.status == 200) {
        response.data.data.forEach(item => {
          roles.value.push(item)
        })
      }
    }
  }
}

const getUsers = async() => {
  loading.value = true

  await userStore.getUsers({ search: search.value, pagination: pagination.value }).then(response => {
    if (response.status == 200) {
      if (users.value.length != 0) {
        users.value = []
      }

      response.data.data.forEach((user, index) => {
        users.value.push({
          id: user.id,
          name: user.name,
          username: user.username,
          email: user.email,
          password: user.password,
          roleId: user.role.id,
          roleName: user.role.name,
          role: user.role,
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

const addUser = async() => {
  loading.value = true

  await userStore.addUser(editedItem.value).then(response => {
    users.value.push(editedItem.value)
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

const updateUser = async() => {
  loading.value = true

  await userStore.updateUser(editedItem.value).then(response => {
    Object.assign(users.value[editedIndex.value], editedItem.value)
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

  await userStore.deleteUser(id).then(response => {
    let i = users.value.map(data => data.id).indexOf(id)
    users.value.splice(i, 1)
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
  getUsers()
  getRoles()
})


const editItem = item => {
  editedIndex.value = users.value.indexOf(item)
  editedItem.value = Object.assign({}, item)
  dialog.value = true
}

const deleteItem = item => {
  editedIndex.value = users.value.indexOf(item)
  editedItem.value = Object.assign({}, item)
  dialogDelete.value = true
}

const deleteItemConfirm = async() => {
  await deleteRole(editedItem.value.id)
  loading.value = true
  await getUsers()
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

      editedItem.value.roleId = editedItem.value.role.id

      if (editedIndex.value > -1) {
        await updateUser()
      } else {
        await addUser()
      }
      await getUsers()
      loading.value = false
    }
  })  
}

const onPageChange = ({ page, itemsPerPage, sortBy, sortDesc }) =>{
  pagination.value.currentPage = page
  getUsers()
}
</script>

<template>
  <VDataTableServer
    :headers="headers"
    :items="users"
    :items-per-page="pagination.perPage"
    :items-length="pagination.totalItems"
    :loading="loading"
    loading-text="Loading..."
    class="elevation-1"
    @update:options="onPageChange"
  >
    <template #top>
      <VToolbar flat>
        <VToolbarTitle>Users</VToolbarTitle>
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
          @keyup.enter="getUsers"
        />
        <VSpacer />
        <VDialog
          v-model="dialog"
          max-width="500px"
        >
          <template #activator="{ props }">
            <VBtn
              v-if="$can('create', 'User')"
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

                  <VTextField
                    v-model="editedItem.username"
                    class="my-1"
                    label="Username"
                    :rules="[requiredValidator]"
                    :error-messages="errors.username"
                  />

                  <VTextField
                    v-model="editedItem.email"
                    class="my-1"
                    label="Email"
                    :rules="[requiredValidator]"
                    :error-messages="errors.email"
                  />

                  <VTextField
                    v-model="editedItem.password"
                    class="my-1"
                    label="Password"
                    type="password"
                    :error-messages="errors.password"
                  />

                  <VAutocomplete
                    v-model="editedItem.role"
                    class="my-1"
                    :error-messages="errors.roleId"
                    :items="roles"
                    item-value="id"
                    item-title="name"
                    label="Role"
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
        v-if="$can('update', 'User')"
        size="small"
        class="me-2"
        :disabled="loading"
        @click="editItem(item.raw)"
      >
        mdi-pencil
      </VIcon>
      <VIcon
        v-if="$can('delete', 'User')"
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
    subject: User
  </route>
