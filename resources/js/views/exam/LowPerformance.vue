<template>
  <div class="app-container">
    <div class="filter-container">
      <div class="filter-items">
        <el-tree-select 
          check-strictly
          v-model="filter.class_id" 
          :data="classList"
          placeholder="Select Class" 
          clearable 
          class="filter-item class-select" 
          @change="fetchData"
        />
        
        <el-input 
          v-model="filter.threshold" 
          placeholder="Threshold (%)" 
          type="number" 
          class="filter-item input-small" 
          @change="fetchData"
        >
          <template #append>%</template>
        </el-input>
        
        <el-button class="filter-item" type="primary" :icon="Search" @click="fetchData">
          Filter
        </el-button>
      </div>
    </div>

    <div v-loading="loading">
      <div v-if="Object.keys(groupedData).length === 0" class="empty-data">
        <el-empty description="No low performance students found with current filters" />
      </div>

      <div v-else>
        <el-card v-for="(students, className) in groupedData" :key="className" class="box-card" style="margin-bottom: 20px;">
          <template #header>
            <div class="clearfix">
              <span class="class-header"><el-icon><School /></el-icon> {{ className }}</span>
              <el-tag type="danger" effect="dark" style="float: right">{{ students.length }} Students At Risk</el-tag>
            </div>
          </template>
          
          <el-table :data="students" style="width: 100%" stripe border>
            <el-table-column prop="student_name" label="Student Name" width="200">
               <template #default="scope">
                 <div class="student-name-cell">
                   <el-avatar size="small" :style="{ backgroundColor: stringToColor(scope.row.student_name) }">
                     {{ scope.row.student_name.charAt(0).toUpperCase() }}
                   </el-avatar>
                   <span style="margin-left: 10px">{{ scope.row.student_name }}</span>
                 </div>
               </template>
            </el-table-column>
            
            <el-table-column label="Subject Performance">
              <template #default="scope">
                <div class="subject-tags">
                  <el-tag 
                    v-for="(subject, index) in scope.row.subjects" 
                    :key="index"
                    :type="getScoreType(subject.average_score)"
                    effect="plain"
                    class="subject-tag"
                  >
                    {{ subject.subject_name }}: {{ subject.average_score }}%
                  </el-tag>
                </div>
              </template>
            </el-table-column>
            
            <el-table-column label="Action" width="120" align="center">
              <template #default="scope">
                 <el-button type="primary" link size="small" @click="viewStudent(scope.row.student_id)">
                   View Profile
                 </el-button>
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Resource from '@/api/resource';
import request from '@/utils/request';
import { useRouter } from 'vue-router';
import { School, Search } from '@element-plus/icons-vue';

const router = useRouter();
const classResource = new Resource('classes');
const loading = ref(false);
const classList = ref([]);
const groupedData = ref({});

const filter = ref({
  class_id: '',
  threshold: 50
});

const getClasses = async () => {
  const { data } = await classResource.list({ include: 'sections' });
  classList.value = data.classes.data.map(classItem => {
    const classNode = {
      value: `class_${classItem.id}`,
      label: classItem.name,
    };
    if (classItem.sections && classItem.sections.length > 0) {
      classNode.children = classItem.sections.map(section => ({
        value: `section_${section.id}`,
        label: section.name,
      }));
    }
    return classNode;
  });
};

const fetchData = async () => {
  loading.value = true;
  try {
    const params = { threshold: filter.value.threshold };
    if (filter.value.class_id) {
      const selectedValue = filter.value.class_id.toString();
      if (selectedValue.startsWith('class_')) {
        params.class_id = selectedValue.split('_')[1];
      } else if (selectedValue.startsWith('section_')) {
        params.section_id = selectedValue.split('_')[1];
      }
    }
    const response = await request({
      url: '/tests/low-performance-students',
      method: 'get',
      params
    });
    groupedData.value = response.data;
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const getScoreType = (score) => {
  if (score < 33) return 'danger';
  if (score < 50) return 'warning';
  return 'info';
};

const stringToColor = (str) => {
  let hash = 0;
  for (let i = 0; i < str.length; i++) {
    hash = str.charCodeAt(i) + ((hash << 5) - hash);
  }
  const c = (hash & 0x00ffffff).toString(16).toUpperCase();
  return '#' + '00000'.substring(0, 6 - c.length) + c;
};

const viewStudent = (id) => {
    // Navigate to student profile/report page if available. 
    // Assuming a route /students/report/:id exists or similar
    router.push(`/students/report/${id}`);
}

onMounted(() => {
  getClasses();
  fetchData();
});
</script>

<style scoped lang="scss">
.app-container {
  padding: 20px;
  background-color: #f0f2f5;
  min-height: calc(100vh - 84px);
}

.filter-container {
  background: white;
  padding: 15px 20px;
  border-radius: 8px;
  box-shadow: 0 1px 4px rgba(0,21,41,0.08);
  margin-bottom: 20px;
  
  .filter-items {
    display: flex;
    align-items: center;
    gap: 15px; // Space between items
    
    .filter-item {
      margin-bottom: 0; // Override default margin if exists
    }
    
    .input-small {
      width: 150px;
    }
    
    .class-select {
        width: 220px;
    }
  }
}

.box-card {
  border-radius: 8px;
  
  .class-header {
    font-size: 18px;
    font-weight: bold;
    color: #303133;
    display: flex;
    align-items: center;
    gap: 8px;
  }
}

.student-name-cell {
  display: flex;
  align-items: center;
}

.subject-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  
  .subject-tag {
    font-weight: 500;
  }
}

.empty-data {
  background: white;
  border-radius: 8px;
  padding: 40px;
  text-align: center;
}
</style>
