<template>
  <div class="syllabus-tracking">
    <el-card>
      <h2>Syllabus Tracking</h2>
      <el-form :model="form" label-width="120px">
        <el-form-item label="Class">
          <el-select v-model="form.class_id" placeholder="Select Class">
            <el-option v-for="cls in classes" :key="cls.id" :label="cls.name" :value="cls.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="Subject">
          <el-select v-model="form.subject_id" placeholder="Select Subject">
            <el-option v-for="sub in subjects" :key="sub.id" :label="sub.name" :value="sub.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="Topic">
          <el-select v-model="form.topic_id" placeholder="Select Topic">
            <el-option v-for="topic in topics" :key="topic.id" :label="topic.name" :value="topic.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="Date Range">
          <el-date-picker v-model="form.date_range" type="daterange" range-separator="to" start-placeholder="Start Date" end-placeholder="End Date" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="saveTracking">Save</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script>
import Resource from '@/api/resource';

export default {
  name: 'SyllabusTrackingEntry',
  data() {
    return {
      form: {
        class_id: null,
        subject_id: null,
        topic_id: null,
        date_range: []
      },
      classes: [],
      subjects: [],
      topics: []
    };
  },
  created() {
    this.fetchClasses();
    this.fetchSubjects();
  },
  methods: {
    async fetchClasses() {
      const resource = new Resource('classes');
      const { data } = await resource.list();
      this.classes = data.classes;
    },
    async fetchSubjects() {
      const resource = new Resource('subjects');
      const { data } = await resource.list();
      this.subjects = data.subjects;
    },
    async fetchTopics() {
      const resource = new Resource('syllabus');
      const { data } = await resource.list({ class_id: this.form.class_id, subject_id: this.form.subject_id });
      this.topics = data.topics;
    },
    async saveTracking() {
      const resource = new Resource('tracking');
      await resource.create(this.form);
      this.$message.success('Tracking entry saved successfully!');
    }
  },
  watch: {
    'form.class_id': 'fetchTopics',
    'form.subject_id': 'fetchTopics'
  }
};
</script>
