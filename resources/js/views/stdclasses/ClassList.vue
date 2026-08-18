<template>
  <div class="app-container">
    <div class="filter-container">
      <el-button :icon="Plus" class="filter-item" style="margin-left: 10px;" type="success" @click="addstdclasspop = true">
        Add Class
      </el-button>
      <div style="margin-left: 20px; display: inline-block;">
        <span style="font-weight: 600; margin-right: 10px;">Sort:</span>
        <el-button size="small" type="primary" @click="toggleSortPriority('asc')">Priority Asc</el-button>
        <el-button size="small" type="primary" @click="toggleSortPriority('desc')">Priority Desc</el-button>
        <el-button size="small" @click="toggleSortPriority(null)">Default</el-button>
      </div>
      <div style="margin-left: 20px; display: inline-block;">
        <span style="font-weight: 600; margin-right: 10px;">Status:</span>
        <el-select v-model="query.status" size="small" style="width: 110px;" @change="getList">
          <el-option label="Enabled" value="enable" />
          <el-option label="Disabled" value="disable" />
          <el-option label="All" value="all" />
        </el-select>
      </div>
    </div>
    <el-table
      :data="displayedClasses"
      style="width: 100%"
      max-height="500"
      size="small"
      stripe
      highlight-current-row
      row-key="id"
    >
      <el-table-column width="50">
        <template #default="scope">
          <el-icon class="drag-handle" style="cursor: move; font-size: 18px;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
              <path d="M9 3h2v2H9V3zm4 0h2v2h-2V3zM9 7h2v2H9V7zm4 0h2v2h-2V7zm-4 4h2v2H9v-2zm4 0h2v2h-2v-2zm-4 4h2v2H9v-2zm4 0h2v2h-2v-2zm-4 4h2v2H9v-2zm4 0h2v2h-2v-2z"/>
            </svg>
          </el-icon>
        </template>
      </el-table-column>
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
                  <el-switch
                    v-model="scope.row.status"
                    active-value="enable"
                    inactive-value="disable"
                    class="cls-status-switch"
                    style="margin-right: 8px;"
                    @change="(val) => handleToggleSectionStatus(scope.row, val)"
                  />
                  <el-tooltip content="Edit Section" placement="top">
                    <el-button
                      size="small"
                      type="primary"
                      plain
                      @click="handleEditSection(scope.row.id)"
                    >
                      <el-icon><Edit /></el-icon>
                    </el-button>
                  </el-tooltip>
                  <el-tooltip content="Delete Section" placement="top">
                    <el-button
                      size="small"
                      type="danger"
                      plain
                      @click="handleDeleteSection(scope.row.id, scope.row.name)"
                    >
                      <el-icon><Delete /></el-icon>
                    </el-button>
                  </el-tooltip>
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
      <el-table-column label="Priority" width="100">
        <template #default="scope">
          <strong>{{ scope.row.priority || 0 }}</strong>
        </template>
      </el-table-column>
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
          <el-switch
            v-model="scope.row.status"
            active-value="enable"
            inactive-value="disable"
            class="cls-status-switch"
            style="margin-right: 8px;"
            @change="(val) => handleToggleStatus(scope.row, val)"
          />
          <el-tooltip content="Edit Class" placement="top">
            <el-button
              size="small"
              type="primary"
              plain
              @click="handleEdit(scope.row.id, scope.row.name)"
            >
              <el-icon><Edit /></el-icon>
            </el-button>
          </el-tooltip>
          <el-tooltip content="Delete Class" placement="top">
            <el-button
              size="small"
              type="danger"
              plain
              @click="handleDelete(scope.row.id, scope.row.name)"
            >
              <el-icon><Delete /></el-icon>
            </el-button>
          </el-tooltip>
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
import { Plus, Edit, Delete } from '@element-plus/icons-vue';
import Sortable from 'sortablejs';
import axios from 'axios';
import { sessionStore } from '@/store/session';

const classesPro = new Resource('classes');
const sectionsResource = new Resource('sections');

export default {
  name: 'ClassList',
  components: { Pagination, AddClass, AddSection, Plus, Edit, Delete },
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
        status: 'enable',
        include: 'sections',
        // classlist management page needs disabled sections visible in the
        // expand table too (so they can be re-enabled); dropdown consumers
        // don't send this, so they get enabled sections only.
        sections_status: 'all',
      },
      sortOrder: null, // null | 'asc' | 'desc'
    };
  },
  computed: {
    currentSessionId() {
      return sessionStore().currentSessionId;
    },
    displayedClasses() {
      if (!this.classes) return [];
      // Make a copy and ensure priority exists
      let list = this.classes.map(c => ({ ...c, priority: Number(c.priority) || 0 }));

      if (this.sortOrder === 'asc') {
        return list.sort((a, b) => a.priority - b.priority);
      }
      if (this.sortOrder === 'desc') {
        return list.sort((a, b) => b.priority - a.priority);
      }

      return list;
    },
  },
  created() {
    this.getList();
  },
  watch: {
    // Navbar session dropdown only updates the store — refetch when it changes
    currentSessionId() {
      this.query.page = 1;
      this.getList();
    },
  },
  mounted() {
    this.initDragDrop();
  },
  methods: {
    initDragDrop() {
      const el = document.querySelector('.el-table__body-wrapper tbody');
      if (!el) return;

      Sortable.create(el, {
        handle: '.drag-handle',
        animation: 150,
        onEnd: async (evt) => {
          const { oldIndex, newIndex } = evt;
          if (oldIndex === newIndex) return;

          // Get the current displayed list (make a deep copy)
          const currentList = JSON.parse(JSON.stringify(this.classes));
          
          // Reorder based on what user sees
          const movedItem = currentList.splice(oldIndex, 1)[0];
          currentList.splice(newIndex, 0, movedItem);

          // Reassign priorities based on new order (1-indexed)
          const updates = currentList.map((item, index) => {
            item.priority = index + 1;
            return {
              id: item.id,
              priority: item.priority
            };
          });

          // Update the classes array immediately
          this.classes = currentList;

          // Batch save to server
          await this.savePriorityOrder(updates);
        }
      });
    },

    async savePriorityOrder(updates) {
      try {
        // Bulk save all priorities in a single request using axios directly
        await axios.post('/api/classes/bulk-update-priority', {
          classes: updates.map(u => ({
            id: u.id,
            priority: u.priority
          }))
        });
        
        this.$message({ type: 'success', message: 'Order saved successfully' });
        // Don't refresh immediately - keep the current order
        // this.getList(); 
      } catch (error) {
        console.error('Error saving order', error);
        this.$message({ type: 'error', message: 'Failed to save order' });
        this.getList(); // Only revert on error
      }
    },
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
      this.query.session_id = this.currentSessionId;
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
    async handleToggleStatus(row, newStatus) {
      try {
        await axios.post(`/api/classes/${row.id}/toggle-status`);
        // Keep the row visible in its new state instead of refetching —
        // a refetch on the Enabled/Disabled filters makes the row vanish,
        // which reads as "the toggle switched back".
        const item = this.classes.find(c => c.id === row.id);
        if (item) item.status = newStatus;
        row.status = newStatus;
        this.$message({
          type: 'success',
          message: `Class "${row.name}" ${newStatus === 'enable' ? 'enabled' : 'disabled'} successfully`
        });
      } catch (error) {
        this.$message({ type: 'error', message: 'Failed to update class status' });
        this.getList(); // revert the optimistic switch on failure
      }
    },
    closeAddClassPopup() {
      this.addstdclasspop = false;
      this.classid = null;
      this.getList();
    },
    getTotalStudents(row) {
      return (row.males_count || 0) + (row.females_count || 0);
    },
    // Toggle sorting by priority: 'asc', 'desc' or null
    toggleSortPriority(order) {
      if (order === null) {
        this.sortOrder = null;
      } else {
        this.sortOrder = order;
      }
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
    async handleToggleSectionStatus(row, newStatus) {
      try {
        await axios.post(`/api/sections/${row.id}/toggle-status`);
        // Keep the row visible in its new state (no refetch — same reason
        // as classes: a refetch would make the row vanish on this page).
        const parent = this.classes.find(c => c.id === row.class_id);
        const item = parent ? parent.sections.find(s => s.id === row.id) : null;
        if (item) item.status = newStatus;
        row.status = newStatus;
        this.$message({
          type: 'success',
          message: `Section "${row.name}" ${newStatus === 'enable' ? 'enabled' : 'disabled'} successfully`
        });
      } catch (error) {
        this.$message({ type: 'error', message: 'Failed to update section status' });
        this.getList(); // revert the optimistic switch on failure
      }
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

/* Status toggle switch — green when enabled, red when disabled.
   element-plus 2.14 no longer applies active-color/inactive-color props, so style via classes. */
.cls-status-switch .el-switch__core {
  border-color: #f56c6c;
  background-color: #f56c6c;
}
.cls-status-switch.is-checked .el-switch__core {
  border-color: #13ce66;
  background-color: #13ce66;
}
</style>