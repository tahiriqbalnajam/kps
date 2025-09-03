<template>
  <div class="create-student-observation">
    <el-card>
      <template #header>
        <div class="card-header">
          <h2>Create Student Observation</h2>
          <el-button 
            icon="Close" 
            circle 
            @click="$router.push({ name: 'student-observations' })"
          />
        </div>
      </template>

      <student-observation-form 
        @saved="onSaved" 
        @cancel="$router.push({ name: 'student-observations' })"
      />
    </el-card>
  </div>
</template>

<script>
import StudentObservationForm from './StudentObservationForm.vue';

export default {
  name: 'CreateStudentObservation',
  components: {
    StudentObservationForm
  },
  methods: {
    onSaved() {
      this.$router.push({ name: 'student-observations' });
    }
  }
};
</script>

<style scoped>
.create-student-observation {
  margin: 20px;
}
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>
