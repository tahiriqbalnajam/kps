<template>
  <el-card class="profile-card" shadow="never">
    <div v-loading="loading" class="user-profile">
      <div class="box-center">
        <el-avatar :size="72" class="avatar">{{ initials }}</el-avatar>
        <div class="user-name">{{ teacher.name || '—' }}</div>
        <div class="user-meta">
          <el-tag v-if="teacher.gender" size="small" effect="plain" :type="genderTagType">
            {{ teacher.gender }}
          </el-tag>
          <el-tag v-if="teacher.experience" size="small" effect="plain" type="warning">
            {{ teacher.experience }}
          </el-tag>
        </div>
      </div>

      <el-descriptions :column="1" border class="info-descriptions">
        <el-descriptions-item label="Father Name">{{ teacher.father_name || '—' }}</el-descriptions-item>
        <el-descriptions-item label="Date of Joining">{{ dateformat(teacher.doj) }}</el-descriptions-item>
        <el-descriptions-item label="Education">{{ teacher.education || '—' }}</el-descriptions-item>
        <el-descriptions-item label="CNIC">{{ teacher.cnic || '—' }}</el-descriptions-item>
        <el-descriptions-item label="Address">{{ teacher.address || '—' }}</el-descriptions-item>
        <el-descriptions-item label="Phone">{{ teacher.phone || '—' }}</el-descriptions-item>
      </el-descriptions>
    </div>
  </el-card>
</template>

<script>
import moment from 'moment'
import Resource from '@/api/resource'
const teachRes = new Resource('teachers')

export default {
  name: 'TeacherInfo',
  data() {
    return {
      loading: false,
      teacher: {}
    }
  },
  computed: {
    initials() {
      const name = (this.teacher.name || '').trim()
      if (!name) return '—'
      return name
        .split(/\s+/)
        .slice(0, 2)
        .map((w) => w.charAt(0))
        .join('')
        .toUpperCase()
    },
    genderTagType() {
      return this.teacher.gender === 'Male' ? 'primary' : 'danger'
    }
  },
  mounted() {
    this.getTeacher(this.$route.params.id)
  },
  methods: {
    async getTeacher(id) {
      this.loading = true
      try {
        const { data } = await teachRes.get(id)
        this.teacher = data.teacher[0] || {}
      } finally {
        this.loading = false
      }
    },
    dateformat(date) {
      return !date ? '—' : moment(date).format('DD MMM, YYYY')
    }
  }
}
</script>

<style scoped lang="scss">
.user-profile {
  .box-center {
    text-align: center;
    padding-bottom: 18px;
    border-bottom: 1px dashed #e4e7ed;
    margin-bottom: 16px;
  }

  .avatar {
    background: linear-gradient(135deg, #4f46e5, #818cf8);
    border: 3px solid #e0e7ff;
    color: #fff;
    font-size: 26px;
    font-weight: 700;
    margin-bottom: 12px;
  }

  .user-name {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
  }

  .user-meta {
    margin-top: 8px;
    display: flex;
    justify-content: center;
    gap: 6px;
    flex-wrap: wrap;
  }

  .info-descriptions {
    :deep(.el-descriptions__label) {
      font-weight: 600;
      color: #5b6b82;
      width: 140px;
    }

    :deep(.el-descriptions__content) {
      color: #1e293b;
    }
  }
}
</style>
