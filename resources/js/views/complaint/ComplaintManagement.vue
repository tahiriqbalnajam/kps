<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input
        v-model="query.keyword"
        placeholder="Search complaints..."
        style="width: 200px;"
        class="filter-item"
        @keyup.enter.native="handleFilter"
      />
      <el-select v-model="query.status" placeholder="Status" clearable class="filter-item" style="width: 130px">
        <el-option v-for="item in statusOptions" :key="item.value" :label="item.label" :value="item.value" />
      </el-select>
      <el-button class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        Search
      </el-button>
      <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus" @click="handleCreate">
        Add Complaint
      </el-button>
    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column align="center" label="ID" width="80">
        <template #default="scope">
          <span>{{ scope.row.id }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Title" min-width="150">
        <template #default="scope">
          <span>{{ scope.row.title }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Student" min-width="120">
        <template #default="scope">
          <span>{{ scope.row.student?.name || 'N/A' }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Teacher" min-width="120">
        <template #default="scope">
          <span>{{ scope.row.teacher?.name || 'N/A' }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Status" width="100">
        <template #default="scope">
          <el-tag :type="getStatusType(scope.row.status)">
            {{ formatStatus(scope.row.status) }}
          </el-tag>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Priority" width="100">
        <template #default="scope">
          <el-tag :type="getPriorityType(scope.row.priority)">
            {{ scope.row.priority.toUpperCase() }}
          </el-tag>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Created" width="120">
        <template #default="scope">
          <span>{{ parseTimeMethod(scope.row.created_at, '{m}/{d}/{y}') }}</span>
        </template>
      </el-table-column>      
      <el-table-column align="center" label="Actions" width="200">
        <template #default="scope">
          <el-button type="primary" size="small" @click="handleEdit(scope.row)">
            Edit
          </el-button>
          <el-button type="info" size="small"  @click="handleView(scope.row)">
            View
          </el-button>
          <el-button type="danger" size="small"  @click="handleDelete(scope.row)">
            Delete
          </el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total > 0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />

    <!-- Add/Edit Dialog -->
    <el-dialog :title="dialogTitle" v-model="dialogVisible" width="800px">
      <el-form ref="complaintForm" :model="currentComplaint" :rules="rules" label-width="120px">
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Student" prop="student_id">
              <el-select
                v-model="currentComplaint.student_id"
                filterable
                remote
                placeholder="Select Student"
                :remote-method="searchStudents"
                :loading="studentsLoading"
                style="width: 100%"
              >
                <el-option
                  v-for="student in students"
                  :key="student.id"
                  :label="`${student.name} (${student.student_id})`"
                  :value="student.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Teacher" prop="teacher_id">
              <el-select
                v-model="currentComplaint.teacher_id"
                filterable
                remote
                placeholder="Select Teacher (Optional)"
                :remote-method="searchTeachers"
                :loading="teachersLoading"
                clearable
                style="width: 100%"
              >
                <el-option
                  v-for="teacher in teachers"
                  :key="teacher.id"
                  :label="teacher.name"
                  :value="teacher.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Type" prop="complaint_type">
              <el-select v-model="currentComplaint.complaint_type" placeholder="Select Type" style="width: 100%">
                <el-option label="Academic" value="academic" />
                <el-option label="Behavioral" value="behavioral" />
                <el-option label="Attendance" value="attendance" />
                <el-option label="Fees" value="fees" />
                <el-option label="Infrastructure" value="infrastructure" />
                <el-option label="Other" value="other" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Priority" prop="priority">
              <el-select v-model="currentComplaint.priority" placeholder="Select Priority" style="width: 100%">
                <el-option label="Low" value="low" />
                <el-option label="Medium" value="medium" />
                <el-option label="High" value="high" />
                <el-option label="Urgent" value="urgent" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="Title" prop="title">
          <el-input v-model="currentComplaint.title" placeholder="Enter complaint title" />
        </el-form-item>

        <el-form-item label="Description" prop="description">
          <el-input
            v-model="currentComplaint.description"
            type="textarea"
            :rows="4"
            placeholder="Enter complaint description"
          />
        </el-form-item>

        <el-form-item v-if="currentComplaint.id" label="Status" prop="status">
          <el-select v-model="currentComplaint.status" placeholder="Select Status" style="width: 100%">
            <el-option label="Pending" value="pending" />
            <el-option label="In Progress" value="in_progress" />
            <el-option label="Resolved" value="resolved" />
            <el-option label="Closed" value="closed" />
          </el-select>
        </el-form-item>

        <el-form-item v-if="currentComplaint.status === 'resolved'" label="Resolution Notes">
          <el-input
            v-model="currentComplaint.resolution_notes"
            type="textarea"
            :rows="3"
            placeholder="Enter resolution notes"
          />
        </el-form-item>
      </el-form>

      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false">Cancel</el-button>
        <el-button type="primary" :loading="submitting" @click="submitForm">
          {{ currentComplaint.id ? 'Update' : 'Create' }}
        </el-button>
      </div>
    </el-dialog>

    <!-- View Dialog -->
    <el-dialog title="Complaint Details" v-model="viewDialogVisible" width="600px">
      <div v-if="viewComplaint">
        <el-descriptions :column="2" border>
          <el-descriptions-item label="Title">{{ viewComplaint.title }}</el-descriptions-item>
          <el-descriptions-item label="Status">
            <el-tag :type="getStatusType(viewComplaint.status)">
              {{ formatStatus(viewComplaint.status) }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="Priority">
            <el-tag :type="getPriorityType(viewComplaint.priority)">
              {{ viewComplaint.priority.toUpperCase() }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="Type">{{ viewComplaint.complaint_type }}</el-descriptions-item>
          <el-descriptions-item label="Student">{{ viewComplaint.student?.name || 'N/A' }}</el-descriptions-item>
          <el-descriptions-item label="Teacher">{{ viewComplaint.teacher?.name || 'N/A' }}</el-descriptions-item>
          <el-descriptions-item label="Created By">{{ viewComplaint.created_by?.name || 'N/A' }}</el-descriptions-item>
          <el-descriptions-item label="Created At">{{ parseTimeMethod(viewComplaint.created_at, '{y}-{m}-{d} {h}:{i}') }}</el-descriptions-item>
        </el-descriptions>
        
        <div style="margin-top: 20px;">
          <h4>Description:</h4>
          <p>{{ viewComplaint.description }}</p>
        </div>

        <div v-if="viewComplaint.resolution_notes" style="margin-top: 20px;">
          <h4>Resolution Notes:</h4>
          <p>{{ viewComplaint.resolution_notes }}</p>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import ComplaintResource from '@/api/complaint';
import Pagination from '@/components/Pagination/index.vue';
import { parseTime } from '@/utils'; // Make sure this import path is correct

const complaintResource = new ComplaintResource();

export default {
  name: 'ComplaintManagement',
  components: { Pagination },
  data() {
    return {
      list: [],
      total: 0,
      loading: false,
      submitting: false,
      studentsLoading: false,
      teachersLoading: false,
      dialogVisible: false,
      viewDialogVisible: false,
      students: [],
      teachers: [],
      viewComplaint: null,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        status: '',
      },
      currentComplaint: {
        student_id: '',
        teacher_id: '',
        complaint_type: '',
        title: '',
        description: '',
        priority: 'medium',
        status: 'pending',
        resolution_notes: '',
      },
      statusOptions: [
        { label: 'Pending', value: 'pending' },
        { label: 'In Progress', value: 'in_progress' },
        { label: 'Resolved', value: 'resolved' },
        { label: 'Closed', value: 'closed' },
      ],
      rules: {
        student_id: [{ required: true, message: 'Please select a student', trigger: 'change' }],
        complaint_type: [{ required: true, message: 'Please select complaint type', trigger: 'change' }],
        title: [{ required: true, message: 'Please enter title', trigger: 'blur' }],
        description: [{ required: true, message: 'Please enter description', trigger: 'blur' }],
        priority: [{ required: true, message: 'Please select priority', trigger: 'change' }],
      },
    };
  },
  computed: {
    dialogTitle() {
      return this.currentComplaint.id ? 'Edit Complaint' : 'Add Complaint';
    },
  },
  created() {
    this.getList();
  },
  methods: {
    async getList() {
      this.loading = true;
      try {
        const response = await complaintResource.list(this.query);
        this.list = response.data.complaints.data;
        this.total = response.data.complaints.total;
      } catch (error) {
        this.$message.error('Failed to fetch complaints');
      } finally {
        this.loading = false;
      }
    },

    handleFilter() {
      this.query.page = 1;
      this.getList();
    },

    handleCreate() {
      this.resetForm();
      this.dialogVisible = true;
    },

    handleEdit(row) {
      this.currentComplaint = { ...row };
      this.dialogVisible = true;
    },

    handleView(row) {
      this.viewComplaint = row;
      this.viewDialogVisible = true;
    },

    async handleDelete(row) {
      try {
        await this.$confirm('Are you sure you want to delete this complaint?', 'Warning', {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning',
        });
        
        await complaintResource.destroy(row.id);
        this.$message.success('Complaint deleted successfully');
        this.getList();
      } catch (error) {
        if (error !== 'cancel') {
          this.$message.error('Failed to delete complaint');
        }
      }
    },

    async submitForm() {
      try {
        await this.$refs.complaintForm.validate();
        this.submitting = true;

        if (this.currentComplaint.id) {
          await complaintResource.update(this.currentComplaint.id, this.currentComplaint);
          this.$message.success('Complaint updated successfully');
        } else {
          await complaintResource.store(this.currentComplaint);
          this.$message.success('Complaint created successfully');
        }

        this.dialogVisible = false;
        this.getList();
      } catch (error) {
        this.$message.error('Failed to save complaint');
      } finally {
        this.submitting = false;
      }
    },

    resetForm() {
      this.currentComplaint = {
        student_id: '',
        teacher_id: '',
        complaint_type: '',
        title: '',
        description: '',
        priority: 'medium',
        status: 'pending',
        resolution_notes: '',
      };
      this.students = [];
      this.teachers = [];
      if (this.$refs.complaintForm) {
        this.$refs.complaintForm.clearValidate();
      }
    },

    async searchStudents(query) {
      if (query !== '') {
        this.studentsLoading = true;
        try {
          const response = await complaintResource.getStudents({ keyword: query });
          this.students = response.data.students;
        } catch (error) {
          this.$message.error('Failed to fetch students');
        } finally {
          this.studentsLoading = false;
        }
      } else {
        this.students = [];
      }
    },

    async searchTeachers(query) {
      if (query !== '') {
        this.teachersLoading = true;
        try {
          const response = await complaintResource.getTeachers({ keyword: query });
          this.teachers = response.data.teachers;
        } catch (error) {
          this.$message.error('Failed to fetch teachers');
        } finally {
          this.teachersLoading = false;
        }
      } else {
        this.teachers = [];
      }
    },

    getStatusType(status) {
      const types = {
        pending: 'warning',
        in_progress: 'info',
        resolved: 'success',
        closed: '',
      };
      return types[status] || '';
    },

    getPriorityType(priority) {
      const types = {
        low: 'info',
        medium: 'warning',
        high: 'danger',
        urgent: 'danger',
      };
      return types[priority] || '';
    },
    formatStatus(status) {
      return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
    },
    
    parseTimeMethod(time, format) {
      return parseTime(time, format);
    },
  }
};
</script>

<style scoped>
.filter-container {
  padding: 20px 0;
}

.filter-item {
  margin-right: 10px;
}
</style>
