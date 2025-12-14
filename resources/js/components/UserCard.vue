<template>
  <button
    type="button"
    class="flex items-center gap-3 p-4 rounded-lg border border-border bg-card hover:bg-accent hover:text-accent-foreground transition-colors text-left w-full"
    @click="handleClick"
  >
    <div
      class="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-primary-foreground text-sm font-medium shrink-0"
    >
      {{ user.initials }}
    </div>
    <div class="flex-1 min-w-0">
      <div class="font-medium truncate">{{ user.name }}</div>
      <div class="text-sm text-muted-foreground truncate">{{ user.email }}</div>
    </div>
    <UserRound class="h-4 w-4 text-muted-foreground shrink-0" />
  </button>
</template>

<script setup lang="ts">
import { UserRound } from 'lucide-vue-next'

interface User {
  id: number
  name: string
  email: string
  initials: string
}

interface Props {
  user: User
}

const props = defineProps<Props>()

const emit = defineEmits<{
  impersonate: [userId: number]
}>()

const handleClick = () => {
  emit('impersonate', props.user.id)
}
</script>
