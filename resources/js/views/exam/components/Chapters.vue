<template>
    <el-select v-model="selectedChapter" placeholder="Select Chapter" @change="fetchQuestions">
      <el-option
        v-for="chapter in chapters"
        :key="chapter.id"
        :label="chapter.title"
        :value="chapter.id">
      </el-option>
    </el-select>
  </template>
  
  <script>
    import resource from '@/api/resource';
  
  export default {
    data() {
      return {
        chapters: [],
        selectedChapter: null
      };
    },
    async created() {
        const { data } = chapter.list();
        this.chapters = data.chapters.data;
    },
    methods: {
      fetchQuestions() {
        this.$emit('chapter-selected', this.selectedChapter);
      }
    }
  };
  </script>