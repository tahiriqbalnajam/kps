<template>
  <div class="app-container">
    <div class="filter-container">
        <head-controls>
            <el-row :gutter="20" justify="space-between">
              <el-col :span="12">
                <el-row :gutter="20">
                  <el-col :span="6">
                    <el-select v-model="query.filtercol" placeholder="Class" class="filter-item">
                      <el-option v-for="filter in filtercol" :key="filter.col" :label="filter.display | uppercaseFirst" :value="filter.col" />
                    </el-select>
                  </el-col>
                  <el-col :span="10">
                    <el-input v-model="query.keyword" placeholder="Parent info" class="filter-item" v-on:input="debounceInput" />
                  </el-col>
                  <el-col :span="6">
                    <el-button  class="filter-item" type="primary" :icon="Search"  @click="handleFilter">
                      {{ $t('table.search') }}
                    </el-button>
                  </el-col>
                </el-row>
              </el-col>
              <el-col :span="12">
                <el-row justify="end">
                  <el-col :span="3">
                    <el-tooltip content="Add Parent" placement="top">
                      <el-button class="filter-item" style="margin-left: 10px;" type="info" :icon="Plus" @click="addparentpop = true" >
                        <el-icon :size="15"><Plus /></el-icon>
                      </el-button>
                    </el-tooltip>
                  </el-col>
                  <el-col :span="3">
                    <el-tooltip content="Parent Excel" placement="top">
                      <el-button class="filter-item" :loading="downloadLoading"  type="danger" :icon="Search"  @click="handleDownload">
                        <el-icon><Download /></el-icon>
                    </el-button>
                    </el-tooltip>
                  </el-col>
                </el-row>
              </el-col>
            </el-row>
        </head-controls> 
    </div>
    <el-table
      :data="parents"
      style="width: 100%"
       max-height="450"
    >
      <el-table-column label="ID" prop="id" />
      <el-table-column label="Name" prop="name" />
      <el-table-column label="Phone" prop="phone" />
      <el-table-column label="Address" prop="address" />
      <el-table-column label="CNIC" prop="cnic" />
      <el-table-column label="Children" prop="children">
        <template #default="scope">
          <el-table :data=" scope.row.students" size="small">
            <el-table-column type="index"/>
            <el-table-column property="name" label="Name" />
          </el-table>
        </template>
      </el-table-column>
      <el-table-column align="right">
        <template #header="scope">
          <el-input ref="search" v-model="query.keyword" size="mini" placeholder="Type to search"  v-on:input="debounceInput" />
        </template>
        <template #default="scope">
          <el-button
            size="mini"
            @click="handleEdit(scope.row.id, scope.row.name)"
          >Edit</el-button>
          <el-button
            size="mini"
            type="danger"
            @click="handleDelete(scope.row.id, scope.row.name)"
          >Delete</el-button>
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
    <add-parent v-if="addparentpop" :editnowprop="addparentpop" :parentid="parentid" @closePopUp="closePopUp()" />
  </div>
</template>
<script>
import Pagination from '@/components/Pagination/index.vue';
import Resource from '@/api/resource';
import AddParent from '@/views/parents/AddParent.vue';
import HeadControls from '@/components/HeadControls.vue';
import { debounce } from 'lodash';
const parentsPro = new Resource('parents');
export default {
  name: 'ParentList',
  components: { Pagination, AddParent, HeadControls},
  directives: { },
  filters: {
    dateformat: (date) => {
      return (!date) ? '' : moment(date).format('DD MMM, YYYY');
    },
  },
  data() {
    return {
      downloadLoading: false,
      parentid: null,
      parents: null,
      search: '',
      total: 0,
      loading: false,
      downloading: false,
      addparentpop: false,
      addstudentpop: false,
      search: '',
      formLabelWidth: '250',
      parent: {
        id: '',
        name: '',
      },
      filtercol: [
        { col: 'name', display: 'Name' },
        { col: 'cnic', display: 'CNIC' },
        { col: 'phone', display: 'Phone#' },
        { col: 'all', display: 'All' },
      ],
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        filtercol: 'name',
        role: '',
      },
    };
  },
  computed: {
  },
  created() {
    this.getList();
  },
  methods: {
    debounceInput: debounce(function (e) {
      this.getList();
    }, 500),
    closeAddParent(parm) {
      this.addparentpop = false;
    },
    async handleSizeChange (val) {
      console.log(`每页 ${val} 条`);
      this.query.limit = val
      await this.getList()
    },
    async handleCurrentChange (val) {
      this.query.page = val
      await this.getList()
    },
    async getList() {
      const { data } = await parentsPro.list(this.query);
      this.parents = data.parents.data;
      this.total = data.parents.total;
    },
    async search_data() {
      await this.getList();
    },
    handleEdit(id, name) {
      this.parentid = id;
      this.addparentpop = true;
    },
    closePopUp() {
      this.parentid = null;
      this.addparentpop = false;
      this.getList();
    },
    addStudentFunc() {
      this.addstudentpop = true;
    },
    closeAddStudent() {
      this.addstudentpop = !this.addstudentpop;
      this.stdid = null;
      this.getList();
    },
    handleFilter() {
      this.getList();
    },
    handleDownload() {
      this.downloadLoading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['Name', 'Phone','Address','Profession','CNIC'];
        const filterVal = ['name', 'phone','address','profession','cnic'];
        const list = this.formateData(this.list);
        const data = this.formatJson(filterVal, list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'paid_fee_today',
        });
      });
    },
    formateData(data) {
      const formatedData = data.map(record => (
        {
          roll_no: record.roll_no,
          name: record.name,
          parent_name: record.parents.name,
          phone: record.parents.phone,
          class: record.stdclasses.name,
          gender: record.gender,
          fee: record.monthly_fee,
          dob: record.dob,
        }
      )
      );
      this.downloadLoading = false;
      return formatedData;
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => {
        if (j === 'timestamp') {
          return parseTime(v[j]);
        } else {
          return v[j];
        }
      }));
    },
  },
};
</script>
<style >
  .el-drawer__body {
    flex: 1;
    padding: 20px;
  }
  .demo-drawer__content {
    display: flex;
    flex-direction: column;
    height: 100%;
    padding: 20px;
  }
</style>
<style>
.el-popper.is-customized {
  /* Set padding to ensure the height is 32px */
  padding: 6px 12px;
  background: linear-gradient(90deg, rgb(159, 229, 151), rgb(204, 229, 129));
}

.el-popper.is-customized .el-popper__arrow::before {
  background: linear-gradient(45deg, #b2e68d, #bce689);
  right: 0;
}
</style>