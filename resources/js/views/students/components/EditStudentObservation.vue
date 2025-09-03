<template>
  <div class="edit-student-observation">
    <el-card>
      <template #header>
        <div class="card-header">
          <h2>Edit Student Observation</h2>
          <el-button 
            icon="Close" 
            circle 
            @click="$router.push({ name: 'student-observations' })"
          />
        </div>
      </template>

      <student-observation-form 
        v-if="observation"
        :edit-mode="true"
        :observation-data="observation"
        @saved="onSaved" 
        @cancel="$router.push({ name: 'student-observations' })"
      />
      <el-skeleton v-else :rows="10" animated />
    </el-card>
  </div>
</template>

<script>
import StudentObservationForm from './StudentObservationForm.vue';

export default {
  name: 'EditStudentObservation',
  components: {
    StudentObservationForm
  },
  data() {
    return {
      observation: null
    };
  },
  created() {
    this.fetchObservation();
  },
  methods: {
    async fetchObservation() {
      try {
        const response = await this.axios.get(`/api/student-observations/${this.$route.params.id}`);
        this.observation = response.data;
      } catch (error) {
        this.$message.error('Failed to fetch observation');
        console.error(error);
      }
    },
    onSaved() {
      this.$router.push({ name: 'student-observations' });
    }
  }
};
</script>

<style scoped>
.edit-student-observation {
  margin: 20px;
}
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>
