<template>
  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">Student Observation Checklist</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item">Students</li>
              <li class="breadcrumb-item active">Observation Checklist</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Student Observation Form</h3>
                <div class="card-tools">
                  <button class="btn btn-primary" @click="showForm = !showForm">
                    {{ showForm ? 'Hide Form' : 'New Observation' }}
                  </button>
                </div>
              </div>
              <div class="card-body" v-if="showForm">
                <form @submit.prevent="saveChecklist">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Student</label>
                        <select v-model="form.student_id" class="form-control" required>
                          <option value="">Select Student</option>
                          <option v-for="student in students" :key="student.id" :value="student.id">
                            {{ student.first_name }} {{ student.last_name }} ({{ student.reg_no }})
                          </option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Observation Date</label>
                        <input type="date" v-model="form.observation_date" class="form-control" required />
                      </div>
                    </div>
                  </div>

                  <!-- Academic Section -->
                  <div class="card mt-3">
                    <div class="card-header bg-info">
                      <h5 class="mb-0">Academic</h5>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-4">
                          <div class="form-group">
                            <label>English Reading</label>
                            <select v-model="form.english_reading" class="form-control">
                              <option value="1">Needs Improvement</option>
                              <option value="2">Developing</option>
                              <option value="3">Meeting Expectations</option>
                              <option value="4">Exceeding Expectations</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label>English Writing</label>
                            <select v-model="form.english_writing" class="form-control">
                              <option value="1">Needs Improvement</option>
                              <option value="2">Developing</option>
                              <option value="3">Meeting Expectations</option>
                              <option value="4">Exceeding Expectations</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label>English Spelling</label>
                            <select v-model="form.english_spelling" class="form-control">
                              <option value="1">Needs Improvement</option>
                              <option value="2">Developing</option>
                              <option value="3">Meeting Expectations</option>
                              <option value="4">Exceeding Expectations</option>
                            </select>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-4">
                          <div class="form-group">
                            <label>Urdu Reading</label>
                            <select v-model="form.urdu_reading" class="form-control">
                              <option value="1">Needs Improvement</option>
                              <option value="2">Developing</option>
                              <option value="3">Meeting Expectations</option>
                              <option value="4">Exceeding Expectations</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label>Urdu Writing</label>
                            <select v-model="form.urdu_writing" class="form-control">
                              <option value="1">Needs Improvement</option>
                              <option value="2">Developing</option>
                              <option value="3">Meeting Expectations</option>
                              <option value="4">Exceeding Expectations</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label>Urdu Spelling</label>
                            <select v-model="form.urdu_spelling" class="form-control">
                              <option value="1">Needs Improvement</option>
                              <option value="2">Developing</option>
                              <option value="3">Meeting Expectations</option>
                              <option value="4">Exceeding Expectations</option>
                            </select>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Math Numbers</label>
                            <select v-model="form.math_numbers" class="form-control">
                              <option value="1">Needs Improvement</option>
                              <option value="2">Developing</option>
                              <option value="3">Meeting Expectations</option>
                              <option value="4">Exceeding Expectations</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Math Concepts</label>
                            <select v-model="form.math_concepts" class="form-control">
                              <option value="1">Needs Improvement</option>
                              <option value="2">Developing</option>
                              <option value="3">Meeting Expectations</option>
                              <option value="4">Exceeding Expectations</option>
                            </select>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Social Behaviors Section -->
                  <div class="card mt-3">
                    <div class="card-header bg-success">
                      <h5 class="mb-0">Social Behaviors</h5>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-4">
                          <div class="form-group">
                            <label>Respectful toward Adults</label>
                            <select v-model="form.respect_adults" class="form-control">
                              <option value="1">Needs Improvement</option>
                              <option value="2">Developing</option>
                              <option value="3">Meeting Expectations</option>
                              <option value="4">Exceeding Expectations</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label>Respectful toward Classmates</label>
                            <select v-model="form.respect_classmates" class="form-control">
                              <option value="1">Needs Improvement</option>
                              <option value="2">Developing</option>
                              <option value="3">Meeting Expectations</option>
                              <option value="4">Exceeding Expectations</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label>Engages with Classmates</label>
                            <select v-model="form.engages_with_classmates" class="form-control">
                              <option value="1">Needs Improvement</option>
                              <option value="2">Developing</option>
                              <option value="3">Meeting Expectations</option>
                              <option value="4">Exceeding Expectations</option>
                            </select>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Follows Instructions</label>
                            <select v-model="form.follows_instructions" class="form-control">
                              <option value="1">Needs Improvement</option>
                              <option value="2">Developing</option>
                              <option value="3">Meeting Expectations</option>
                              <option value="4">Exceeding Expectations</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Conflict Resolution</label>
                            <select v-model="form.conflict_resolution" class="form-control">
                              <option value="1">Needs Improvement</option>
                              <option value="2">Developing</option>
                              <option value="3">Meeting Expectations</option>
                              <option value="4">Exceeding Expectations</option>
                            </select>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Health Section -->
                  <div class="card mt-3">
                    <div class="card-header bg-warning">
                      <h5 class="mb-0">Health</h5>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-3">
                          <div class="form-group">
                            <label>Uniform Clean</label>
                            <select v-model="form.uniform_clean" class="form-control">
                              <option value="1">Needs Improvement</option>
                              <option value="2">Developing</option>
                              <option value="3">Meeting Expectations</option>
                              <option value="4">Exceeding Expectations</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                            <label>Nails Clean</label>
                            <select v-model="form.nails_clean" class="form-control">
                              <option value="1">Needs Improvement</option>
                              <option value="2">Developing</option>
                              <option value="3">Meeting Expectations</option>
                              <option value="4">Exceeding Expectations</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                            <label>Shoes Polished</label>
                            <select v-model="form.shoes_polished" class="form-control">
                              <option value="1">Needs Improvement</option>
                              <option value="2">Developing</option>
                              <option value="3">Meeting Expectations</option>
                              <option value="4">Exceeding Expectations</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                            <label>Personal Hygiene</label>
                            <select v-model="form.personal_hygiene" class="form-control">
                              <option value="1">Needs Improvement</option>
                              <option value="2">Developing</option>
                              <option value="3">Meeting Expectations</option>
                              <option value="4">Exceeding Expectations</option>
                            </select>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- General Behavior Section -->
                  <div class="card mt-3">
                    <div class="card-header bg-danger">
                      <h5 class="mb-0">General Behavior</h5>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-4">
                          <div class="form-group">
                            <label>Good Manners</label>
                            <select v-model="form.good_manners" class="form-control">
                              <option value="1">Needs Improvement</option>
                              <option value="2">Developing</option>
                              <option value="3">Meeting Expectations</option>
                              <option value="4">Exceeding Expectations</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label>Concern for School</label>
                            <select v-model="form.school_concern" class="form-control">
                              <option value="1">Needs Improvement</option>
                              <option value="2">Developing</option>
                              <option value="3">Meeting Expectations</option>
                              <option value="4">Exceeding Expectations</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label>Activity Participation</label>
                            <select v-model="form.activity_participation" class="form-control">
                              <option value="1">Needs Improvement</option>
                              <option value="2">Developing</option>
                              <option value="3">Meeting Expectations</option>
                              <option value="4">Exceeding Expectations</option>
                            </select>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Punctuality</label>
                            <select v-model="form.punctuality" class="form-control">
                              <option value="1">Needs Improvement</option>
                              <option value="2">Developing</option>
                              <option value="3">Meeting Expectations</option>
                              <option value="4">Exceeding Expectations</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Task Completion</label>
                            <select v-model="form.task_completion" class="form-control">
                              <option value="1">Needs Improvement</option>
                              <option value="2">Developing</option>
                              <option value="3">Meeting Expectations</option>
                              <option value="4">Exceeding Expectations</option>
                            </select>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Additional Comments -->
                  <div class="form-group mt-3">
                    <label>Additional Comments</label>
                    <textarea v-model="form.additional_comments" class="form-control" rows="3"></textarea>
                  </div>

                  <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary" :disabled="loading">
                      <i class="fas fa-save mr-1"></i> {{ editing ? 'Update' : 'Save' }} Checklist
                    </button>
                    <button type="button" class="btn btn-secondary ml-2" @click="resetForm">
                      <i class="fas fa-undo mr-1"></i> Reset
                    </button>
                  </div>
                </form>
              </div>
            </div>

            <!-- List of Checklists -->
            <div class="card mt-4">
              <div class="card-header">
                <h3 class="card-title">Observation Checklists</h3>
                <div class="card-tools">
                  <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" v-model="search" class="form-control float-right" placeholder="Search">
                    <div class="input-group-append">
                      <button type="submit" class="btn btn-default" @click="getChecklists">
                        <i class="fas fa-search"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Student</th>
                      <th>Date</th>
                      <th>Academic Avg.</th>
                      <th>Social Avg.</th>
                      <th>Health Avg.</th>
                      <th>Behavior Avg.</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="checklist in checklists.data" :key="checklist.id">
                      <td>{{ checklist.id }}</td>
                      <td>{{ checklist.student ? `${checklist.student.first_name} ${checklist.student.last_name}` : 'N/A' }}</td>
                      <td>{{ formatDate(checklist.observation_date) }}</td>
                      <td>{{ calculateAverageAcademic(checklist) }}</td>
                      <td>{{ calculateAverageSocial(checklist) }}</td>
                      <td>{{ calculateAverageHealth(checklist) }}</td>
                      <td>{{ calculateAverageBehavior(checklist) }}</td>
                      <td>
                        <button class="btn btn-sm btn-info mr-1" @click="editChecklist(checklist)">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" @click="deleteChecklist(checklist.id)">
                          <i class="fas fa-trash"></i>
                        </button>
                      </td>
                    </tr>
                    <tr v-if="!checklists.data || checklists.data.length === 0">
                      <td colspan="8" class="text-center">No observation checklists found</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="card-footer clearfix" v-if="checklists.data && checklists.data.length > 0">
                <ul class="pagination pagination-sm m-0 float-right">
                  <li class="page-item" :class="{ disabled: checklists.current_page <= 1 }">
                    <a class="page-link" href="#" @click.prevent="changePage(checklists.current_page - 1)">«</a>
                  </li>
                  <li class="page-item" v-for="page in pagesArray" :key="page" :class="{ active: checklists.current_page === page }">
                    <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
                  </li>
                  <li class="page-item" :class="{ disabled: checklists.current_page >= checklists.last_page }">
                    <a class="page-link" href="#" @click.prevent="changePage(checklists.current_page + 1)">»</a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      showForm: false,
      editing: false,
      loading: false,
      search: '',
      form: this.getEmptyForm(),
      checklists: {
        data: []
      },
      students: [],
      currentPage: 1
    };
  },
  computed: {
    pagesArray() {
      if (!this.checklists.last_page) return [];
      let pages = [];
      for (let i = 1; i <= this.checklists.last_page; i++) {
        pages.push(i);
      }
      return pages;
    }
  },
  created() {
    this.getChecklists();
    this.getStudents();
  },
  methods: {
    getEmptyForm() {
      return {
        student_id: '',
        observation_date: new Date().toISOString().substr(0, 10),
        english_reading: null,
        english_writing: null,
        english_spelling: null,
        urdu_reading: null,
        urdu_writing: null,
        urdu_spelling: null,
        math_numbers: null,
        math_concepts: null,
        respect_adults: null,
        respect_classmates: null,
        engages_with_classmates: null,
        follows_instructions: null,
        conflict_resolution: null,
        uniform_clean: null,
        nails_clean: null,
        shoes_polished: null,
        personal_hygiene: null,
        good_manners: null,
        school_concern: null,
        activity_participation: null,
        punctuality: null,
        task_completion: null,
        additional_comments: ''
      };
    },
    getChecklists(page = 1) {
      this.loading = true;
      axios.get(`/api/student-observation-checklist?page=${page}`)
        .then(response => {
          this.checklists = response.data.data;
          this.loading = false;
        })
        .catch(error => {
          console.error('Error fetching checklists:', error);
          this.loading = false;
          this.$toastr.e('Failed to load checklists');
        });
    },
    getStudents() {
      axios.get('/api/student-observation-checklist/students')
        .then(response => {
          this.students = response.data.data;
        })
        .catch(error => {
          console.error('Error fetching students:', error);
          this.$toastr.e('Failed to load students');
        });
    },
    saveChecklist() {
      this.loading = true;
      const method = this.editing ? 'put' : 'post';
      const url = this.editing 
        ? `/api/student-observation-checklist/${this.form.id}`
        : '/api/student-observation-checklist';
      
      axios[method](url, this.form)
        .then(response => {
          this.loading = false;
          this.$toastr.s(response.data.message);
          this.resetForm();
          this.getChecklists();
          this.showForm = false;
        })
        .catch(error => {
          this.loading = false;
          this.$toastr.e('Failed to save checklist');
          console.error('Error saving checklist:', error);
        });
    },
    editChecklist(checklist) {
      this.form = { ...checklist };
      this.editing = true;
      this.showForm = true;
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    deleteChecklist(id) {
      if (!confirm('Are you sure you want to delete this observation checklist?')) {
        return;
      }
      
      axios.delete(`/api/student-observation-checklist/${id}`)
        .then(response => {
          this.$toastr.s(response.data.message);
          this.getChecklists();
        })
        .catch(error => {
          console.error('Error deleting checklist:', error);
          this.$toastr.e('Failed to delete checklist');
        });
    },
    resetForm() {
      this.form = this.getEmptyForm();
      this.editing = false;
    },
    formatDate(date) {
      if (!date) return 'N/A';
      return new Date(date).toLocaleDateString();
    },
    changePage(page) {
      if (page <= 0 || page > this.checklists.last_page) return;
      this.currentPage = page;
      this.getChecklists(page);
    },
    calculateAverageAcademic(checklist) {
      const scores = [
        checklist.english_reading,
        checklist.english_writing,
        checklist.english_spelling,
        checklist.urdu_reading,
        checklist.urdu_writing,
        checklist.urdu_spelling,
        checklist.math_numbers,
        checklist.math_concepts
      ].filter(score => score !== null && score !== undefined);
      
      if (scores.length === 0) return 'N/A';
      
      const sum = scores.reduce((total, score) => total + parseInt(score), 0);
      return (sum / scores.length).toFixed(1);
    },
    calculateAverageSocial(checklist) {
      const scores = [
        checklist.respect_adults,
        checklist.respect_classmates,
        checklist.engages_with_classmates,
        checklist.follows_instructions,
        checklist.conflict_resolution
      ].filter(score => score !== null && score !== undefined);
      
      if (scores.length === 0) return 'N/A';
      
      const sum = scores.reduce((total, score) => total + parseInt(score), 0);
      return (sum / scores.length).toFixed(1);
    },
    calculateAverageHealth(checklist) {
      const scores = [
        checklist.uniform_clean,
        checklist.nails_clean,
        checklist.shoes_polished,
        checklist.personal_hygiene
      ].filter(score => score !== null && score !== undefined);
      
      if (scores.length === 0) return 'N/A';
      
      const sum = scores.reduce((total, score) => total + parseInt(score), 0);
      return (sum / scores.length).toFixed(1);
    },
    calculateAverageBehavior(checklist) {
      const scores = [
        checklist.good_manners,
        checklist.school_concern,
        checklist.activity_participation,
        checklist.punctuality,
        checklist.task_completion
      ].filter(score => score !== null && score !== undefined);
      
      if (scores.length === 0) return 'N/A';
      
      const sum = scores.reduce((total, score) => total + parseInt(score), 0);
      return (sum / scores.length).toFixed(1);
    }
  }
};
</script>

<style scoped>
.card-header {
  color: white;
}
</style>
