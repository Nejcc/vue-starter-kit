<template>
  <div v-if="canImpersonate">
    <SidebarMenu>
      <SidebarMenuItem>
        <SidebarMenuButton
          class="text-neutral-600 hover:text-neutral-800 dark:text-neutral-300 dark:hover:text-neutral-100"
          @click="openModal"
        >
          <UserRound class="h-4 w-4" />
          <span>Impersonate</span>
        </SidebarMenuButton>
      </SidebarMenuItem>
    </SidebarMenu>

    <ImpersonateModal
      v-model:open="isModalOpen"
      :users="users"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { UserRound } from 'lucide-vue-next'
import { usePage } from '@inertiajs/vue3'
import {
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar'
import ImpersonateModal from './ImpersonateModal.vue'

interface User {
  id: number
  name: string
  email: string
  initials: string
}

const page = usePage()
const auth = computed(() => page.props.auth as {
  user?: {
    roles?: string[]
    permissions?: string[]
  } | null
})

const canImpersonate = computed(() => {
  const user = auth.value?.user
  if (!user) {
    return false
  }
  
  const roles = user.roles ?? []
  const permissions = user.permissions ?? []
  
  // Check if user is super-admin or has impersonate permission
  return roles.includes('super-admin') || permissions.includes('impersonate')
})

const isModalOpen = ref(false)
const users = ref<User[]>([])

const openModal = async () => {
  // Load users when modal opens
  await loadUsers()
  isModalOpen.value = true
}

const loadUsers = async () => {
  try {
    const response = await fetch('/impersonate?partial=1', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
    })

    if (response.ok) {
      const data = await response.json()
      if (data.users) {
        users.value = data.users
      }
    }
  } catch (error) {
    console.error('Failed to load users:', error)
  }
}
</script>
