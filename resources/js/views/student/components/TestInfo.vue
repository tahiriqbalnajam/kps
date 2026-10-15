<template>
    <el-card>
      <div v-for="subject in subjects">
        <el-row>
          <el-col :span="24"><h3>{{ subject.subject }}</h3></el-col>
        </el-row>
        <el-row justify="center" :gutter="20">
          <el-col :span="16">
            <el-table :data="subject.tests" border stripe size="small">
              <el-table-column label="Date" prop="test_date"/>
              <el-table-column label="Total Marks" prop="total_marks"/>
              <el-table-column label="Obtained">
                <template #default="scope">
                  {{ scope.row.absent === 'yes' ? 'A' : scope.row.score }}
                </template>
              </el-table-column>
              <el-table-column label="%">
                <template #default="scope">
                  {{ scope.row.absent === 'yes' ? 'A' : Math.round(scope.row.percentage) }}
                </template>
                </el-table-column>
            </el-table>
          </el-col>
          <el-col :span="8">
            <el-progress type="dashboard" :percentage="subject.has_graded_work === false ? 0 : Math.round(subject.overall_percentage || 0)" :width="90">
              <template #default="{ percentage }">
                <!-- Every test in this subject was absent — there is no average, so show "A". -->
                <span class="percentage-value">{{ subject.has_graded_work === false ? 'A' : Math.round(percentage) + '%' }}</span>
              </template>
            </el-progress>
          </el-col>
        </el-row>
      </div>
    </el-card>
</template>
<script>
  import Resource from '@/api/resource';
  import { useRoute } from 'vue-router';
  const route = useRoute()
  import { getSubjectWiseScores } from '@/api/student';
  import {userStore} from "@/store/user";
  export default {
    name: 'StudentInfo',
    components: {
    },
    data() {
      return {
        subjects: {},
        query: {
          studentid: '',
        }
      };
    },
    mounted() {
        const useUserStore = userStore()
        const studentId = useUserStore.student.student_id; // Accessing the URL parameter named 'id'
      this.getProfile(studentId);
    },
    methods: {
      async getProfile(stdid) {
        let { data } = await getSubjectWiseScores(stdid);
        this.subjects = data.results;
      }
    }
  };
</script>

<style lang="scss" scoped>

</style>
