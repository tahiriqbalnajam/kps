<!-- components/QuestionCard.vue -->
<template>
    <el-card style="margin-top: 50px;" :class="(this.$route.params.lang == 'eng') ? 'ltr' : 'rtl'">
      <p style="font-size: 18px; margin-bottom: 30px;">{{ question.question_text }}</p>
      <el-radio-group v-model="selectedAnswer" @change="handleAnswerChange">
        <el-radio :value="question.choice_1" border class="bigsize">{{question.choice_1 }}</el-radio>
        <el-radio :value="question.choice_2" border class="bigsize">{{ question.choice_2 }}</el-radio>
        <el-radio :value="question.choice_3" border class="bigsize">{{ question.choice_3 }}</el-radio>
        <el-radio :value="question.choice_4" border class="bigsize">{{ question.choice_4 }}</el-radio>
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
  <style scoped>
    .bigsize {
      font-size:16px;
    }
    .ltr {
      text-align: left;
      direction: ltr;
    }
    .rtl {
      text-align: right;
      direction: rtl;
    }



  </style>