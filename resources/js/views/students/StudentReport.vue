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
        </el-card>     
      </el-col>
      <el-col :span="9">
        <TestInfo />
      </el-col>
    </el-row>
</template>

<script>
import StudentInfo from './components/StudentInfo.vue';
import AttendanceInfo from './components/AttendanceInfo.vue';
import TestInfo from './components/TestInfo.vue';
import Resource from '@/api/resource';
const student = new Resource('students');
export default {
  name: 'StudentReport',
  components: {
    StudentInfo,
    AttendanceInfo,
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
  },
  methods: {
  }
};
</script>

<style scoped>
/* Add your component styles here */
</style>
