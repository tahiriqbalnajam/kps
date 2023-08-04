<template>
  <div class="app-container">
    <div class="filter-container">
      <el-button class="filter-item" style="margin-left: 10px;" type="success" icon="el-icon-plus" @click="editnow = true">
        Add Subject
      </el-button>
    </div>
    <el-table
      :data="list"
      style="width: 100%"
    >
      <el-table-column label="ID" prop="id" />
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
    <el-drawer
      title="Edit Subject"
      :visible.sync="editnow"
      direction="rtl"
      custom-class="demo-drawer"
      ref="drawer"
    >
      <div class="demo-drawer__content">
        <el-form :model="subject">
          <el-form-item label="Subject Title" :label-width="formLabelWidth">
            <el-input v-model="subject.title" autocomplete="off" />
          </el-form-item>
        </el-form>
        <div class="demo-drawer__footer">
          <el-button @click="editnow = false">Cancel</el-button>
          <el-button type="primary" :loading="loading" @click="onSubmit">{{ loading ? 'Submitting ...' : 'Submit' }}</el-button>
        </div>
      </div>
    </el-drawer>
  </div>
</template>
<script>
import Pagination from '@/components/Pagination';
import Resource from '@/api/resource';
const subjectsPro = new Resource('subjects');
export default {
  name: '',
  components: { Pagination },
  directives: { },
  data() {
    return {
      list: null,
      search: '',
      total: 0,
      loading: false,
      downloading: false,
      editnow: false,
      formLabelWidth: '250',
      subject: {
        id: '',
        name: '',
      },
      resetsubject: {
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
      const { data } = await subjectsPro.list(this.query);
      this.list = data.subjects.data;
      this.total = data.subjects.total;
    },
    async search_data() {
      await this.getList();
    },
    async handleEdit(id, name) {
      const { data } = await subjectsPro.get(id);
      this.subject = data.subject;
      this.editnow = true;
    },
    async handleDelete(id, name) {
      this.$confirm('Do you really want to delete?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(async () => {
        await subjectsPro.destroy(id);
        this.getList();
        this.message({
          type: 'success',
          message: name + ' Delete successfully'
        });
      });
    },
    async onSubmit() {
      this.loading = true;
      if (this.subject.id !== '') {
        await subjectsPro.update(this.subject.id, this.subject);
        this.editnow = false;
        this.subject = { ...this.resetsubject }
        this.getList();
      } else {
        await subjectsPro.store(this.subject);
        this.subject = { ...this.resetsubject }
        this.getList();
      }
      this.loading = false;
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