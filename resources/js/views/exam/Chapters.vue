<template>
    <div class="app-container">
        <div class="filter-container">
            <head-controls>
                    <el-row>
                    <el-col :span="12">
                        <el-row :gutter="20">
                            <el-col :span="8">
                                <el-form-item>
                                    <el-select v-model="query.filter.class_id" placeholder="Select Class" @change="getSubjects">
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
                                <el-select v-model="query.filter.subject_id" placeholder="Select Subject" no-data-text="Select class first" @change="getChapters()">
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
                    <el-tooltip content="Add Options" placement="top">
                        <el-button type="primary" @click="addOptions(scope.row.id)">
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
                </template>
            </el-table-column>
        </el-table>
        <add-online-question v-if="drawerAddQuestion" :drawerAddQuestion="drawerAddQuestion" @closeAddQuestion="closeAddQuestion" />
        <add-chapter v-if="drawerAddChapter" :chapterId="chapter_id" :drawerAddChapter="drawerAddChapter" @closeAddChapter="closeAddChapter" />
        
    </div>
</template>

<script>
import AddOnlineQuestion from './components/AddOnlineQuestion.vue';
import AddChapter from './components/AddChapter.vue';
import HeadControls from '@/components/HeadControls.vue';
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