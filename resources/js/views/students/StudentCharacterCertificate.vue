
<template>
    <div class="app-container">
        <div class="filter-container">
            <head-controls>
                <el-form-item>
                    <el-col :span="6">
                        Search Student:
                        <el-select
                            v-model="rdata.query.filter.student_id"
                            placeholder="Search student"
                            filterable
                            remote
                            reserve-keyword
                            :remote-method="searchStudent"
                            :loading="rdata.loading"
                        >
                            <el-option
                                v-for="item in rdata.students"
                                :key="item.id"
                                :label="item.name"
                                :value="item.id"
                            />
                        </el-select>
                    </el-col>
                </el-form-item>
            </head-controls>
        </div>
        <!-- Your other HTML content here -->
       
    </div>
</template>
        

<script>
import { ref, reactive, computed } from 'vue';
import Resource  from '@/api/resource'; 
import HeadControls from '@/components/HeadControls.vue';
export default {
    name: 'YourComponentName',
    components: {
        HeadControls,
    },
    setup() {
        const resource = new Resource('students');
        const rdata = reactive({
            students: [],
            searchText:'',
            loading: false,
            query: {
                page: 1,
                per_page: 10,
                filter: {},
            }
        });

        // Define computed properties
        const computedProperty = computed(() => {
            // Your computed property logic here
        });

        // Define methods
        const method = () => {
            // Your method logic here
        };

        // Define watchers
        watch(() => {
            // Your watcher logic here
        });
        const searchStudent = async(searchword) => {
            rdata.loading = true;
            rdata.query.filter.name = searchword;
            const { data }  = await resource.list(rdata.query);
            rdata.students = data.students.data;
            // Perform the search logic here
            // You can use the searchText value to filter the items or make an API call
        };


        return {
            rdata,
            searchStudent,
            HeadControls,
            resource,
        };
    },
};
</script>

<style scoped>
/* Your component styles here */
</style>
