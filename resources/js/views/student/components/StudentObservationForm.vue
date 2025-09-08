<template>
  <div class="student-observation-form">
    <el-form ref="form" :model="formData" :rules="rules" @submit.prevent="submitForm" label-position="top">
      <el-card class="mb-4">
        <template #header>
          <div class="card-header">
            <span>Student Information</span>
          </div>
        </template>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Select Student" prop="student_id">
              <el-select
                v-model="formData.student_id"
                filterable
                remote
                :remote-method="handleStudentSearch"
                :loading="studentsLoading"
                placeholder="Search student by name"
                :disabled="editMode"
                style="width: 100%"
              >
                <el-option
                  v-for="student in students"
                  :key="student.id"
                  :label="student.name"
                  :value="student.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Observation Date" prop="observation_date">
              <el-date-picker
                v-model="formData.observation_date"
                type="date"
                placeholder="Select Date"
                style="width: 100%"
              />
            </el-form-item>
          </el-col>
        </el-row>
      </el-card>

      <!-- Academic Section -->
      <el-card class="mb-4">
        <template #header>
          <div class="card-header">
            <span>Academic</span>
          </div>
        </template>
        <el-row :gutter="20">
          <el-col :span="8">
            <el-form-item label="English Reading">
              <div class="rating-container">
                <el-rate
                  v-model="ratingValues.academic.english_reading"
                  :colors="ratingColors"
                  :texts="ratingTexts"
                  show-text
                  @change="updateObservation('academic', 'english_reading')"
                />
              </div>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="English Writing">
              <div class="rating-container">
                <el-rate
                  v-model="ratingValues.academic.english_writing"
                  :colors="ratingColors"
                  :texts="ratingTexts"
                  show-text
                  @change="updateObservation('academic', 'english_writing')"
                />
              </div>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="English Spelling">
              <div class="rating-container">
                <el-rate
                  v-model="ratingValues.academic.english_spelling"
                  :colors="ratingColors"
                  :texts="ratingTexts"
                  show-text
                  @change="updateObservation('academic', 'english_spelling')"
                />
              </div>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="8">
            <el-form-item label="Urdu Reading">
              <div class="rating-container">
                <el-rate
                  v-model="ratingValues.academic.urdu_reading"
                  :colors="ratingColors"
                  :texts="ratingTexts"
                  show-text
                  @change="updateObservation('academic', 'urdu_reading')"
                />
              </div>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="Urdu Writing">
              <div class="rating-container">
                <el-rate
                  v-model="ratingValues.academic.urdu_writing"
                  :colors="ratingColors"
                  :texts="ratingTexts"
                  show-text
                  @change="updateObservation('academic', 'urdu_writing')"
                />
              </div>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="Urdu Tor Jor">
              <div class="rating-container">
                <el-rate
                  v-model="ratingValues.academic.urdu_tor_jor"
                  :colors="ratingColors"
                  :texts="ratingTexts"
                  show-text
                  @change="updateObservation('academic', 'urdu_tor_jor')"
                />
              </div>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="8">
            <el-form-item label="Mathematics/Tables">
              <div class="rating-container">
                <el-rate
                  v-model="ratingValues.academic.mathematics"
                  :colors="ratingColors"
                  :texts="ratingTexts"
                  show-text
                  @change="updateObservation('academic', 'mathematics')"
                />
              </div>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="Science">
              <div class="rating-container">
                <el-rate
                  v-model="ratingValues.academic.science"
                  :colors="ratingColors"
                  :texts="ratingTexts"
                  show-text
                  @change="updateObservation('academic', 'science')"
                />
              </div>
            </el-form-item>
          </el-col>
        </el-row>
      </el-card>

      <!-- Social Section -->
      <el-card class="mb-4">
        <template #header>
          <div class="card-header">
            <span>Social</span>
          </div>
        </template>
        <el-row :gutter="20">
          <el-col :span="8">
            <el-form-item label="Respectful toward Adults">
              <div class="rating-container">
                <el-rate
                  v-model="ratingValues.social.respect_adults"
                  :colors="ratingColors"
                  :texts="ratingTexts"
                  show-text
                  @change="updateObservation('social', 'respect_adults')"
                />
              </div>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="Respectful toward Classmates">
              <div class="rating-container">
                <el-rate
                  v-model="ratingValues.social.respect_classmates"
                  :colors="ratingColors"
                  :texts="ratingTexts"
                  show-text
                  @change="updateObservation('social', 'respect_classmates')"
                />
              </div>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="Shares Things with Classmates">
              <div class="rating-container">
                <el-rate
                  v-model="ratingValues.social.sharing"
                  :colors="ratingColors"
                  :texts="ratingTexts"
                  show-text
                  @change="updateObservation('social', 'sharing')"
                />
              </div>
            </el-form-item>
          </el-col>
        </el-row>
      </el-card>

      <!-- Health Section -->
      <el-card class="mb-4">
        <template #header>
          <div class="card-header">
            <span>Health</span>
          </div>
        </template>
        <el-row :gutter="20">
          <el-col :span="8">
            <el-form-item label="Neat Clean Uniform">
              <div class="rating-container">
                <el-rate
                  v-model="ratingValues.health.clean_uniform"
                  :colors="ratingColors"
                  :texts="ratingTexts"
                  show-text
                  @change="updateObservation('health', 'clean_uniform')"
                />
              </div>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="Clean Nails">
              <div class="rating-container">
                <el-rate
                  v-model="ratingValues.health.clean_nails"
                  :colors="ratingColors"
                  :texts="ratingTexts"
                  show-text
                  @change="updateObservation('health', 'clean_nails')"
                />
              </div>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="Shoes Polish">
              <div class="rating-container">
                <el-rate
                  v-model="ratingValues.health.shoes_polish"
                  :colors="ratingColors"
                  :texts="ratingTexts"
                  show-text
                  @change="updateObservation('health', 'shoes_polish')"
                />
              </div>
            </el-form-item>
          </el-col>
        </el-row>
      </el-card>

      <!-- General Section -->
      <el-card class="mb-4">
        <template #header>
          <div class="card-header">
            <span>General</span>
          </div>
        </template>
        <el-row :gutter="20">
          <el-col :span="8">
            <el-form-item label="Good Ethics">
              <div class="rating-container">
                <el-rate
                  v-model="ratingValues.general.ethics"
                  :colors="ratingColors"
                  :texts="ratingTexts"
                  show-text
                  @change="updateObservation('general', 'ethics')"
                />
              </div>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="Good Manners">
              <div class="rating-container">
                <el-rate
                  v-model="ratingValues.general.manners"
                  :colors="ratingColors"
                  :texts="ratingTexts"
                  show-text
                  @change="updateObservation('general', 'manners')"
                />
              </div>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="Concern for School">
              <div class="rating-container">
                <el-rate
                  v-model="ratingValues.general.school_concern"
                  :colors="ratingColors"
                  :texts="ratingTexts"
                  show-text
                  @change="updateObservation('general', 'school_concern')"
                />
              </div>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="8">
            <el-form-item label="School Activities (Speech, Games, etc.)">
              <div class="rating-container">
                <el-rate
                  v-model="ratingValues.general.school_activities"
                  :colors="ratingColors"
                  :texts="ratingTexts"
                  show-text
                  @change="updateObservation('general', 'school_activities')"
                />
              </div>
            </el-form-item>
          </el-col>
        </el-row>
      </el-card>

      <!-- Comments Section -->
      <el-card class="mb-4">
        <template #header>
          <div class="card-header">
            <span>Additional Comments</span>
          </div>
        </template>
        <el-form-item label="Comments">
          <el-input
            v-model="formData.comments"
            type="textarea"
            :rows="3"
            placeholder="Enter additional comments"
          />
        </el-form-item>
      </el-card>

      <div class="form-actions">
        <el-button @click="cancel">Cancel</el-button>
        <el-button type="primary" @click="submitForm" :loading="loading" :disabled="!isFormValid">
          {{ editMode ? 'Update' : 'Save' }} Observation
        </el-button>
      </div>
    </el-form>
  </div>
</template>

<script>
import Resource from '@/api/resource';
const studentRes = new Resource('students');
const observationRes = new Resource('student-observations');
export default {
  name: 'StudentObservationForm',
  props: {
    editMode: {
      type: Boolean,
      default: false
    },
    observationData: {
      type: Object,
      default: () => null
    }
  },
  data() {
    return {
      isFormValid: false,
      loading: false,
      studentsLoading: false,
      students: [],
      studentQuery: {
        filter: {},
        limit: 20
      },
      ratingOptions: [
        { text: 'Poor', value: 'poor' },
        { text: 'Needs Improvement', value: 'needs_improvement' },
        { text: 'Satisfactory', value: 'satisfactory' },
        { text: 'Good', value: 'good' },
        { text: 'Excellent', value: 'excellent' }
      ],
      // Colors for ratings: gray -> yellow -> green
      ratingColors: ['#C6D1DE', '#C6D1DE', '#F7BA2A', '#F7BA2A', '#67C23A'],
      ratingTexts: ['Poor', 'Needs Improvement', 'Satisfactory', 'Good', 'Excellent'],
      
      // Rating values for the star components (1-5)
      ratingValues: {
        academic: {
          english_reading: 0,
          english_writing: 0,
          english_spelling: 0,
          urdu_reading: 0,
          urdu_writing: 0,
          urdu_tor_jor: 0,
          mathematics: 0,
          science: 0
        },
        social: {
          respect_adults: 0,
          respect_classmates: 0,
          sharing: 0
        },
        health: {
          clean_uniform: 0,
          clean_nails: 0,
          shoes_polish: 0
        },
        general: {
          ethics: 0,
          manners: 0,
          school_concern: 0,
          school_activities: 0
        }
      },
      
      formData: {
        student_id: null,
        observation_date: new Date(),
        comments: ''
      },
      rules: {
        student_id: [
          { required: true, message: 'Please select a student', trigger: 'change' }
        ],
        observation_date: [
          { required: true, message: 'Please select a date', trigger: 'change' }
        ]
      },
      observations: {
        academic: {
          english_reading: null,
          english_writing: null,
          english_spelling: null,
          urdu_reading: null,
          urdu_writing: null,
          urdu_tor_jor: null,
          mathematics: null,
          science: null
        },
        social: {
          respect_adults: null,
          respect_classmates: null,
          sharing: null
        },
        health: {
          clean_uniform: null,
          clean_nails: null,
          shoes_polish: null
        },
        general: {
          ethics: null,
          manners: null,
          school_concern: null,
          school_activities: null
        }
      }
    };
  },
  created() {
    // Initial fetch of students with empty query
    this.fetchStudents();
    if (this.editMode && this.observationData) {
      this.populateForm();
    }
  },
  methods: {
    // Maps rating value (1-5) to the corresponding text value in ratingOptions
    getRatingValue(ratingNumber) {
      if (!ratingNumber) return null;
      return this.ratingOptions[ratingNumber - 1]?.value || null;
    },
    
    // Maps text value to rating number (1-5)
    getRatingNumber(value) {
      if (!value) return 0;
      const index = this.ratingOptions.findIndex(option => option.value === value);
      return index !== -1 ? index + 1 : 0;
    },
    
    // Updates the observation value when rating changes
    updateObservation(category, parameter) {
      const ratingNumber = this.ratingValues[category][parameter];
      this.observations[category][parameter] = this.getRatingValue(ratingNumber);
    },
    
    handleStudentSearch(query) {
      if (query) {
        this.studentQuery.filter['name'] = query;
        this.fetchStudents();
      }
    },
    
    async fetchStudents() {
      try {
        this.studentsLoading = true;
        const response = await studentRes.list(this.studentQuery);
        this.students = response.data;
      } catch (error) {
        this.$message.error('Failed to fetch students');
        console.error(error);
      } finally {
        this.studentsLoading = false;
      }
    },
    
    populateForm() {
      // Set basic info
      this.formData.student_id = this.observationData.student_id;
      
      // Fetch the student details if it's edit mode to display the student name
      if (this.editMode && this.observationData.student_id) {
        this.getStudentDetails(this.observationData.student_id);
      }
      
      this.formData.observation_date = this.observationData.observation_date;
      this.formData.comments = this.observationData.comments;

      // Populate observation details
      this.observationData.details.forEach(detail => {
        const { category, parameter, value } = detail;
        if (this.observations[category] && this.observations[category].hasOwnProperty(parameter)) {
          this.observations[category][parameter] = value;
          
          // Set the corresponding rating value for the star component
          if (this.ratingValues[category] && this.ratingValues[category].hasOwnProperty(parameter)) {
            this.ratingValues[category][parameter] = this.getRatingNumber(value);
          }
        }
      });
    },
    
    async getStudentDetails(studentId) {
      try {
        this.studentsLoading = true;
        const response = await studentRes.get(studentId);
        // Add the student to the options so it's displayed in the select
        if (response.data) {
          const studentExists = this.students.some(s => s.id === response.data.id);
          if (!studentExists) {
            this.students.push(response.data);
          }
        }
      } catch (error) {
        console.error('Failed to fetch student details', error);
      } finally {
        this.studentsLoading = false;
      }
    },
    
    async submitForm() {
      this.$refs.form.validate(async valid => {
        if (!valid) return;
        
        this.loading = true;
        
        // Prepare details array
        const details = [];
        
        // Process all categories
        for (const category in this.observations) {
          for (const parameter in this.observations[category]) {
            if (this.observations[category][parameter]) {
              details.push({
                category,
                parameter,
                value: this.observations[category][parameter]
              });
            }
          }
        }

        // Prepare payload
        const payload = {
          ...this.formData,
          details
        };

        try {
          let response;
          if (this.editMode) {
            response = await observationRes.update(this.observationData.id, payload);
            this.$message.success('Observation updated successfully');
          } else {
            response = await observationRes.store(payload);
            this.$message.success('Observation saved successfully');
          }
          
          this.$emit('saved', response.data);
        } catch (error) {
          this.$message.error('Failed to save observation');
          console.error(error);
        } finally {
          this.loading = false;
        }
      });
    },
    
    cancel() {
      this.$emit('cancel');
    }
  }
};
</script>

<style scoped>
.student-observation-form {
  max-width: 1200px;
  margin: 0 auto;
}
.mb-4 {
  margin-bottom: 1.5rem;
}
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: bold;
}
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 1rem;
}
.rating-container {
  padding: 8px 0;
}
</style>
