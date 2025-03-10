<template>
  <div class="app-container">
    <div class="filter-container">
      <el-button :icon="Plus" class="filter-item" style="margin-left: 10px;" type="success" @click="addstdclasspop = true">
        Add Class
      </el-button>
    </div>
    <el-table
      :data="classes"
      style="width: 100%"
      max-height="500"
      size="small"
      stripe
      highlight-current-row
    >
      <el-table-column type="expand">
        <template #default="props">
          <div class="sections-container">
            <div class="sections-header">
              <h4>Sections</h4>
              <el-button 
                size="mini" 
                type="primary" 
                @click="openAddSection(props.row.id)"
              >
                <el-icon><Plus style="margin-right: 8px"/></el-icon> Add Section
              </el-button>
            </div>
            <el-table 
              v-if="props.row.sections && props.row.sections.length > 0"
              :data="props.row.sections"
              style="width: 100%"
              border
              size="small"
              stripe
              fit
            >
              <el-table-column label="ID" prop="id" width="60" />
              <el-table-column label="Name" prop="name" />
              <el-table-column label="Total Students" prop="students_count" />
              <el-table-column label="Boys" prop="males_count" />
              <el-table-column label="Girls" prop="females_count" />
              <el-table-column align="right">
                <template #default="scope">
                  <el-button
                    size="small"
                    @click="handleEditSection(scope.row.id)"
                  >Edit</el-button>
                  <el-button
                    size="small"
                    type="danger"
                    @click="handleDeleteSection(scope.row.id, scope.row.name)"
                  >Delete</el-button>
                </template>
              </el-table-column>
            </el-table>
            <div v-else class="no-sections">
              No sections available for this class
            </div>
          </div>
        </template>
      </el-table-column>
      <el-table-column label="ID" prop="id" />
      <el-table-column label="Name" prop="name" />
      <el-table-column label="Total Students">
        <template #default="scope">
          {{ getTotalStudents(scope.row) }}
        </template>
      </el-table-column>
      <el-table-column label="Boys" prop="males_count" />
      <el-table-column label="Girls" prop="females_count" />
      <el-table-column align="right">
        <template #header="scope">
          <el-input ref="search" v-model="query.keyword" size="mini" placeholder="Type to search" v-on:input="debounceInput" />
        </template>
        <template #default="scope">
          <el-button
            size="small"
            @click="handleEdit(scope.row.id, scope.row.name)"
          >Edit</el-button>
          <el-button
            size="small"
            type="danger"
            @click="handleDelete(scope.row.id, scope.row.name)"
          >Delete</el-button>
        </template>
      </el-table-column>
    </el-table>
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
    <add-class :addeditclassprop="addstdclasspop" :classid="classid" @closeAddClass="closeAddClassPopup()" />
    <add-section 
      v-if="showSectionDialog"
      :addeditsectionprop="showSectionDialog" 
      :sectionId="currentSectionId" 
      :classId="currentClassId"
      @closeAddSection="closeSectionDialog" 
    />
  </div>
</template>
<script>
import Pagination from '@/components/Pagination/index.vue';
import AddClass from '@/views/stdclasses/AddClass.vue';
import AddSection from '@/views/stdclasses/AddSection.vue';
import Resource from '@/api/resource';
import { debounce } from 'lodash';
import { Plus } from '@element-plus/icons-vue';

const classesPro = new Resource('classes');
const sectionsResource = new Resource('sections');

export default {
  name: 'ClassList',
  components: { Pagination, AddClass, AddSection, Plus },
  directives: { },
  data() {
    return {
      classid: null,
      addstdclasspop: false,
      showSectionDialog: false,
      currentSectionId: null,
      currentClassId: null,
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
        include: 'sections',
      },
    };
  },
  computed: {
  },
  created() {
    this.getList();
  },
  methods: {
    async handleSizeChange (val) {
      this.query.limit = val
      await this.getList()
    },
    async handleCurrentChange (val) {
      this.query.page = val
      await this.getList()
    },
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
    getTotalStudents(row) {
      return (row.males_count || 0) + (row.females_count || 0);
    },
    openAddSection(classId) {
      this.currentClassId = classId;
      this.currentSectionId = null;
      this.showSectionDialog = true;
    },
    handleEditSection(sectionId) {
      this.currentSectionId = sectionId;
      this.showSectionDialog = true;
    },
    handleDeleteSection(sectionId, sectionName) {
      this.$confirm(`Do you really want to delete section "${sectionName}"?`, 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(async () => {
        await sectionsResource.destroy(sectionId);
        this.getList(); // Refresh to show updated sections
        this.$message({
          type: 'success',
          message: `Section "${sectionName}" deleted successfully`
        });
      });
    },
    closeSectionDialog() {
      this.showSectionDialog = false;
      this.currentSectionId = null;
      this.currentClassId = null;
      this.getList(); // Refresh data to show new/updated sections
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

/* New styles for sections */
.sections-container {
  padding: 20px;
  background: #f9f9f9;
  border-radius: 4px;
  margin: 10px;
}

.sections-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.sections-header h4 {
  margin: 0;
}

.no-sections {
  text-align: center;
  padding: 20px;
  color: #909399;
  font-style: italic;
}
</style>