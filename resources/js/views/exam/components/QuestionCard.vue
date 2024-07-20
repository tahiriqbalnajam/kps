<!-- components/QuestionCard.vue -->
<template>
    <el-card>
      <p>{{ question.question_text }}</p>
      <el-radio-group v-model="selectedAnswer" @change="handleAnswerChange">
        <el-radio :value="question.choice_1" border>{{ question.choice_1 }}</el-radio>
        <el-radio :value="question.choice_2" border>{{ question.choice_2 }}</el-radio>
        <el-radio :value="question.choice_3" border>{{ question.choice_3 }}</el-radio>
        <el-radio :value="question.choice_4" border>{{ question.choice_4 }}</el-radio>
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
        this.selectedAnswer = null;
      }
    }
  };
  </script>