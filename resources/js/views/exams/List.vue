<template>
  <div class="app-container">
    <div class="filter-container">
      <el-button class="filter-item" style="margin-left: 10px;" type="success" icon="el-icon-plus" @click="addexampop = true">
        Add Exam
      </el-button>
    </div>
    <el-table
      :data="list"
      style="width: 100%"
    >
      <el-table-column label="Title" prop="title" />
      <el-table-column align="right">
        <template slot="header" slot-scope="scope">
          <el-input ref="search" v-model="query.keyword" size="mini" placeholder="Type to search" v-on:input="debounceInput" />
        </template>
        <template slot-scope="scope">
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
    <add-exam v-if="addexampop" :addexamprop="addexampop" :examid="examid" @closeAddExam="closeAddExamPopup()" />
  </div>
</template>
<script>
import Pagination from '@/components/Pagination';
import AddExam from '../exams/AddExam';
import Resource from '@/api/resource';
const examsPro = new Resource('exams');
export default {
  name: '',
  components: { Pagination, AddExam },
  directives: { },
  data() {
    return {
      list: null,
      addexampop: false,
      examid: null,
      search: '',
      total: 0,
      loading: false,
      downloading: false,
      editnow: false,
      formLabelWidth: 250,
      model: {
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
    debounceInput: _.debounce(function (e) {
      this.getList();
    }, 500),
    async getList() {
      const { data } = await examsPro.list(this.query);
      this.list = data.exams.data;
      this.total = data.exams.total;
    },
    async search_data() {
      await this.getList();
    },
    async handleEdit(id, name) {
      this.examid = id;
      this.addexampop = true;
    },
    async handleDelete(id, name) {
      this.$confirm('Do you really want to delete?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(async () => {
        await examsPro.destroy(id);
        this.getList();
        this.message({
          type: 'success',
          message: name+' Delete successfully',
        });
      });
    },
    closeAddExamPopup() {
      this.examid = null;
      this.addexampop = false;
      this.getList();
    },
  },
};
</script>
<style  scoped>
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