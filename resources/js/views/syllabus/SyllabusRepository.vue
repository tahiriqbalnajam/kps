<template>
  <div class="syllabus-repository">
    <el-card>
      <h2>Syllabus Repository</h2>
      <el-form :model="form" label-width="120px">
        <el-form-item label="Class">
          <el-select v-model="form.class_id" placeholder="Select Class" @change="fetchSubjects">
            <el-option v-for="cls in classes" :key="cls.id" :label="cls.name" :value="cls.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="Subject">
          <el-select v-model="form.subject_id" placeholder="Select Subject" :disabled="!form.class_id">
            <el-option v-for="sub in subjects" :key="sub.id" :label="sub.title" :value="sub.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="Chapter">
          <el-select
            v-model="form.chapter"
            placeholder="Search or Add Chapter"
            filterable
            allow-create
            default-first-option
          >
            <el-option v-for="chapter in chapters" :key="chapter" :label="chapter" :value="chapter" />
          </el-select>
        </el-form-item>
        <el-form-item label="Topics">
          <el-input v-model="form.topics" type="textarea" placeholder="Enter Topics (comma-separated)" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="saveSyllabus">Save</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script>
import Resource from '@/api/resource';

export default {
  name: 'SyllabusRepository',
  data() {
    return {
      form: {
        class_id: null,
        subject_id: null,
        chapter: '',
        topics: ''
      },
      classes: [],
      subjects: [],
      chapters: [],
      query: {
        class_id: null,
        subject_id: null,
        id: null,
        filter: {},
      }
    };
  },
  computed: {
    filteredSubjects() {
      return this.subjects.filter(subject => subject.class_id === this.form.class_id);
    }
  },
  created() {
    this.fetchClasses();
    this.fetchChapters();
  },
  methods: {
    async fetchClasses() {
      const resource = new Resource('classes');
      const { data } = await resource.list();
      this.classes = data.classes.data;
    },
    async fetchSubjects() {
      if (!this.form.class_id) return;
      const resource = new Resource('subject_class');
      this.query.filter['id'] = this.form.class_id;
      const { data } = await resource.list(this.query);
      console.log(data.classubj.data[0].subjects);
      this.subjects = data.classubj.data[0].subjects;
    },
    async fetchChapters() {
      const resource = new Resource('chapters');
      const { data } = await resource.list();
      this.chapters = data.chapters.map(chapter => chapter.name);
    },
    async saveSyllabus() {
      const resource = new Resource('syllabus/repository');
      await resource.store(this.form);
      this.$message.success('Syllabus saved successfully!');
    }
  }
};
</script>
