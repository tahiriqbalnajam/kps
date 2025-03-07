<template>
  <el-row :gutter="40" class="panel-group">
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel">
        <div class="card-panel-icon-wrapper person-badge">
          <icon class-name="person-badge card-panel-icon"/>
        </div>
        <div class="card-panel-description">
          <div class="card-panel-text">Total Students</div>
          <span class="card-panel-num">{{ data.total_students }}</span>
          <CountTo :start-val="0" :end-val="data.total_students" class="card-panel-num" />
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel">
        <div class="card-panel-icon-wrapper person-bounding-box">
          <icon class-name="person-bounding-box card-panel-icon"/>
        </div>
        <div class="card-panel-description">
          <div class="card-panel-text">Total Teachers</div>
          <span class="card-panel-num">{{ data.total_teachers }}</span>
          <CountTo :start-val="0" :end-val="data.total_teachers" class="card-panel-num" />
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel">
        <div class="card-panel-icon-wrapper person-dash-fill">
          <icon class-name="person-dash-fill card-panel-icon"/>
        </div>
        <div class="card-panel-description">
          <div class="card-panel-text">Absent Students</div>
          <span class="card-panel-num">{{ data.total_absent_students }}</span>
          <CountTo :start-val="0" :end-val="data.total_absent_students" class="card-panel-num" />
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel">
        <div class="card-panel-icon-wrapper person-fill-dash">
          <icon class-name="person-fill-dash card-panel-icon"/>
        </div>
        <div class="card-panel-description">
          <div class="card-panel-text">Absent Teachers</div>
          <span class="card-panel-num">
            <el-tooltip placement="top">
              <template #content>
                <div v-for="teacher in data.absent_teachers" :key="teacher">
                  {{ teacher }}
                </div>
              </template>
              {{ data.total_absent_teachers }}
            </el-tooltip>       
          </span>
          <CountTo :start-val="0" :end-val="data.total_absent_teachers" class="card-panel-num" />
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel" style="height: 400px;">
          <div class="panel-heading">Student Birthdays</div>
            <el-table :data="data.student_birthdays" max-height="250">
              <el-table-column prop="name" label="Name"></el-table-column>
              <el-table-column prop="class" label="Class"></el-table-column>
              <el-table-column label="Age">
                <template #default="scope">
                  {{ new Date().getFullYear() - new Date(scope.row.dob).getFullYear() }} years
                </template>
              </el-table-column>
            </el-table>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel" style="height: 400px;">
          <div class="panel-heading">New Admissions ({{ data.newAdmissions }})</div>
            <el-table :data="data.newAdmissionsPerClass[0]" max-height="250">
              <el-table-column prop="class_name" label="Class"></el-table-column>
              <el-table-column prop="count" label="Total"></el-table-column>
            </el-table>
      </div>
    </el-col>
  </el-row>
</template>

<script setup>
import Resource from '@/api/resource';
let dashRes = new Resource('dashboard');
import { ref } from 'vue';

const getData = async () => {
  try {
    const response = await dashRes.list(); // Assuming there is a method called getData in the dashRes object
    return response.data;
  } catch (error) {
    console.error(error);
    return null;
  }
};

const data = ref({total_students: 0, total_absent_students: 0, total_teachers: 0, total_absent_teachers: 0, 
                  absent_teachers: [], student_birthdays: [], newAdmissionsPerClass: [], newAdmissions: 0});


getData().then((result) => {
  if (result) {
    data.value = result;
  }
});

const emit = defineEmits(['handleSetLineChartData'])
const handleSetLineChartData = (type) => {
  emit('handleSetLineChartData', type)
}
</script>

<style lang="scss" scoped>
.panel-group {
  margin-top: 18px;

  .flex-container {
    display: flex;
    flex-direction: column;
    height: 100%;
  }

  .flex-item {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .full-height {
    flex: 1;
  }

  .half-height {
    flex: 0.5;
  }

  .card-panel-col {
    margin-bottom: 32px;
  }

  .card-panel {
    height: 108px;
    cursor: pointer;
    font-size: 12px;
    position: relative;
    overflow: hidden;
    color: #666;
    background: #fff;
    box-shadow: 4px 4px 40px rgba(0, 0, 0, 0.05);
    border-color: rgba(0, 0, 0, 0.05);
    border-radius: 5px;

    &:hover {
      box-shadow: 0 5px 5px 0px #ccc;
      .card-panel-icon-wrapper {
        color: #fff;
      }

      .person-fill-dash {
        background: #40c9c6;
      }

      .person-dash-fill {
        background: #36a3f7;
      }

      .person-bounding-box {
        background: #f4516c;
      }

      .person-badge {
        background: #34bfa3;
      }
    }

    .person-fill-dash {
      color: #40c9c6;
    }

    .person-dash-fill {
      color: #36a3f7;
    }

    .person-bounding-box {
      color: #f4516c;
    }

    .person-badge {
      color: #34bfa3;
    }

    .card-panel-icon-wrapper {
      float: left;
      margin: 14px 0 0 14px;
      padding: 16px;
      transition: all 0.38s ease-out;
      border-radius: 6px;
    }

    .card-panel-icon {
      float: left;
      font-size: 48px;
    }

    .card-panel-description {
      float: right;
      font-weight: bold;
      margin: 26px;
      margin-left: 0px;

      .card-panel-text {
        line-height: 18px;
        color: rgba(0, 0, 0, 0.45);
        font-size: 16px;
        margin-bottom: 12px;
      }

      .card-panel-num {
        font-size: 20px;
      }
    }
  }
}

@media (max-width: 550px) {
  .card-panel-description {
    display: none;
  }

  .card-panel-icon-wrapper {
    float: none !important;
    width: 100%;
    height: 100%;
    margin: 0 !important;

    .svg-icon {
      display: block;
      margin: 14px auto !important;
      float: none !important;
    }
  }
}
.panel-heading {
  font-size: 18px;
  font-weight: bold;
  text-align: center;
  padding: 10px 0;
}
</style>
