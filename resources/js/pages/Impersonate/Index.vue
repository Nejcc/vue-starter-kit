<template>
  <div>
    <Head title="Impersonate User" />

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

      <div v-if="users.length === 0" class="flex items-center justify-center py-8">
        <div class="text-muted-foreground">No users found</div>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <UserCard
          v-for="user in users"
          :key="user.id"
          :user="user"
          @impersonate="handleImpersonate"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { router, Head } from '@inertiajs/vue3'
import { Input } from '@/components/ui/input'
import { Search } from 'lucide-vue-next'
import UserCard from '@/components/UserCard.vue'
import { useDebounceFn } from '@vueuse/core'

interface User {
  id: number
  name: string
  email: string
  initials: string
}

interface Props {
  users: User[]
  search?: string
}

const props = defineProps<Props>()

const searchQuery = ref(props.search || '')

const debouncedSearch = useDebounceFn((query: string) => {
  router.get(
    '/impersonate',
    { search: query },
    {
      preserveState: true,
      preserveScroll: true,
      only: ['users', 'search'],
    }
  )
}, 300)

const handleSearch = () => {
  debouncedSearch(searchQuery.value)
}

const handleImpersonate = (userId: number) => {
  router.post(
    '/impersonate',
    { user_id: userId },
    {
      onSuccess: () => {
        // Redirect handled by backend
      },
    }
  )
}
</script>
