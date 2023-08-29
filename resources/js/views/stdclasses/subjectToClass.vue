<template>
  <div class="app-container">
    <div class="filter-container" />
    <el-table
      :data="list"
      style="width: 100%"
    >
      <el-table-column label="Class" prop="name" />
      <el-table-column label="Subjects">
        <template #default="scope">
          {{ ConcateIt(scope.row.subjects) }}
        </template>
      </el-table-column>
      <el-table-column align="right">
        <template #default="scope">
          <el-button
            size="mini"
            @click="handleEdit(scope.row.id, scope.row.name)"
          >Edit</el-button>
        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />
    <el-drawer
      title="Edit Record"
      :modelValue="editnow"
      direction="rtl"
      custom-class="demo-drawer"
      ref="drawer"
    >
      <div class="demo-drawer__content">
        <el-form :model="model">
          <el-form-item label="Class" :label-width="formLabelWidth">
            <el-select v-model="model.class_id" placeholder="Select Class" disabled>
              <el-option
                v-for="sclass in classes"
                :key="sclass.id"
                :label="sclass.name"
                :value="sclass.id"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="Subjects" :label-width="formLabelWidth">
            <el-select v-model="model.subject_ids" multiple filterable placeholder="Select Subjects">
              <el-option
                v-for="subject in subjects"
                :key="subject.id"
                :label="subject.title"
                :value="subject.id"
              />
            </el-select>
          </el-form-item>
        </el-form>
        <div class="demo-drawer__footer">
          <el-button @click="editnow = false">Cancel</el-button>
          <el-button type="primary" @click="onSubmit" :loading="loading">{{ loading ? 'Submitting ...' : 'Submit' }}</el-button>
        </div>
      </div>
    </el-drawer>
  </div>
</template>
<script>
import Pagination from '@/components/Pagination/index.vue';
import Resource from '@/api/resource';
import { debounce } from 'lodash';
const classesPro = new Resource('classes');
const subjectsPro = new Resource('subjects');
const stocPro = new Resource('subject_class');
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
      formLabelWidth: 250,
      subjects: [],
      classes: [],
      model: {
        id: '',
        class_id: '',
        subject_ids: [],
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
      this.getClasses();

      const { data } = await classesPro.list(this.query);
      this.classes = data.classes.data;

      const sdata = await subjectsPro.list(this.query);
      this.subjects = sdata.data.subjects.data;
    },
    async getClasses() {
      const stocdata = await stocPro.list(this.query);
      this.list = stocdata.data.classubj.data;
    },
    ConcateIt(subjects) {
      return subjects.map(subject => subject.title).join(' | ');
    },
    async search_data() {
      await this.getList();
    },
    async handleEdit(id, name) {
      const { data } = await stocPro.get(id);
      const clsubj = data.classubj;
      this.model.class_id = clsubj.id;
      this.model.subject_ids = clsubj.subjects.map(subject => subject.id);
      this.editnow = true;
    },
    async handleDelete(id, name) {
      this.confirm('Do you really want to delete?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(async () => {
        await stocPro.destroy(id);
        this.getList();
        this.message({
          type: 'success',
          message: name+' Delete successfully'
        });
      });
    },
    async onSubmit() {
      if (this.model.class_id !== '') {
        this.loading = true;
        await stocPro.update(this.model.class_id, this.model);
        this.loading = false;
        this.editnow = false;
        this.getClasses();
      } else {
        this.loading = true;
        await stocPro.store(this.model);
        this.loading = false;
        this.editnow = false;
        this.getClasses();
      }
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