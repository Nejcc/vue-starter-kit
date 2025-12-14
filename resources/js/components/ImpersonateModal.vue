<template>
  <Dialog v-model:open="isOpen">
    <DialogContent class="sm:max-w-2xl">
      <DialogHeader>
        <DialogTitle>Impersonate User</DialogTitle>
        <DialogDescription>
          Search and select a user to impersonate
        </DialogDescription>
      </DialogHeader>

      <div class="space-y-4">
        <div class="relative">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <Input
            v-model="searchQuery"
            placeholder="Search by name or email..."
            class="pl-10"
            @input="handleSearch"
          />
        </div>

        <div v-if="isLoading" class="flex items-center justify-center py-8">
          <div class="text-muted-foreground">Loading users...</div>
        </div>

        <div v-else-if="filteredUsers.length === 0" class="flex items-center justify-center py-8">
          <div class="text-muted-foreground">No users found</div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-[400px] overflow-y-auto">
          <UserCard
            v-for="user in filteredUsers"
            :key="user.id"
            :user="user"
            @impersonate="handleImpersonate"
          />
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
// Import shadcn-vue components - adjust path if needed
// If these don't exist, install with: npx shadcn-vue@latest add dialog input
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Search } from 'lucide-vue-next'
import UserCard from './UserCard.vue'
import { useDebounceFn } from '@vueuse/core'

interface User {
  id: number
  name: string
  email: string
  initials: string
}

interface Props {
  open?: boolean
  users?: User[]
}

const props = withDefaults(defineProps<Props>(), {
  open: false,
  users: () => [],
})

const emit = defineEmits<{
  'update:open': [value: boolean]
}>()

const isOpen = computed({
  get: () => props.open,
  set: (value) => emit('update:open', value),
})

const searchQuery = ref('')
const isLoading = ref(false)
const localUsers = ref<User[]>(props.users)

watch(() => props.users, (newUsers) => {
  localUsers.value = newUsers
}, { immediate: true })

const filteredUsers = computed(() => {
  if (!searchQuery.value.trim()) {
    return localUsers.value
  }

  const query = searchQuery.value.toLowerCase()
  return localUsers.value.filter(
    (user) =>
      user.name.toLowerCase().includes(query) ||
      user.email.toLowerCase().includes(query)
  )
})

const debouncedSearch = useDebounceFn(async (query: string) => {
  if (!query.trim()) {
    isLoading.value = false
    return
  }

  isLoading.value = true
  router.get(
    '/impersonate',
    { search: query },
    {
      preserveState: true,
      preserveScroll: true,
      only: ['users'],
      onSuccess: (page) => {
        if (page.props.users) {
          localUsers.value = page.props.users as User[]
        }
        isLoading.value = false
      },
      onError: () => {
        isLoading.value = false
      },
    }
  )
}, 300)

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    isLoading.value = true
    debouncedSearch(searchQuery.value)
  } else {
    isLoading.value = false
    // Reload all users when search is cleared
    router.get(
      '/impersonate',
      {},
      {
        preserveState: true,
        preserveScroll: true,
        only: ['users'],
        onSuccess: (page) => {
          if (page.props.users) {
            localUsers.value = page.props.users as User[]
          }
        },
      }
    )
  }
}

const handleImpersonate = (userId: number) => {
  router.post(
    '/impersonate',
    { user_id: userId },
    {
      onSuccess: () => {
        isOpen.value = false
      },
    }
  )
}
</script>
