<template>
  <div class="app-container">
    <div class="filter-container">
      <el-button class="filter-item" style="margin-left: 10px;" type="success" icon="el-icon-plus" @click="addstdclasspop = true">
        Add Class
      </el-button>
    </div>
    <el-table
      :data="classes"
      style="width: 100%"
      max-height="500"
    >
      <el-table-column label="ID" prop="id" />
      <el-table-column label="Name" prop="name" />
      <el-table-column label="Total Students" prop="total_students" />
      <el-table-column align="right">
        <template #header="scope">
          <el-input ref="search" v-model="query.keyword" size="mini" placeholder="Type to search" v-on:input="debounceInput" />
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
    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />
    <add-class :addeditclassprop="addstdclasspop" :classid="classid" @closeAddClass="closeAddClassPopup()" />
  </div>
</template>
<script>
import Pagination from '@/components/Pagination/index.vue';
import AddClass from '@/views/stdclasses/AddClass.vue';
import Resource from '@/api/resource';
import { debounce } from 'lodash';
const classesPro = new Resource('classes');
export default {
  name: 'ClassList',
  components: { Pagination, AddClass },
  directives: { },
  data() {
    return {
      classid: null,
      addstdclasspop: false,
      classes: null,
      search: '',
      total: 0,
      loading: false,
      downloading: false,
      editnow: false,
      formLabelWidth: '150',
      stdclass: {
        id: '',
        name: '',
      },
      query: {
        page: 1,
        limit: 15,
        keyword: '',
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
    async getList() {
      const { data } = await classesPro.list(this.query);
      this.classes = data.classes.data;
      this.total = data.classes.total;
    },
    search_data() {
      this.getList();
    },
    async handleEdit(id, name) {
      this.classid = id;
      this.addstdclasspop = true;
    },
    handleDelete(id, name) {
      this.$confirm('Do you really want to delete?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(async () => {
        await classesPro.destroy(id);
        this.getList();
        this.$message({
          type: 'success',
          message: name+' Delete successfully'
        });
      });
    },
    closeAddClassPopup() {
      this.addstdclasspop = false;
      this.classid = null;
      this.getList();
    },
  },
};
</script>
<style>
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