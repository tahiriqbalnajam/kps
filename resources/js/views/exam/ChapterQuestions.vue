<template>
    <div class="app-container">
        <div class="filter-container">
            <head-controls>
                <el-row>
                    <el-col :span="12">
                       <h3>Chapter:{{ questions[0].chapter.title }}</h3>
                    </el-col>
                    <el-col :span="12">
                        <el-row :gutter="20" justify="end">
                            <el-col :span="6" :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                <el-button type="primary" @click="addQuestion()" >Add Question</el-button>
                            </el-col>
                        </el-row>
                        
                    </el-col>
                </el-row> 
            </head-controls>
        </div>
        <el-table :data="questions" style="width: 100%" max-height="600" size="small">
            <el-table-column type="index"/>
            <el-table-column prop="question_text" label="Question"></el-table-column>
            <el-table-column prop="choice_1" label="Choice 1"></el-table-column>
            <el-table-column prop="choice_2" label="Choice 2"></el-table-column>
            <el-table-column prop="choice_3" label="Choice 3"></el-table-column>
            <el-table-column prop="choice_4" label="Choice 4"></el-table-column>
            <el-table-column prop="correct_choice" label="Correct"></el-table-column>
            <el-table-column>
                <template #default="scope">
                    <el-tooltip content="Delete Test" placement="top">
                        <el-button type="danger" @click="deleteQuestions(scope.row.id)">
                        <el-icon><Delete /></el-icon>
                        </el-button>
                    </el-tooltip>
                </template>
            </el-table-column>
        </el-table>
        <div class="demo-pagination-block">
            <el-pagination
                v-show="total>0"
                v-model:current-page="query.page"
                v-model:page-size="query.limit"
                :page-sizes="[10, 15, 20, 30, 50, 100]"
                :small="small"
                :disabled="disabled"
                background="white"
                layout="total, sizes, prev, pager, next, jumper"
                :total="total"
                @size-change="handleSizeChange"
                @current-change="handleCurrentChange"
            />
        </div>
        <add-online-question v-if="drawerAddQuestion" :chapterId="chapterid" :drawerAddQuestion="drawerAddQuestion" @closeAddQuestion="closeAddQuestion" />
        
    </div>
</template>

<script>
import Pagination from '@/components/Pagination/index.vue';
import AddOnlineQuestion from './components/AddOnlineQuestion.vue';
import HeadControls from '@/components/HeadControls.vue';
import resource from '@/api/resource';
const questions = new resource('questions');
export default {
    name: 'OnlineTestAdmin',
    components:{ AddOnlineQuestion,  HeadControls },
    data() {
        return {
            loading: false,
            drawerAddChapter: false,
            drawerAddQuestion: false,
            questions: [],
            chapterid: '',
            total: 0,
            query: {
                filter:{}
            }
            
        };
    },
    created() {
        this.getQuestions();
    },
    methods: {
        async getQuestions() {
            this.chapterid = this.$route.params.id;
            this.query.filter['chapter_id'] = this.$route.params.id;
            const { data } = await questions.list(this.query);
            this.questions = data.questions.data;
            this.total = data.questions.total;

        },
        async handleSizeChange (val) {
            this.query.limit = val
            await this.getQuestions()
        },
        async handleCurrentChange (val) {
            this.query.page = val
            await this.getQuestions()
        },
        deleteQuestions(id) {
            this.$confirm('Are you sure you want to delete this question?', 'Confirm', {
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                type: 'warning',
            }).then(async () => {
                await questions.destroy(id);
                this.getQuestions();
            }).catch(() => {
                // Canceled
            });
        },
        addQuestion() {
            this.chapterid = this.$route.params.id;
            this.drawerAddQuestion = true;
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