<template>
  <div class="app-container">
    <div class="filter-container">
      <el-card class="box-card">
        <head-controls>
          <el-row justify="space-between">
            <el-col :span="12">
              <el-row :gutter="20">
                <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8">
                  <el-date-picker v-model="query.filter.holiday_date" type="month" size="normal" placeholder="Pick Month" value-format="YYYY-MM-DD"  format="DD/MM/YYYY">
                  </el-date-picker>
                </el-col>
                <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8">
                  <el-input v-model="query.filter.description" placeholder="Description" class="filter-item" v-on:input="debounceInput" />
                </el-col>
                <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8">
                  <el-button class="filter-item" type="primary" :icon="Search" @click="fetchHolidays">
                    {{ $t('table.search') }}
                  </el-button>
                </el-col>
              </el-row>
            </el-col>
            <el-col :span="12">
              <el-row :gutter="20" justify="end">
                <el-col :span="3">
                  <el-tooltip content="Add Holiday" placement="top">
                    <el-button class="filter-item" style="margin-left: 10px;" type="info" :icon="el-icon-plus" @click="openAddNew()">
                      <el-icon :size="15"><Plus /></el-icon>
                    </el-button>
                  </el-tooltip>
                </el-col>
                <el-col :span="2">
                  <el-tooltip content="Download Excel" placement="top">
                    <el-button class="filter-item" :loading="downloadLoading" type="danger" :icon="Search" @click="handleDownload">
                      <el-icon><Download /></el-icon>
                    </el-button>
                  </el-tooltip>
                </el-col>
              </el-row>
            </el-col>
          </el-row>
        </head-controls>
      </el-card>
    </div>
    <el-table :data="holidays" style="width: 100%">
      <el-table-column label="Description" prop="description" />
      <el-table-column label="Date" prop="holiday_date" />
      <el-table-column align="right">
        <template slot="header" #header="scope">
          <el-input ref="search" v-model="query.keyword" size="mini" placeholder="Type to search" v-on:input="debounceInput" />
        </template>
        <template #default="scope">
          <el-button circle size="mini" @click="editHoliday(scope.row)">
            <el-icon :size="15"><Edit /></el-icon>
          </el-button>
          <el-button circle size="mini" type="danger" @click="deleteHoliday(scope.row.id)">
            <el-icon :size="15"><Delete /></el-icon>
          </el-button>
        </template>
      </el-table-column>
    </el-table>
    <div class="demo-pagination-block">
      <el-pagination
        v-show="total > 0"
        v-model:current-page="query.page"
        v-model:page-size="query.limit"
        :page-sizes="[10, 15, 20, 30, 50, 100]"
        :small="small"
        :disabled="disabled"
        background="white"
        layout="total, sizes, prev, pager, next, jumper"
        :total="total"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      />
    </div>
    <el-drawer
      title="Edit Record"
      :modelValue="editnow"
      direction="rtl"
      custom-class="demo-drawer"
      ref="drawer"
      size="90%"
      @close="fetchHolidays()"
    >
      <div class="demo-drawer__content">
        <el-form :model="holiday" :rules="rules" ref="holidayForm">
          <el-row :gutter="20">
            <el-col :span="8">
              <el-form-item label="Date" :label-width="formLabelWidth" prop="holiday_date">
                <el-date-picker v-model="holiday.holiday_date" type="date" placeholder="Pick a date" format="DD/MM/YYYY"  value-format="YYYY-MM-DD"/>
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="Description" :label-width="formLabelWidth" prop="description">
                <el-input v-model="holiday.description" autocomplete="off" />
              </el-form-item>
            </el-col>
          </el-row>
        </el-form>
      </div>
      <template #footer>
        <div style="flex: auto">
          <el-button @click="editnow = false">Cancel</el-button>
          <el-button type="primary" @click="handleSubmit" :loading="loading">{{ loading ? 'Submitting ...' : 'Submit' }}</el-button>
        </div>
      </template>
    </el-drawer>
  </div>
</template>
<script>
import { ElMessage } from 'element-plus';
import { Plus, Edit, Delete, Download, Search } from '@element-plus/icons-vue';
import Resource from '@/api/resource';
const resourcePro = new Resource('holidays');

export default {
  name: 'Holidays',
  components: {
    Plus,
    Edit,
    Delete,
    Download,
    Search,
  },
  data() {
    return {
      holidays: [], // List of holidays
      holiday: { holiday_date: '', description: '' }, // Single holiday object for add/edit
      isEditing: false,
      editnow: false,
      editId: null,
      query: {
        page: 1,
        limit: 15,
        holiday_date: '',
        description: '',
        filter:{}
      },
      total: 0,
      loading: false,
      downloadLoading: false,
      formLabelWidth: '120px',
    };
  },
  methods: {
    async fetchHolidays() {
      //this.query.filter['holiday_date'] =  this.query.holiday_date;
      //this.query.filter['description'] =  this.query.description;
      const { data } = await resourcePro.list(this.query);
      this.holidays = data.holidays.data;
      this.total = data.holidays.total;
    },
    async handleSubmit() {
      this.loading = true;
      try {
        if (this.isEditing) {
          await resourcePro.update(this.editId, this.holiday);
          ElMessage.success('Holiday updated successfully');
        } else {
          await resourcePro.store(this.holiday);
          ElMessage.success('Holiday added successfully');
        }
        this.editnow = false;
        this.resetForm();
        await this.fetchHolidays();
      } catch (error) {
        ElMessage.error('Failed to save holiday');
      } finally {
        this.loading = false;
      }
    },
    editHoliday(holiday) {
      this.isEditing = true;
      this.editId = holiday.id;
      this.holiday = { ...holiday };
      this.editnow = true;
    },
    async deleteHoliday(id) {
      try {
        this.$confirm('Are you sure you want to delete this holiday?', 'Warning', {
          confirmButtonText: 'Yes',
          cancelButtonText: 'No',
          type: 'warning',
        }).then(async () => {
          await resourcePro.destroy(id);
          ElMessage.success('Holiday deleted successfully');
          this.fetchHolidays();
        }).catch(() => {
          ElMessage.info('Delete canceled');
        });
      } catch (error) {
        ElMessage.error('Failed to delete holiday');
      }
    },
    resetForm() {
      this.holiday = { holiday_date: '', description: '' };
      this.isEditing = false;
      this.editId = null;
    },
    openAddNew() {
      this.resetForm();
      this.editnow = true;
    },
    handleSizeChange(size) {
      this.query.limit = size;
      this.fetchHolidays();
    },
    handleCurrentChange(page) {
      this.query.page = page;
      this.fetchHolidays();
    },
  },
  mounted() {
    this.fetchHolidays();
  },
};
</script>
<style scoped>
.camera-selection {
  margin-bottom: 10px;
  text-align: center;
}

.qrcode-container {
  position: relative;
}

.circle-overlay {
  position: absolute;
  top: 20%; /* Move the circle closer to the top */
  left: 50%;
  width: 250px; /* Make the circle bigger */
  height: 250px; /* Make the circle bigger */
  margin-top: -125px; /* Half of the height */
  margin-left: -125px; /* Half of the width */
  border: 2px solid red;
  border-radius: 50%;
  background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
  pointer-events: none; /* Allow clicks to pass through */
}
</style>