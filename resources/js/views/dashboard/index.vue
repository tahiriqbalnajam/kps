<template>
  <div class="dashboard-container scroll-y">
    <Admin v-if="Object.values(roles).includes('admin')" />
    <Student v-else-if="Object.values(roles).includes('student')" />
    <Editor v-else />
  </div>
</template>

<script setup>
import Admin from './Admin'
import Editor from './Editor'
import Student from './Student'
import { computed } from 'vue'
import { userStore } from '@/store/user'
const useUserStore = userStore()
const roles = computed(() => {
  return useUserStore.roles
})
</script>

<style scoped lang="scss"></style>
