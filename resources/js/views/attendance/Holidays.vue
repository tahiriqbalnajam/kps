<template>
    <div class="app-container">
        <div class="filter-container">
            <el-card class="box-card">
                <head-controls>
                <el-row justify="space-between">
                    <el-col :span="12">
                    <el-row :gutter="20">
                        <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl ="8">
                            <el-date-picker v-model="query.date" type="month" size="normal" placeholder="Pick Month">
                        </el-date-picker>
                        
                        </el-col>
                        <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl ="8">
                        <el-input v-model="query.keyword" placeholder="Description" class="filter-item" v-on:input="debounceInput" />
                        </el-col>
                        <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl ="8">
                        <el-button  class="filter-item" type="primary" :icon="Search"  @click="fetchHolidays">
                            {{ $t('table.search') }}
                        </el-button>
                        </el-col>
                    </el-row>
                    </el-col>
                    <el-col :span="12">
                    <el-row :gutter="20" justify="end">
                        <el-col :span="3">
                        <el-tooltip content="Add Teacher" placement="top">
                            <el-button class="filter-item" style="margin-left: 10px;" type="info" :icon="el-icon-plus" @click="openAddNew()">
                                <el-icon :size="15"><Plus /></el-icon>
                            </el-button>
                        </el-tooltip>
                        </el-col>
                        <el-col :span="2">
                        <el-tooltip content="Teacher Excel" placement="top">
                            <el-button class="filter-item" :loading="downloadLoading"  type="danger" :icon="Search"  @click="handleDownload">
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
    </div>
    <el-table
      :data="holidays"
      style="width: 100%"
    >
        <el-table-column label="Description" prop="description" />
        <el-table-column label="Date" prop="holiday_date" />
        <el-table-column align="right">
            <template slot="header" #header="scope">
            <el-input ref="search" v-model="query.keyword" size="mini" placeholder="Type to search" v-on:input="debounceInput" />
            </template>
            <template #default="scope">
            <el-button
                circle
                size="mini"
                @click="handleEdit(scope.row.holiday_date, scope.row.description)"
            >
                <el-icon :size="15"><Edit /></el-icon>
            </el-button>
            <el-button
                circle
                size="mini"
                type="danger"
                @click="handleDelete(scope.row.holiday_date, scope.row.description)"
            ><el-icon :size="15"><Delete /></el-icon></el-button>
        </template>
      </el-table-column>
    </el-table>
    <div class="demo-pagination-block">
      <el-pagination
        v-show="total>0"
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
      @close="updatelist()"
    >
      <div class="demo-drawer__content">
        <el-form :model="holidy" :rules="rules" ref="teacher">
          <el-row :gutter="20">
            <el-col :span="8">
              <el-form-item label="Date" :label-width="formLabelWidth" prop="name">
                <el-input v-model="holidy.holidy_date" autocomplete="off" />
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="Description" :label-width="formLabelWidth" prop="phone">
                <el-input v-model="holidy.description" autocomplete="off" />
              </el-form-item>
            </el-col>
          </el-row>
        </el-form>
      </div>
      <template #footer>
          <div style="flex: auto">
            <el-button @click="editnow = false">Cancel</el-button>
            <el-button type="primary" @click="onSubmit('teacher')" :loading="loading">{{ loading ? 'Submitting ...' : 'Submit' }}</el-button>
          </div>
        </template>
    </el-drawer>
</template>
<script>
import Pagination from '@/components/Pagination/index.vue';
import Resource from '@/api/resource';
const resourcePro = new Resource('holidays');
export default {
    name: 'Holidays',
    components: { Pagination},
    data() {
        return {
        holidays: [], // List of holidays
        holiday: { date: '', description: '' }, // Single holiday object for add/edit
        isEditing: false,
        editId: null,
        query: {
            page: 1,
            limit: 15,
            keyword: '',
            filter: {},
            role: '',
        },
        };
    },
    methods: {
        async fetchHolidays() {
        // Fetch the list of holidays from the API
        this.query.filter.description = {
                                            ['description']: this.query.keyword,
                                            ['date']: this.query.date,
                                        };
        const { data } = await resourcePro.list(this.query);
        this.holidays = data.holidays.data;
        },
        async handleSubmit() {
        const method = this.isEditing ? 'PUT' : 'POST';
        const url = this.isEditing ? `/api/holidays/${this.editId}` : '/api/holidays';

        await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(this.holiday),
        });

        this.resetForm();
        await this.fetchHolidays();
        },
        editHoliday(holiday) {
        this.isEditing = true;
        this.editId = holiday.id;
        this.holiday = { ...holiday };
        },
        async deleteHoliday(id) {
        await fetch(`/api/holidays/${id}`, {
            method: 'DELETE',
        });
        this.fetchHolidays();
        },
        resetForm() {
        this.holiday = { name: '', date: '', description: '' };
        this.isEditing = false;
        this.editId = null;
        },
    },
    mounted() {
        this.fetchHolidays();
    },
    };
</script>
<style scoped>
/* Add your styles here */
</style>