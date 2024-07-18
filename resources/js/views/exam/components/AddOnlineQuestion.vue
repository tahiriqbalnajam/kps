<template>
    <el-drawer title="Add Chapter" modelValue="drawerAddQuestion" size="95%" :before-close="handleClose">
        <el-row :gutter="20" justify="start" style="margin-bottom: 20px;">
                <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl ="6">
                    <el-select v-model="query.class_id" value-key="" placeholder="Select Class" clearable filterable @change="getSubjects()">
                        <el-option v-for="stdclass in classes"
                            :key="stdclass.id"
                            :label="stdclass.name"
                            :value="stdclass.id">
                        </el-option>
                    </el-select>
                </el-col>
                <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl ="6">
                    <el-select v-model="query.subject_id" value-key="" placeholder="Select Subject" clearable filterable @change="getChapters()">
                        <el-option v-for="subject in subjects"
                            :key="subject.id"
                            :label="subject.title"
                            :value="subject.id">
                        </el-option>
                    </el-select>
                </el-col>
                <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl ="6">
                    <el-select v-model="chapter_question.chapter_id" value-key="" placeholder="Select Subject" clearable filterable @change="getChapters()">
                        <el-option v-for="chapter in chapters"
                            :key="chapter.id"
                            :label="chapter.title"
                            :value="chapter.id">
                        </el-option>
                    </el-select>
                </el-col>
                <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl ="6">
                    <el-button type="primary" icon="el-icon-plus" @click="addQuestionGroup">Add Question Group</el-button>
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
                            <el-button type="danger" icon="el-icon-delete" @click="removeQuestionGroup(index)">Remove</el-button>
                        </el-col>
                </el-row>
            </el-form>
            <template #footer>
                <div style="flex: auto">
                    <el-button @click="handleClose">
                    Cancel
                    </el-button>
                    <el-button type="primary" :loading="loading" @click="handleSubmit()">
                        Add Questions
                    </el-button>
                </div>
            </template>
        </el-drawer>
</template>

<script>
import { QuestionFilled } from '@element-plus/icons-vue'
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
/* Your component's CSS styles go here */
</style>