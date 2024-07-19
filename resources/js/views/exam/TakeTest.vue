<template>
    <div id="app">
      <el-steps :active="activeStep" finish-status="success">
        <el-step v-for="(question, index) in questions" :key="index" :title="'Question ' + (index + 1)">
        </el-step>
        <el-step title="Result"></el-step>
      </el-steps>
  
      <div v-if="activeStep < questions.length">
        <QuestionCard
          :question="questions[activeStep]"
          @answer-selected="handleAnswerSelected"
        />
      </div>
  
      <div v-else>
        <Result :score="calculateScore()" />
      </div>
    </div>
  </template>
  
  <script>
  import { filter } from 'lodash';
import ChapterList from './components/Chapters.vue';
  import QuestionCard from './components/QuestionCard.vue';
  import Result from './components/ResultCard.vue';
  import resource from '@/api/resource';
  const chapters  = new resource('chapters');
  const questions  = new resource('questions');

  export default {
    name: 'OnlineTestAdmin',
    components:{ ChapterList, QuestionCard,Result },
    data() {
        return {
            loading: false,
            activeStep: 0,
            selectedChapterId: '',
            questions: [],
            userAnswers: {},
            chapter_query: {
                filter:{}
            },
            question_query: {
                filter:{}
            }
            
        };
    },
    created() {
        this.handleChapterSelected();
    },
    methods: {

        async handleChapterSelected () {
            this.question_query.filter['chapter_id'] = 9;
            const { data } = await questions.list(this.question_query);
            this.questions = data.questions.data

            questions.splice(0, questions.length, ...response.data);
         },
         handleAnswerSelected(questionId, answer){
            userAnswers[questionId] = answer;
            if (activeStep.value < questions.length - 1) {
                activeStep.value++;
            } else {
                activeStep.value++;
            }
        },
        calculateScore() {
            let correctCount = 0;
            questions.forEach(question => {
            if (userAnswers[question.id] === question.correct_choice) {
                correctCount++;
            }
            });
            return ((correctCount / questions.length) * 100).toFixed(2);
        },
    },
    mounted() {
        // Code to run when the component is mounted goes here
    },
};
  </script>
  
  <style>
  #app {
    margin: 20px;
  }
  </style>