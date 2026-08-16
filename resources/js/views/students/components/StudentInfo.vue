<template>
  <el-card class="profile-card" shadow="never">
    <div v-loading="loading" class="user-profile">
      <div class="box-center">
        <el-avatar :size="72" class="avatar">{{ initials }}</el-avatar>
        <div class="user-name">{{ student.name || '—' }}</div>
        <div class="user-meta">
          <el-tag v-if="student.stdclasses?.name" size="small" effect="plain" type="primary">
            {{ student.stdclasses.name }}
          </el-tag>
          <el-tag v-if="student.gender" size="small" effect="plain" :type="genderTagType">
            {{ student.gender }}
          </el-tag>
          <el-tag v-if="isOrphan" size="small" effect="dark" type="danger">Orphan</el-tag>
        </div>
      </div>

      <el-descriptions :column="1" border class="info-descriptions">
        <el-descriptions-item label="Father Name">{{ student.parents?.name || '—' }}</el-descriptions-item>
        <el-descriptions-item label="Address">{{ student.parents?.address || '—' }}</el-descriptions-item>
        <el-descriptions-item label="Registration No">{{ student.adminssion_number || '—' }}</el-descriptions-item>
        <el-descriptions-item label="Date of Admission">{{ formatDate(student.doa) }}</el-descriptions-item>
        <el-descriptions-item label="Date of Birth">{{ formatDate(student.dob) }}</el-descriptions-item>
        <el-descriptions-item label="Birth Form / NIC">{{ student.b_form || '—' }}</el-descriptions-item>
        <el-descriptions-item label="Cast">{{ student.cast || '—' }}</el-descriptions-item>
        <el-descriptions-item label="Previous School">{{ student.previous_school || '—' }}</el-descriptions-item>
        <el-descriptions-item label="Religion">{{ student.religion || '—' }}</el-descriptions-item>
      </el-descriptions>
    </div>
  </el-card>
</template>

<script>
import moment from 'moment'
import Resource from '@/api/resource'

const student = new Resource('students')

export default {
  name: 'StudentInfo',
  data() {
    return {
      loading: false,
      student: { parents: {}, stdclasses: {} }
    }
  },
  computed: {
    initials() {
      const name = (this.student.name || '').trim()
      if (!name) return '—'
      return name
        .split(/\s+/)
        .slice(0, 2)
        .map((w) => w.charAt(0))
        .join('')
        .toUpperCase()
    },
    genderTagType() {
      return this.student.gender === 'Male' ? 'primary' : 'danger'
    },
    isOrphan() {
      const v = this.student.is_orphan
      return v === true || v === 1 || v === '1' || v === 'Yes' || v === 'yes'
    }
  },
  mounted() {
    this.getProfile(this.$route.params.id)
  },
  methods: {
    async getProfile(stdid) {
      this.loading = true
      try {
        const { data } = await student.get(stdid)
        this.student = data.student
      } finally {
        this.loading = false
      }
    },
    formatDate(date) {
      return date ? moment(date).format('DD MMM, YYYY') : '—'
    }
  }
}
</script>

<style lang="scss" scoped>
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
