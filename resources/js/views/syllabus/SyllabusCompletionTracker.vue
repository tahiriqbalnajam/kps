<template>
  <div class="syllabus-completion">
    <el-card>
      <h2>Syllabus Completion Tracker</h2>
      <el-table :data="trackingEntries" style="width: 100%">
        <el-table-column prop="class_name" label="Class" />
        <el-table-column prop="subject_name" label="Subject" />
        <el-table-column prop="topic_name" label="Topic" />
        <el-table-column prop="date_range" label="Date Range" />
        <el-table-column label="Status">
          <template #default="scope">
            <el-switch v-model="scope.row.completed" @change="markCompletion(scope.row)" />
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script>
import Resource from '@/api/resource';

export default {
  name: 'SyllabusCompletionTracker',
  data() {
    return {
      trackingEntries: []
    };
  },
  created() {
    this.fetchTrackingEntries();
  },
  methods: {
    async fetchTrackingEntries() {
      const resource = new Resource('tracking');
      const { data } = await resource.list();
      this.trackingEntries = data.entries;
    },
    async markCompletion(entry) {
      const resource = new Resource('tracking');
      await resource.update(entry.id, { completed: entry.completed });
      this.$message.success('Completion status updated!');
    }
  }
};
</script>
