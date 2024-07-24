<template>
    <div class="app-container">
        <div class="filter-container">
            <head-controls>
                    <el-row>
                    <el-col :span="12">
                        <el-row :gutter="20">
                            <el-col :span="8">
                                <el-form-item>
                                    <el-select v-model="query.filter.class_id" clearable placeholder="Select Class" @change="getSubjects">
                                        <el-option 
                                            v-for="stdclass in classes"
                                            :key="stdclass.id"
                                            :label="stdclass.name"
                                            :value="stdclass.id"
                                        />
                                    </el-select>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-select v-model="query.filter.subject_id" clearable placeholder="Select Subject" no-data-text="Select class first" @change="getChapters()">
                                    <el-option 
                                        v-for="subject in subjects"
                                        :key="subject.id"
                                        :label="subject.title"
                                        :value="subject.id"
                                    />
                                </el-select>
                            </el-col>
                        </el-row>
                    </el-col>
                    <el-col :span="12">
                        <el-row :gutter="20" justify="end">
                            <el-col :span="6" :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                <el-button type="primary" @click="addChapter(null)" >Add Chapter</el-button>
                            </el-col>
                        </el-row>                    
                    </el-col>
                </el-row>
            </head-controls>
        </div>
        <el-table :data="chapters" style="width: 100%">
            <el-table-column prop="class.name" label="Class"></el-table-column>
            <el-table-column prop="subject.title" label="Subject"></el-table-column>
            <el-table-column prop="title" label="Chapter"></el-table-column>
            <el-table-column>
                <template #default="scope">
                    <el-button-group class="ml-4">
                        <el-tooltip content="Take Test" placement="top">
                            <el-button type="primary" @click="getTestLink(scope.row.id)">
                            <el-icon><Aim /></el-icon>
                            </el-button>
                         </el-tooltip>
                        <el-tooltip content="Add Questions" placement="top">
                            <el-button type="primary" @click="addQuestions(scope.row.id)">
                            <el-icon><SuccessFilled /></el-icon>
                            </el-button>
                         </el-tooltip>
                        <el-tooltip content="Edit Chapter" placement="top">
                            <el-button type="primary" @click="addChapter(scope.row.id)">
                            <el-icon><Edit /></el-icon>
                            </el-button>
                        </el-tooltip>

                        <el-tooltip content="Delete Test" placement="top">
                            <el-button type="danger" @click="deleteQuestions(scope.row.id)">
                            <el-icon><Delete /></el-icon>
                            </el-button>
                    </el-tooltip>
                    </el-button-group>
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
        <add-online-question v-if="drawerAddQuestion" :drawerAddQuestion="drawerAddQuestion" @closeAddQuestion="closeAddQuestion" />
        <add-chapter v-if="drawerAddChapter" :chapterId="chapter_id" :drawerAddChapter="drawerAddChapter" @closeAddChapter="closeAddChapter" />
        
    </div>
</template>

<script>
import Pagination from '@/components/Pagination/index.vue';
import AddOnlineQuestion from './components/AddOnlineQuestion.vue';
import AddChapter from './components/AddChapter.vue';
import HeadControls from '@/components/HeadControls.vue';
import { ElMessage, ElMessageBox } from 'element-plus'
import resource from '@/api/resource';
const chapters = new resource('chapters');
const classes = new resource('classes');
const subjects = new resource('subjects');

export default {
    name: 'OnlineTestAdmin',
    components:{ AddOnlineQuestion, AddChapter, HeadControls },
    data() {
        return {
            loading: false,
            chapter_id: null,
            drawerAddChapter: false,
            drawerAddQuestion: false,
            chapters: [],
            classes: [],
            subjects: [],
            total: 0,
            query: {
                filter: {
                    class_id: '',
                    subject_id: '',
                },
            },
            subject_query: {
                filter:{},
            },
        };
    },
    created() {
        this.getChapters();
        this.getClasses();

    },
    methods: {
        async getChapters() {
            this.query.include = 'subject,class';
            this.query.filter['subject']
            const { data } = await chapters.list(this.query);
            this.chapters = data.chapters.data;
            this.total = data.chapters.total;

        },
        async getClasses() {
            const { data } = await classes.list();
            this.classes = data.classes.data;
        },
        async getSubjects() {
            this.subject_query.filter['id'] =  this.query.class_id;
            const { data } = await subjects.list(this.class_subject);
            this.subjects = data.subjects.data;
        }, 
        addChapter(id = null) {
            this.chapter_id = id;
            this.drawerAddChapter = true;
        },
        deleteQuestions(id) {
            this.$confirm('Are you sure you want to delete this question?', 'Confirm', {
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                type: 'warning',
            }).then(async () => {
                await chapters.destroy(id);
                this.getChapters();
            }).catch(() => {
                // Canceled
            });
        },
        getTestLink(id) {
            ElMessageBox.prompt('Enter number of question', 'Test Link', {
                confirmButtonText: 'Open Link',
                cancelButtonText: 'Cancel',
                inputPattern: /^[0-9]+$/,
                inputErrorMessage: 'Invalid number',
            })
            .then(({ value }) => {
                const routeData = this.$router.resolve(`/take-test/${id}/${value}`);
                window.open(routeData.href, '_blank');
            })
            .catch(() => {
                
            })
        },
        addQuestions(id) {
            this.$router.push(`/exam/chapter_options/${id}`);

        },
        closeAddQuestion() {
            this.drawerAddQuestion = false;
        },
        closeAddChapter() {
            this.drawerAddChapter = false;
            this.getChapters();
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