<template>
  <el-card class="comments-card" shadow="never">
    <template #header>
      <div class="card-header"><strong>Attendance Comments</strong></div>
    </template>
    <el-table
      v-loading="loading"
      :data="comments"
      stripe
      size="small"
      empty-text="No comments yet"
      style="width: 100%"
    >
      <el-table-column label="Date" width="140">
        <template #default="scope">
          {{ dateformat(scope.row.attendance_date) }}
        </template>
      </el-table-column>
      <el-table-column label="Comments" prop="comment" />
    </el-table>
  </el-card>
</template>

<script>
import moment from 'moment'
import { getAttComments } from '@/api/attendance'

export default {
  name: 'AttendanceComments',
  data() {
    return {
      loading: false,
      comments: []
    }
  },
  mounted() {
    this.getComments(this.$route.params.id)
  },
  methods: {
    async getComments(stdid) {
      this.loading = true
      try {
        const { data } = await getAttComments(stdid)
        this.comments = data.comments
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

<style scoped>
.comments-card {
  margin-top: 16px;
}
</style>
