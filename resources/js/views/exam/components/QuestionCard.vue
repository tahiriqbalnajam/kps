<!-- components/QuestionCard.vue -->
<template>
    <el-card>
      <p>{{ question.choice_1 }}</p>
      <el-radio-group v-model="selectedAnswer" @change="handleAnswerChange">
        <el-radio :label="1">{{ question.choice_1 }}</el-radio>
        <el-radio :label="2">{{ question.choice_2 }}</el-radio>
        <el-radio :label="3">{{ question.choice_3 }}</el-radio>
        <el-radio :label="4">{{ question.choice_4 }}</el-radio>
      </el-radio-group>
    </el-card>
  </template>
  
  <script>
  export default {
    props: ['question'],
    data() {
      return {
        selectedAnswer: null
      };
    },
    methods: {
      handleAnswerChange() {
        this.$emit('answer-selected', this.question.id, this.selectedAnswer);
      }
    }
  };
  </script>