<template>
  <el-dialog
    :title="sectionId ? 'Edit Section' : 'Add Section'"
    :modelValue="addeditsectionprop"
    width="30%"
    @close="closeDialog"
  >
    <el-form :model="section" label-width="120px">
      <el-form-item label="Section Name" :rules="{ required: true }">
        <el-input v-model="section.name" autocomplete="off" />
      </el-form-item>
    </el-form>
    <template #footer>
      <span class="dialog-footer">
        <el-button @click="closeDialog">Cancel</el-button>
        <el-button type="primary" @click="onSubmit" :loading="loading">{{ loading ? 'Saving...' : 'Save' }}</el-button>
      </span>
    </template>
  </el-dialog>
</template>

<script>
import Resource from '@/api/resource';
const sectionResource = new Resource('sections');

export default {
  name: 'AddSection',
  props: {
    addeditsectionprop: {
      type: Boolean,
      required: true,
    },
    sectionId: {
      type: Number,
      required: false,
      default: null,
    },
    classId: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {
      loading: false,
      section: {
        id: null,
        name: '',
        class_id: this.classId,
      },
    };
  },
  watch: {
    sectionId: {
      handler: function(id) {
        if (id) {
          this.getSection(id);
        } else {
          this.section = {
            id: null,
            name: '',
            class_id: this.classId,
          };
        }
      },
      immediate: true,
    },
  },
  methods: {
    async getSection(id) {
      try {
        this.loading = true;
        const { data } = await sectionResource.get(id);
        this.section = data.section;
      } catch (error) {
        console.error('Error fetching section:', error);
        this.$message.error('Failed to load section details');
      } finally {
        this.loading = false;
      }
    },
    async onSubmit() {
      this.loading = true;
      try {
        if (this.sectionId) {
          await sectionResource.update(this.sectionId, this.section);
          this.$message({
            type: 'success',
            message: 'Section updated successfully',
          });
        } else {
          this.section.class_id = this.classId;
          await sectionResource.store(this.section);
          this.$message({
            type: 'success',
            message: 'Section added successfully',
          });
        }
        this.closeDialog();
      } catch (error) {
        console.error('Error saving section:', error);
        this.$message.error('Failed to save section');
      } finally {
        this.loading = false;
      }
    },
    closeDialog() {
      this.$emit('closeAddSection');
    },
  },
};
</script>

<style scoped>
.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
</style>
