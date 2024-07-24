<template>
    <el-drawer title="Add Chapter" modelValue="drawerAddQuestion" size="95%" :before-close="handleClose">
        <el-row :gutter="20" justify="start" class="header">
                <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl ="6">

                </el-col>
                <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl ="6">
                    <el-row :gutter="20" justify="end">
                        <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl ="6">
                            <el-button type="primary" @click="addQuestionGroup"><el-icon><Plus /></el-icon> Add Question Group</el-button>
                        </el-col>
                    </el-row>
                </el-col>
            </el-row>

            <el-form :model="chapter_question" :rules="questionGroupRules" ref="questionGroupForm" label-width="120px">
                <el-row v-for="(question, index) in chapter_question.questions" :key="index" :gutter="20" style="margin-bottom: 20px;">

                    <el-col :span="6">
                        <el-input v-model="question.question_text" placeholder="Enter Question"></el-input>
                    </el-col>
                    <el-radio-group v-model="question.correct_choice">
                        <el-col :span="4">
                            <el-radio value="choice_1" size="large" border>
                                <el-input v-model="question.choice_1" placeholder="Enter First Answer" />
                            </el-radio>
                        </el-col>
                        <el-col :span="4">
                            <el-radio value="choice_2" size="large" border>
                                <el-input v-model="question.choice_2" placeholder="Enter Second Answer" />
                            </el-radio>
                        </el-col>
                        <el-col :span="4">
                            <el-radio value="choice_3" size="large" border>
                            <el-input v-model="question.choice_3" placeholder="Enter Third Answer" />
                            </el-radio>
                        </el-col>
                        <el-col :span="4">
                            <el-radio value="choice_4" size="large" border>
                                <el-input v-model="question.choice_4" placeholder="Enter Fourth Answer" />
                            </el-radio>
                        </el-col>
                    </el-radio-group>
                        <el-col :span="2">
                            <el-button type="danger" @click="removeQuestionGroup(index)">
                                <el-icon><Close /></el-icon>
                            </el-button>
                        </el-col>
                </el-row>
            </el-form>
            <template #footer>
                <div style="flex: auto">
                    <el-button @click="handleClose">
                    Cancel
                    </el-button>
                    <el-button type="primary" :loading="loading" @click="handleSubmit()">
                        Save Questions
                    </el-button>
                </div>
            </template>
        </el-drawer>
</template>

<script>
import { QuestionFilled, Close, Plus } from '@element-plus/icons-vue'
import resource from '@/api/resource';
const classes = new resource('classes');
const subjectRes = new resource('subject_class');
const chapter = new resource('chapters');
const question = new resource('questions');
export default {
    name: 'ComponentName',
    emits: ['closeAddQuestion'],
    props: {
        drawerAddQuestion: {
            type: Boolean,
            default: false,
        },
        chapterId: {
            type: Number,
            default: null
        }
    },
    data() {
        return {
            classes: [],
            subjects: [],
            chapters: [],
            chapter_question: {
                chapter_id: '',
                questions: [],
            },
            questionGroups: [],
            question: {
                question_text: '',
                choice_1: '',
                choice_2: '',
                choice_3: '',
                choice_4: '',
                correct_choice: '',
            },
            query: {
                class_id: '',
                subject_id: '',
                filter: {},
            },
            chapter_query: {
                filter: {},
            }
            // Your component's data goes here
        };
    },
    created() {
       this.getClasses();
       alert(this.chapterId);
       this.chapter_question.chapter_id = this.chapterId;
    },
    methods: {
        async getClasses() {
            const { data } = await classes.list();
            this.classes = data.classes.data;
        },
        async getSubjects() {
            this.query.filter['id'] =  this.query.class_id;
            const subjectdata = await subjectRes.list(this.query);
            this.subjects = subjectdata.data.classubj.data[0].subjects;
        },
        async getChapters() {
            this.chapter_query.filter['subject_id'] =  this.query.subject_id;
            this.chapter_query.filter['class_id'] = this.query.class_id;
            const { data } = await chapter.list(this.chapter_query);
            this.chapters = data.chapters.data;
        },
        addQuestionGroup() {
            this.chapter_question.questions.push(
                {
                    question_text: '',
                    choice_1: '',
                    choice_2: '',
                    choice_3: '',
                    choice_4: '',
                    correct_choice: '',
                }
            );
        },
        removeQuestionGroup(index) {
            this.chapter_question.questions.splice(index, 1);
        },
        handleClose() {
            this.$emit('closeAddQuestion');
            this.drawerAddQuestion = false;
        },
        async handleSubmit() {
            this.chapter_question.chapter_id = this.chapterId;
            await question.store(this.chapter_question);
            this.$message({
                message: 'Questions added successfully',
                type: 'success',
            });
            this.handleClose();
        },
    },
    mounted() {
        // Code to run when the component is mounted goes here
    },
};
</script>

<style scoped>
.header {
    margin-left: -10px;
    margin-right: -10px;
    margin-bottom: 40px;
    border: 1px solid #cccccc85;
    padding: 10px;
    border-radius: 5px;
    background: white;
    box-shadow: 0px 1px 6px #ccc;
}
</style>