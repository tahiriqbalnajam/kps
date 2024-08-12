<!-- components/Result.vue -->
<template>
    <el-card>
      <h2>Your Score</h2>
      <el-row :gutter="20">
        <el-col :span="8">
          <span class="heading"> Name : 10th Computer Chapter 1 Online Test</span>
          <span class="heading">Type</span> : MCQ's
          <span class="heading">Total Questions</span> : {{ totalQuestions }}
          <span class="heading">Total Marks</span> : {{ totalQuestions }}
        </el-col>
        <el-col :span="8">
          <div v-if="incorrectQuestions.length">
            <h3>Incorrect Questions:</h3>
            <ul>
              <li v-for="(question, index) in incorrectQuestions" :key="index">
                <p><strong>Question {{ index + 1 }}: {{ question.question_text }}</strong></p>
                <p class="wrong">Selected Option: {{ question.selectedOption }}</p>
                <p class="correct">Correct Option: {{ question.correctOption }}</p>
              </li>
            </ul>
          </div>
          <div v-else>
            <h3>All questions were answered correctly!</h3>
          </div>
        </el-col>
        <el-col :span="8">
          <el-progress type="dashboard" :percentage="score">
            <template #default="{ percentage }">
              <span class="percentage-value">{{ percentage }}%</span>
              <span class="percentage-label"></span>
            </template>
          </el-progress>
        </el-col>
      </el-row>
      
    </el-card>
  </template>
  
  <script>
  export default {
    props: {
      score: {
        type: Number,
        required: true,
      },
      questions: {
        type: Array,
        required: true,
      },
      userAnswers: {
        type: Object,
        required: true,
      }
  },
  computed: {
    incorrectQuestions() {
      return this.questions.filter(question => {
        const selectedOption = this.userAnswers[question.id];
        const correctOption = question[question.correct_choice];
        return selectedOption !== correctOption;
      }).map(question => {
        return {
          question_text: question.question_text,
          selectedOption: this.userAnswers[question.id],
          correctOption: question[question.correct_choice]
        };
      });
    }
  }
  };
  </script>
  <style scoped>
  .percentage-value {
    display: block;
    margin-top: 10px;
    font-size: 28px;
  }
  .percentage-label {
    display: block;
    margin-top: 10px;
    font-size: 12px;
  }
  .demo-progress .el-progress--line {
    margin-bottom: 15px;
    max-width: 600px;
  }
  .demo-progress .el-progress--circle {
    margin-right: 15px;
  }
  .heading {
    font-size: 17;
    font-weight: bold;
  }
  .wrong {
    color:#ff0000d1
  }
  .correct {
    color:#00a500fa;
  }
  </style>