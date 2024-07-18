<template>
    <div class="app-container">
        <div class="filter-container">
            <head-controls>
                <el-row>
                    <el-col :span="12">
                        <!-- Content for the first column -->
                    </el-col>
                    <el-col :span="12">
                        <el-row :gutter="20" justify="end">
                            <el-col :span="6" :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                <el-button type="primary" @click="drawerAddQuestion = true" >Add Question</el-button>
                            </el-col>
                            <el-col :span="6" :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                <el-button type="primary" @click="drawerAddChapter = true" >Add Chapter</el-button>
                            </el-col>
                        </el-row>
                        
                    </el-col>
                </el-row> 
            </head-controls>
        </div>
        <el-table :data="questions" style="width: 100%">
            <el-table-column prop="chapter.title" label="Chapter"></el-table-column>
            <el-table-column prop="question_text" label="Question"></el-table-column>
            <el-table-column prop="choice_1" label="Choice 1"></el-table-column>
            <el-table-column prop="choice_2" label="Choice 2"></el-table-column>
            <el-table-column prop="choice_3" label="Choice 3"></el-table-column>
            <el-table-column prop="choice_4" label="Choice 4"></el-table-column>
            <el-table-column prop="correct_choice" label="Correct"></el-table-column>
        </el-table>
        <add-online-question v-if="drawerAddQuestion" :drawerAddQuestion="drawerAddQuestion" @closeAddQuestion="closeAddQuestion" />
        <add-chapter v-if="drawerAddChapter" :drawerAddChapter="drawerAddChapter" @closeAddChapter="closeAddChapter" />
        
    </div>
</template>

<script>
import AddOnlineQuestion from './components/AddOnlineQuestion.vue';
import AddChapter from './components/AddChapter.vue';
import HeadControls from '@/components/HeadControls.vue';
import resource from '@/api/resource';
const questions = new resource('questions');
export default {
    name: 'OnlineTestAdmin',
    components:{ AddOnlineQuestion, AddChapter, HeadControls },
    data() {
        return {
            loading: false,
            drawerAddChapter: false,
            drawerAddQuestion: false,
            questions: [],
            
        };
    },
    created() {
        this.getQuestions();
    },
    methods: {
        async getQuestions() {
            const { data } = await questions.list();
            this.questions = data.questions.data;
        },
        closeAddQuestion() {
            this.drawerAddQuestion = false;
        },
        closeAddChapter() {
            this.drawerAddChapter = false;
        },
    },
    mounted() {
        // Code to run when the component is mounted goes here
    },
};
</script>

<style scoped>
/* Your component styles go here */
</style>