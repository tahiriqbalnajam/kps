<template>
  <div class="navbar rowBC reset-el-dropdown">
    <div class="rowSC">
      <hamburger
        v-if="settings.showHamburger"
        :is-active="opened"
        class="hamburger-container"
        @toggleClick="toggleSideBar"
      />
      <breadcrumb class="breadcrumb-container" />
    </div>

    <!--nav title-->
    <div v-if="settings.showNavbarTitle" class="heardCenterTitle">{{ settings.showNavbarTitle }}</div>

    <div v-if="settings.ShowDropDown" class="right-menu rowSC">

      <!-- ── Session Selector ── -->
      <div class="session-selector">
        <el-select
          :model-value="useSessionStore.currentSessionId"
          placeholder="Select Session"
          size="small"
          style="width: 170px;"
          @change="handleSessionChange"
          :loading="!useSessionStore.loaded"
        >
          <template #prefix>
            <el-icon style="color:#fff;"><Calendar /></el-icon>
          </template>
          <el-option
            v-for="s in useSessionStore.sessions"
            :key="s.id"
            :value="s.id"
            :label="s.name"
          >
            <span>{{ s.name }}</span>
            <el-tag
              v-if="s.is_active"
              type="success"
              size="small"
              style="margin-left:6px;"
            >Active</el-tag>
          </el-option>
        </el-select>
      </div>
      <!-- ── end Session Selector ── -->

      <ScreenFull />

      <el-badge
        :value="pendingComplaints"
        :hidden="pendingComplaints === 0"
        :max="99"
        class="notification-badge"
      >
        <el-icon class="notification-icon" @click="goToComplaints"><Bell /></el-icon>
      </el-badge>

      <el-dropdown trigger="click" size="medium">
        <div class="avatar-wrapper">
          <img
            src="https://laravel-vue-admin.trumanwl.com/images/avatar.gif"
            class="user-avatar"
          />
          <CaretBottom style="width: 1em; height: 1em; margin-left: 4px" />
        </div>
        <template #dropdown>
          <el-dropdown-menu>
            <router-link to="/">
              <el-dropdown-item>Home</el-dropdown-item>
            </router-link>
            <el-dropdown-item divided @click="loginOut">Logout</el-dropdown-item>
          </el-dropdown-menu>
        </template>
      </el-dropdown>
    </div>
  </div>
</template>

<script setup>
import ScreenFull from '@/components/ScreenFull/index.vue'
import ComplaintResource from '@/api/complaint'
import { Bell, CaretBottom, Calendar } from '@element-plus/icons-vue'
import Breadcrumb from './Breadcrumb'
import Hamburger from './Hamburger'
import { appStore } from '@/store/app'
import { userStore } from '@/store/user'
import { sessionStore } from '@/store/session'
import { ElMessage } from 'element-plus'

const router = useRouter()
const useUserStore = userStore()
const useAppStore  = appStore()
const useSessionStore = sessionStore()

const settings = computed(() => useAppStore.settings)
const opened   = computed(() => useAppStore.sidebar.opened)

const toggleSideBar = () => useAppStore.toggleSideBar()

// ── Complaint notification badge ──
const complaintResource = new ComplaintResource()
const pendingComplaints = ref(0)
let complaintsTimer = null

const fetchPendingComplaints = async () => {
  try {
    const response = await complaintResource.list({ status: 'pending', limit: 1 })
    pendingComplaints.value = response?.complaints?.total ?? response?.data?.complaints?.total ?? 0
  } catch (e) {
    console.warn('Failed to fetch pending complaints count:', e)
  }
}

const goToComplaints = () => router.push('/communication/complaints')

// Initialise session list when the navbar mounts (once per session)
onMounted(() => {
  useSessionStore.init()
  fetchPendingComplaints()
  // Refresh the badge periodically so new complaints show up
  complaintsTimer = setInterval(fetchPendingComplaints, 60000)
})

onUnmounted(() => {
  if (complaintsTimer) clearInterval(complaintsTimer)
})

const handleSessionChange = (id) => {
  useSessionStore.setSession(id)
  const name = useSessionStore.currentSession?.name ?? ''
  ElMessage({ message: `Switched to session: ${name}`, type: 'success', duration: 1800 })
}

const loginOut = async () => {
  await useUserStore.logout().then(() => {
    useSessionStore.clear()
    router.push(`/login?redirect=/`)
  })
}
</script>

<style lang="scss" scoped>
.navbar {
  height: $navBarHeight;
  overflow: hidden;
  position: relative;
  background: #fff;
  box-shadow: 0 1px 4px rgba(0, 21, 41, 0.08);
  background: linear-gradient(to right, #4f46e5, #818cf8);
}

.session-selector {
  margin-right: 12px;
  display: flex;
  align-items: center;

  // Make the select look good on the blue navbar
  :deep(.el-select .el-input__wrapper) {
    background: rgba(255, 255, 255, 0.18);
    border: 1px solid rgba(255, 255, 255, 0.4);
    box-shadow: none;
    color: #fff;
  }
  :deep(.el-select .el-input__inner) {
    color: #fff;
    font-weight: 600;
    font-size: 13px;
  }
  :deep(.el-select .el-input__inner::placeholder) {
    color: rgba(255,255,255,0.7);
  }
  :deep(.el-select .el-select__caret) {
    color: #fff;
  }
}

.avatar-wrapper {
  margin-top: 5px;
  position: relative;
  cursor: pointer;

  .user-avatar {
    cursor: pointer;
    width: 40px;
    height: 40px;
    border-radius: 10px;
  }
}

.notification-badge {
  margin-right: 18px;
  margin-top: 6px;

  // Keep Element Plus' default corner position (badge straddles the top-right
  // corner of the bell, half outside the icon) — don't override the transform.
  :deep(.el-badge__content) {
    border: none;
  }
}

.notification-icon {
  font-size: 20px;
  color: #fff;
  cursor: pointer;

  &:hover {
    opacity: 0.85;
  }
}

.heardCenterTitle {
  text-align: center;
  position: absolute;
  top: 50%;
  left: 46%;
  font-weight: 600;
  font-size: 20px;
  transform: translate(-50%, -50%);
}

.right-menu {
  cursor: pointer;
  margin-right: 40px;
}
</style>
