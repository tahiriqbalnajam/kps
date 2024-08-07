<template>
    <div id="app">
      <el-steps :active="activeStep" finish-status="success" align-center>
        <el-step v-for="(question, index) in questions" :key="index" :title="'Q# ' + (index + 1)" />
        <el-step title="result" :icon="Picture" />
      </el-steps>
  
      <div v-if="activeStep < questions.length">
        <QuestionCard
          :question="questions[activeStep]"
          @answer-selected="handleAnswerSelected"
        />
      </div>      
      <div v-else>
        <Result :score="calculateScore()" :questions="questions" :userAnswers="userAnswers" />
      </div>
    </div>

  </template>
  
  <script>
  import { Edit, Picture, Upload } from '@element-plus/icons-vue'
  import { filter } from 'lodash';
  import ChapterList from './components/Chapters.vue';
  import QuestionCard from './components/QuestionCard.vue';
  import Result from './components/ResultCard.vue';
  import Resource from '@/api/resource';
  const chapters  = new Resource('chapters');
  const questions  = new Resource('questions');

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
                filter:{},
                sort:'',
                limit:10,

            }
            
        };
    },
    created() {
        this.handleChapterSelected();
    },
    methods: {

        async handleChapterSelected () {
            this.question_query.filter['chapter_id'] = this.$route.params.chapterid;
            this.question_query.filter['lang'] = this.$route.params.lang;
            this.question_query.sort = 'random';
            this.question_query.limit = this.$route.params.question;
            const { data } = await questions.list(this.question_query);
            this.questions = data.questions.data;
         },
         handleAnswerSelected(questionId, answer){
            this.userAnswers[questionId] = answer;
            if (this.activeStep < questions.length - 1) {
                this.activeStep++;
            } else {
                this.activeStep++;
            }
        },
        calculateScore() {
            let correctCount = 0;
            this.questions.forEach(question => {
              if (this.userAnswers[question.id] === question[question.correct_choice]) {
                  correctCount++;
              }
            });
            return ((correctCount / this.questions.length) * 100).toFixed(2);
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