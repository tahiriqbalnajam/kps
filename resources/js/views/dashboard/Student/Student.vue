<template>
  <el-row :gutter="20">
    <el-col :span="6">
      <student-info />
    </el-col>
    <el-col :span="9">
      <el-card>
        <template #header>
          <div class="card-header" style="padding: 0 0 0 20px;">
            <h2>Attendance Report</h2>
          </div>
        </template>
        <attendance-info />
        <attendance-comments />
      </el-card>
    </el-col>
    <el-col :span="9">
      <TestInfo />
    </el-col>
  </el-row>
</template>

<script>
import StudentInfo from "@/views/student/components/StudentInfo.vue";
import AttendanceInfo from "@/views/student/components/AttendanceInfo.vue";
import AttendanceComments from "@/views/student/components/AttendanceComments.vue";
import TestInfo from "@/views/student/components/TestInfo.vue";
import Resource from "@/api/resource";
import {userStore} from "@/store/user";
const student = new Resource('students');
export default {
  name: 'StudentReport',
  components: {
    StudentInfo,
    AttendanceInfo,
    AttendanceComments,
    TestInfo
  },
  data() {
    return {
      presents: 'asdfas',
      users: [
        { name: 'afa', role: 'asdfa' },
        { name: 'afa', role: 'asdfa' }
      ]
    };
  },
  mounted() {
    //this.getProfile();
    const useUserStore = userStore()
  },
  methods: {
  }
};
</script>

<style scoped>
/* Add your component styles here */
</style>
