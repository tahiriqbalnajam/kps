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
              <el-table-column label="Obtained" prop="score"/>
              <el-table-column label="%" prop="percentage"> 
                <template #default="scope">
                  {{ Math.round(scope.row.percentage) }}
                </template>
                </el-table-column>
            </el-table>          
          </el-col>
          <el-col :span="8">
            <el-progress type="dashboard" :percentage="subject.overall_percentage" :width="90">
              <template #default="{ percentage }">
                <span class="percentage-value">{{ Math.round(percentage) }}%</span>
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
      const studentId = this.$route.params.id; // Accessing the URL parameter named 'id'
      this.getProfile(studentId);
    },
    methods: {
      async getProfile(stdid) {  
        let { data } = await getSubjectWiseScores(stdid);
        console.log(data);
        this.subjects = data.results;
      }
    }
  };
</script>

<style lang="scss" scoped>

</style>