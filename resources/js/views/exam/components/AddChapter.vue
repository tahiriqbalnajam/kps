<template>
    <el-drawer title="Add Chapter" modelValue="drawerAddChapter" :before-close="handleClose">
        <el-form ref="chapter" :model="chapter" label-width="120px">
            <el-form-item label="Class">
                <el-select v-model="chapter.class_id" placeholder="Select Class" @change="getSubjects">
                    <el-option 
                        v-for="stdclass in classes"
                        :key="stdclass.id"
                        :label="stdclass.name"
                        :value="stdclass.id"
                    />
                </el-select>
            </el-form-item>
            <el-form-item label="Subject">
                <el-select v-model="chapter.subject_id" placeholder="Select Subject">
                    <el-option 
                        v-for="subject in subjects"
                        :key="subject.id"
                        :label="subject.title"
                        :value="subject.id"
                    />
                </el-select>
            </el-form-item>
            <el-form-item label="Chapter Name">
                <el-input v-model="chapter.title" placeholder="Enter Chapter Name"></el-input>
            </el-form-item>
        </el-form>
        <template #footer>
            <div style="flex: auto">
                <el-button @click="handleClose">
                Cancel
                </el-button>
                <el-button type="primary" :loading="loading" @click="saveChapter()">
                    Add Questions
                </el-button>
            </div>
        </template>
    </el-drawer>
</template>

<script>
import resource from '@/api/resource';
const classes = new resource('classes');
const subjectRes = new resource('subject_class');
const chapter = new resource('chapters');
export default {
    name: 'AddChapter',
    emits: ['closeAddChapter'],
    props: {
        drawerAddChapter: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            classes: {},
            chapter: {
                class_id: '',
                subject_id: '',
                title: '',
            },
            subjects: {},
            class_subject: {
                filter: {},
            },
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
            this.class_subject.filter['id'] =  this.chapter.class_id;
            const subjectdata = await subjectRes.list(this.class_subject);
            this.subjects = subjectdata.data.classubj.data[0].subjects;
        },
        async saveChapter() {
            await chapter.store(this.chapter);
            this.$message({
                  message:
                    'New Chapter  ' +
                    this.chapter.title +
                    ' has been created successfully.',
                  type: 'success',
                  duration: 5 * 1000,
                });
            this.handleClose();
        },
        handleClose() {
            this.$emit('closeAddChapter');
            this.drawerAddChapter = false;
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