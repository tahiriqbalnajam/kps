<template>
  <div class="panel-group">
    <!-- Header with animated gradient -->

    <!-- Stats Cards with improved responsiveness -->
    <el-row :gutter="16" class="stats-row">
      <el-col :xs="24" :sm="12" :md="6" class="card-panel-col">
        <div 
          class="card-panel stats-card students-card"
          @click="handleCardClick('students')"
          :class="{ 'card-active': activeCard === 'students' }"
        >
          <div class="card-gradient"></div>
          <div class="card-content">
            <div class="card-icon">
              <el-icon><User /></el-icon>
            </div>
            <div class="card-info">
              <div class="card-title">Total Students</div>
              <div class="card-number">
                <span class="counter-animation">{{ animatedStudents }}</span>
              </div>
              <div class="card-trend">
                <el-icon class="trend-icon"><TrendCharts /></el-icon>
                <span>Active Learners</span>
              </div>
            </div>
          </div>
        </div>
      </el-col>

      <el-col :xs="24" :sm="12" :md="6" class="card-panel-col">
        <div 
          class="card-panel stats-card teachers-card"
          @click="handleCardClick('teachers')"
          :class="{ 'card-active': activeCard === 'teachers' }"
        >
          <div class="card-gradient"></div>
          <div class="card-content">
            <div class="card-icon">
              <el-icon><Avatar /></el-icon>
            </div>
            <div class="card-info">
              <div class="card-title">Total Teachers</div>
              <div class="card-number">
                <span class="counter-animation">{{ animatedTeachers }}</span>
              </div>
              <div class="card-trend">
                <el-icon class="trend-icon"><Star /></el-icon>
                <span>Educators</span>
              </div>
            </div>
          </div>
        </div>
      </el-col>

      <el-col :xs="24" :sm="12" :md="6" class="card-panel-col">
        <div 
          class="card-panel stats-card absent-students-card"
          @click="handleCardClick('absent-students')"
          :class="{ 'card-active': activeCard === 'absent-students' }"
        >
          <div class="card-gradient"></div>
          <div class="card-content">
            <div class="card-icon">
              <el-icon><Warning /></el-icon>
            </div>
            <div class="card-info">
              <div class="card-title">Absent Students</div>
              <div class="card-number">
                <span class="counter-animation">{{ animatedAbsentStudents }}</span>
              </div>
              <div class="card-trend">
                <el-icon class="trend-icon"><Clock /></el-icon>
                <span>Today</span>
              </div>
            </div>
          </div>
        </div>
      </el-col>

      <el-col :xs="24" :sm="12" :md="6" class="card-panel-col">
        <div 
          class="card-panel stats-card absent-teachers-card"
          @click="handleCardClick('absent-teachers')"
          :class="{ 'card-active': activeCard === 'absent-teachers' }"
        >
          <div class="card-gradient"></div>
          <div class="card-content">
            <div class="card-icon">
              <el-icon><UserFilled /></el-icon>
            </div>
            <div class="card-info">
              <div class="card-title">Absent Teachers</div>
              <div class="card-number">
                <el-tooltip placement="top" effect="dark">
                  <template #content>
                    <div class="teacher-tooltip">
                      <div v-if="data.absent_teachers.length === 0" class="no-absences">
                        No teacher absences today! 🎉
                      </div>
                      <div v-else>
                        <div class="tooltip-title">Absent Teachers:</div>
                        <div v-for="teacher in data.absent_teachers" :key="teacher" class="teacher-item">
                          • {{ teacher }}
                        </div>
                      </div>
                    </div>
                  </template>
                  <span class="counter-animation">{{ animatedAbsentTeachers }}</span>
                </el-tooltip>
              </div>
              <div class="card-trend">
                <el-icon class="trend-icon"><Clock /></el-icon>
                <span>Today</span>
              </div>
            </div>
          </div>
        </div>
      </el-col>
    </el-row>
    <!-- Information Cards -->
    <el-row :gutter="16" class="info-row">
      <el-col :xs="24" :sm="24" :md="12" :lg="8" class="card-panel-col">
        <div class="info-card birthdays-card">
          <div class="card-header">
            <el-icon class="header-icon"><Present /></el-icon>
            <h3 class="card-header-title">Today's Birthdays</h3>
          </div>
          
          <div class="birthday-section">
            <div class="section-title">
              <el-icon><User /></el-icon>
              Students ({{ data.student_birthdays?.length || 0 }})
            </div>
            <div class="birthday-list" v-if="data.student_birthdays?.length">
              <el-scrollbar max-height="120px">
                <div 
                  v-for="student in data.student_birthdays" 
                  :key="student.name"
                  class="birthday-item student-birthday"
                >
                  <div class="birthday-avatar">
                    <el-icon><UserFilled /></el-icon>
                  </div>
                  <div class="birthday-info">
                    <div class="birthday-name">{{ student.name }}</div>
                    <div class="birthday-details">
                      {{ student.class }} • 
                      {{ new Date().getFullYear() - new Date(student.dob).getFullYear() }} years
                    </div>
                  </div>
                  <div class="birthday-icon">🎂</div>
                </div>
              </el-scrollbar>
            </div>
            <div v-else class="empty-state">
              <el-icon><CircleCheck /></el-icon>
              <span>No student birthdays today</span>
            </div>
          </div>

          <el-divider />

          <div class="birthday-section">
            <div class="section-title">
              <el-icon><Avatar /></el-icon>
              Teachers ({{ data.teacher_birthdays?.length || 0 }})
            </div>
            <div class="birthday-list" v-if="data.teacher_birthdays?.length">
              <el-scrollbar max-height="120px">
                <div 
                  v-for="teacher in data.teacher_birthdays" 
                  :key="teacher.name"
                  class="birthday-item teacher-birthday"
                >
                  <div class="birthday-avatar">
                    <el-icon><Star /></el-icon>
                  </div>
                  <div class="birthday-info">
                    <div class="birthday-name">{{ teacher.name }}</div>
                    <div class="birthday-details">
                      {{ teacher.subject }} • 
                      {{ new Date().getFullYear() - new Date(teacher.dob).getFullYear() }} years
                    </div>
                  </div>
                  <div class="birthday-icon">🎉</div>
                </div>
              </el-scrollbar>
            </div>
            <div v-else class="empty-state">
              <el-icon><CircleCheck /></el-icon>
              <span>No teacher birthdays today</span>
            </div>
          </div>
        </div>
      </el-col>

      <el-col :xs="24" :sm="24" :md="12" :lg="8" class="card-panel-col">
        <div class="info-card admissions-card">
          <div class="card-header">
            <el-icon class="header-icon"><Plus /></el-icon>
            <h3 class="card-header-title">
              New Admissions 
              <el-tag type="success" size="small" round>{{ data.newAdmissions || 0 }}</el-tag>
            </h3>
          </div>
          
          <div class="admissions-content">
            <div v-if="data.newAdmissionsPerClass?.length && data.newAdmissionsPerClass[0]?.length">
              <div class="admissions-chart">
                <div 
                  v-for="admission in data.newAdmissionsPerClass[0]" 
                  :key="admission.class_name"
                  class="admission-bar"
                >
                  <div class="admission-info">
                    <span class="class-name">{{ admission.class_name }}</span>
                    <span class="class-count">{{ admission.count }}</span>
                  </div>
                  <div class="progress-bar">
                    <div 
                      class="progress-fill"
                      :style="{ width: getAdmissionPercentage(admission.count) + '%' }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="empty-state">
              <el-icon><DocumentAdd /></el-icon>
              <span>No new admissions this period</span>
            </div>
          </div>
        </div>
      </el-col>

      <el-col :xs="24" :sm="24" :md="24" :lg="8" class="card-panel-col">
        <div class="info-card at-risk-card">
          <div class="card-header">
            <el-icon class="header-icon warning-icon"><WarningFilled /></el-icon>
            <h3 class="card-header-title">At Risk Students</h3>
          </div>
          
          <div class="at-risk-content">
             <div v-if="data.at_risk_students?.length" class="at-risk-list">
               <el-scrollbar max-height="320px">
                  <div v-for="(student, index) in data.at_risk_students" :key="index" class="at-risk-item">
                     <div class="student-left">
                       <div class="student-avatar" :class="student.type">
                          <span class="avatar-text">{{ student.name.charAt(0) }}</span>
                       </div>
                       <div class="student-info">
                          <div class="student-name">{{ student.name }}</div>
                          <div class="student-meta">
                            <span class="student-class">{{ student.class }}</span>
                            <span v-if="student.parent_phone" class="phone-number">
                              <el-icon><Phone /></el-icon> {{ student.parent_phone }}
                            </span>
                          </div>
                       </div>
                     </div>
                     <div class="risk-badge">
                        <el-tag :type="student.type" size="small" effect="dark">{{ student.reason }}</el-tag>
                     </div>
                  </div>
               </el-scrollbar>
             </div>
             <div v-else class="empty-state">
                <el-icon class="success-icon"><CircleCheckFilled /></el-icon>
                <span>No students at risk! 🎉</span>
                <span class="sub-text">Attendance is looking good</span>
             </div>
          </div>
        </div>
      </el-col>
    </el-row>

    <!-- Quick Actions -->
    <div class="quick-actions">
      <h3 class="section-title">Quick Actions</h3>
      <el-row :gutter="12">
        <el-col :xs="12" :sm="8" :md="6" :lg="4">
          <el-button 
            type="primary" 
            class="action-btn"
            @click="navigateTo('/students')"
          >
            <el-icon><User /></el-icon>
            <span>View Students</span>
          </el-button>
        </el-col>
        <el-col :xs="12" :sm="8" :md="6" :lg="4">
          <el-button 
            type="success" 
            class="action-btn"
            @click="navigateTo('/teachers')"
          >
            <el-icon><Avatar /></el-icon>
            <span>View Teachers</span>
          </el-button>
        </el-col>
        <el-col :xs="12" :sm="8" :md="6" :lg="4">
          <el-button 
            type="warning" 
            class="action-btn"
            @click="navigateTo('/attendance')"
          >
            <el-icon><Clock /></el-icon>
            <span>Attendance</span>
          </el-button>
        </el-col>
        <el-col :xs="12" :sm="8" :md="6" :lg="4">
          <el-button 
            type="info" 
            class="action-btn"
            @click="navigateTo('/admissions')"
          >
            <el-icon><Plus /></el-icon>
            <span>Admissions</span>
          </el-button>
        </el-col>
      </el-row>
    </div>
  </div>
</template>

<script setup>
import Resource from '@/api/resource';
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { 
  User, Avatar, Warning, UserFilled, School, RefreshRight, 
  TrendCharts, Star, Clock, Present, CircleCheck, Plus, 
  DocumentAdd, WarningFilled, CircleCheckFilled, Phone
} from '@element-plus/icons-vue';

const router = useRouter();
let dashRes = new Resource('dashboard');

// Reactive data
const data = ref({
  total_students: 0, 
  total_absent_students: 0, 
  total_teachers: 0, 
  total_absent_teachers: 0, 
  absent_teachers: [], 
  student_birthdays: [], 
  teacher_birthdays: [], 
  newAdmissionsPerClass: [], 
  newAdmissions: 0,
  at_risk_students: []
});

const loading = ref(false);
const activeCard = ref('');

// Animated counters
const animatedStudents = ref(0);
const animatedTeachers = ref(0);
const animatedAbsentStudents = ref(0);
const animatedAbsentTeachers = ref(0);

// Animation function
const animateCounter = (target, endValue, duration = 2000) => {
  const startTime = Date.now();
  const startValue = target.value;
  
  const animate = () => {
    const elapsed = Date.now() - startTime;
    const progress = Math.min(elapsed / duration, 1);
    
    // Easing function for smooth animation
    const easeOutQuart = 1 - Math.pow(1 - progress, 4);
    
    target.value = Math.round(startValue + (endValue - startValue) * easeOutQuart);
    
    if (progress < 1) {
      requestAnimationFrame(animate);
    }
  };
  
  requestAnimationFrame(animate);
};

// Methods
const getData = async () => {
  loading.value = true;
  try {
    const response = await dashRes.list();
    return response.data;
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
    return null;
  } finally {
    loading.value = false;
  }
};

const refreshData = async () => {
  const result = await getData();
  if (result) {
    data.value = result;
    
    // Trigger counter animations
    animateCounter(animatedStudents, result.total_students || 0);
    animateCounter(animatedTeachers, result.total_teachers || 0, 2200);
    animateCounter(animatedAbsentStudents, result.total_absent_students || 0, 2400);
    animateCounter(animatedAbsentTeachers, result.total_absent_teachers || 0, 2600);
  }
};

const handleCardClick = (cardType) => {
  activeCard.value = activeCard.value === cardType ? '' : cardType;
  
  // Navigate to relevant pages based on card type
  switch(cardType) {
    case 'students':
      navigateTo('/students');
      break;
    case 'teachers':
      navigateTo('/teachers');
      break;
    case 'absent-students':
    case 'absent-teachers':
      navigateTo('/attendance');
      break;
  }
};

const navigateTo = (path) => {
  router.push(path);
};

const getAdmissionPercentage = (count) => {
  const maxCount = Math.max(...(data.value.newAdmissionsPerClass[0]?.map(item => item.count) || [1]));
  return (count / maxCount) * 100;
};

// Lifecycle
onMounted(() => {
  refreshData();
});

// Emit events
const emit = defineEmits(['handleSetLineChartData']);
const handleSetLineChartData = (type) => {
  emit('handleSetLineChartData', type);
};
</script>

<style lang="scss" scoped>
.panel-group {
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 15px;
  min-height: 100vh;
  
  // Dashboard Header
  .dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    padding: 20px 25px;
    border-radius: 15px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    
    .dashboard-title {
      color: white;
      margin: 0;
      font-size: 28px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 12px;
      
      .title-icon {
        font-size: 32px;
        color: #ffd700;
      }
    }
    
    .refresh-controls {
      .el-button {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        
        &:hover {
          background: rgba(255, 255, 255, 0.3);
          transform: rotate(180deg);
          transition: all 0.3s ease;
        }
      }
    }
  }

  // Stats Cards Row
  .stats-row {
    margin-bottom: 30px;
  }

  .card-panel-col {
    margin-bottom: 20px;
    
    &:last-child {
      margin-bottom: 0;
    }
  }

  // Stats Cards
  .stats-card {
    position: relative;
    overflow: hidden;
    border-radius: 20px;
    padding: 0px 25px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    backdrop-filter: blur(10px);
    
    &:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    }
    
    &.card-active {
      transform: scale(1.05);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
    }
    
    .card-gradient {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      opacity: 0.9;
      z-index: 1;
    }
    
    .card-content {
      position: relative;
      z-index: 2;
      padding: 12px 20px;
      display: flex;
      align-items: center;
      height: 85px;
      
      .card-icon {
        font-size: 32px;
        color: rgba(255, 255, 255, 0.9);
        margin-right: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        backdrop-filter: blur(5px);
      }
      
      .card-info {
        flex: 1;
        color: white;
        
        .card-title {
          font-size: 13px;
          font-weight: 500;
          margin-bottom: 2px;
          opacity: 0.9;
          text-transform: uppercase;
          letter-spacing: 0.5px;
        }
        
        .card-number {
          font-size: 26px;
          font-weight: 700;
          margin-bottom: 2px;
          text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .card-trend {
          display: flex;
          align-items: center;
          gap: 6px;
          font-size: 11px;
          opacity: 0.8;
          
          .trend-icon {
            font-size: 12px;
          }
        }
      }
    }
    
    // Individual card colors
    &.students-card .card-gradient {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    &.teachers-card .card-gradient {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    &.absent-students-card .card-gradient {
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    
    &.absent-teachers-card .card-gradient {
      background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }
  }

  // Info Cards Row
  .info-row {
    margin-bottom: 30px;
  }

  .info-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 25px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    height: 100%;
    min-height: 400px;
    
    .card-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 2px solid #f0f0f0;
      
      .header-icon {
        font-size: 24px;
        color: #667eea;
      }
      
      .card-header-title {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
      }
    }
  }

  // Birthday Card Styles
  .birthdays-card {
    .birthday-section {
      margin-bottom: 20px;
      
      .section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        color: #555;
        margin-bottom: 15px;
        font-size: 14px;
      }
      
      .birthday-list {
        .birthday-item {
          display: flex;
          align-items: center;
          gap: 12px;
          padding: 12px;
          margin-bottom: 8px;
          background: linear-gradient(135deg, #f6f9fc 0%, #ffffff 100%);
          border-radius: 12px;
          border: 1px solid #e8f0fe;
          transition: all 0.2s ease;
          
          &:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
          }
          
          &.student-birthday {
            border-left: 4px solid #667eea;
          }
          
          &.teacher-birthday {
            border-left: 4px solid #f093fb;
          }
          
          .birthday-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
          }
          
          .birthday-info {
            flex: 1;
            
            .birthday-name {
              font-weight: 600;
              color: #333;
              margin-bottom: 4px;
            }
            
            .birthday-details {
              font-size: 12px;
              color: #666;
            }
          }
          
          .birthday-icon {
            font-size: 20px;
          }
        }
      }
    }
  }

  // Admissions Card Styles
  .admissions-card {
    .admissions-content {
      .admissions-chart {
        .admission-bar {
          margin-bottom: 15px;
          
          .admission-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
            
            .class-name {
              font-weight: 600;
              color: #333;
            }
            
            .class-count {
              background: linear-gradient(135deg, #667eea, #764ba2);
              color: white;
              padding: 4px 12px;
              border-radius: 15px;
              font-size: 12px;
              font-weight: 600;
            }
          }
          
          .progress-bar {
            height: 8px;
            background: #f0f0f0;
            border-radius: 4px;
            overflow: hidden;
            
            .progress-fill {
              height: 100%;
              background: linear-gradient(90deg, #667eea, #764ba2);
              border-radius: 4px;
              transition: width 0.8s ease;
            }
          }
        }
      }
    }
  }

  // Empty State
  .empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    color: #999;
    
    .el-icon {
      font-size: 48px;
      margin-bottom: 12px;
      color: #ccc;
    }
    
    span {
      font-size: 14px;
    }
  }

  // Quick Actions
  .quick-actions {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 25px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    margin-top: 60px;
    
    .section-title {
      color: white;
      margin: 0 0 20px 0;
      font-size: 20px;
      font-weight: 600;
    }
    
    .action-btn {
      width: 100%;
      height: 60px;
      border-radius: 15px;
      font-weight: 600;
      transition: all 0.3s ease;
      border: none;
      
      &:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
      }
      
      span {
        margin-left: 8px;
      }
    }
  }

  // Tooltip Styles
  .teacher-tooltip {
    .tooltip-title {
      font-weight: 600;
      margin-bottom: 8px;
      color: #333;
    }
    
    .teacher-item {
      padding: 2px 0;
      color: #666;
    }
    
    .no-absences {
      color: #67c23a;
      font-weight: 600;
    }
  }

  // Responsive Design
  @media (max-width: 1024px) {
    .info-row {
      .card-panel-col {
        margin-bottom: 30px;
      }
    }
    
    .quick-actions {
      margin-top: 35px;
    }
  }
  
  @media (max-width: 768px) {
    padding: 15px;
    
    .dashboard-header {
      flex-direction: column;
      gap: 15px;
      text-align: center;
      
      .dashboard-title {
        font-size: 24px;
      }
    }
    
    .stats-card .card-content {
      padding: 20px;
      height: 100px;
      
      .card-icon {
        width: 50px;
        height: 50px;
        font-size: 24px;
        margin-right: 15px;
      }
      
      .card-info .card-number {
        font-size: 24px;
      }
    }
    
    // Fix overlapping info cards
    .info-row {
      .card-panel-col {
        margin-bottom: 25px;
      }
    }
    
    .info-card {
      padding: 20px;
      min-height: auto;
      margin-bottom: 20px;
    }
    
    .quick-actions {
      margin-top: 40px;
      
      .action-btn {
        height: 50px;
        margin-bottom: 10px;
      }
    }
  }

  @media (max-width: 480px) {
    .stats-card .card-content {
      flex-direction: column;
      text-align: center;
      height: auto;
      padding: 15px;
      
      .card-icon {
        margin-right: 0;
        margin-bottom: 10px;
      }
    }
    
    // Enhanced mobile responsive for info cards
    .info-row {
      .card-panel-col {
        margin-bottom: 30px;
      }
    }
    
    .info-card {
      padding: 15px;
      min-height: 350px;
      
      .card-header {
        margin-bottom: 15px;
        padding-bottom: 10px;
        
        .card-header-title {
          font-size: 16px;
        }
      }
    }
    
    // Birthday card mobile improvements
    .birthdays-card {
      .birthday-section {
        margin-bottom: 15px;
        
        .birthday-list {
          .birthday-item {
            padding: 10px;
            margin-bottom: 6px;
            
            .birthday-avatar {
              width: 35px;
              height: 35px;
              font-size: 16px;
            }
            
            .birthday-info {
              .birthday-name {
                font-size: 14px;
              }
              
              .birthday-details {
                font-size: 11px;
              }
            }
          }
        }
      }
    }
    
    // Admissions card mobile improvements
    .admissions-card {
      .admissions-content {
        .admissions-chart {
          .admission-bar {
            margin-bottom: 12px;
            
            .admission-info {
              .class-name {
                font-size: 14px;
              }
              
              .class-count {
                padding: 3px 10px;
                font-size: 11px;
              }
            }
          }
        }
      }
    }
    
    // Quick actions mobile improvements
    .quick-actions {
      margin-top: 60px;
      padding: 20px 15px;
      
      .section-title {
        font-size: 18px;
        margin-bottom: 15px;
      }
      
      .action-btn {
        height: 45px;
        font-size: 14px;
        margin-bottom: 8px;
      }
    }
  }
}

// Animation Classes
.counter-animation {
  transition: all 0.3s ease;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s;
}

.fade-enter, .fade-leave-to {
  opacity: 0;
}

// At Risk Card Styles
.at-risk-card {
  .header-icon.warning-icon {
    color: #F56C6C;
  }
  
  .at-risk-list {
    .at-risk-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px;
      margin-bottom: 8px;
      background: #fafafa;
      border-radius: 12px;
      border: 1px solid #f0f0f0;
      transition: all 0.2s ease;
      
      &:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        background: #fff;
      }
      
      .student-left {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
        min-width: 0;
        
        .student-avatar {
          width: 40px;
          height: 40px;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          color: white;
          font-weight: bold;
          font-size: 16px;
          flex-shrink: 0;
          
          &.danger {
            background: linear-gradient(135deg, #F56C6C 0%, #fab6b6 100%);
            box-shadow: 0 4px 10px rgba(245, 108, 108, 0.3);
          }
          
          &.warning {
            background: linear-gradient(135deg, #E6A23C 0%, #f7c986 100%);
            box-shadow: 0 4px 10px rgba(230, 162, 60, 0.3);
          }
        }
        
        .student-info {
          flex: 1;
          min-width: 0;
          
          .student-name {
            font-weight: 600;
            color: #333;
            font-size: 14px;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
          }
          
          .student-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #909399;
            flex-wrap: wrap;
            
            .student-class {
              background: #f4f4f5;
              padding: 2px 6px;
              border-radius: 4px;
            }
            
            .phone-number {
              display: flex;
              align-items: center;
              gap: 3px;
              
              .el-icon {
                font-size: 10px;
              }
            }
          }
        }
      }
      
      .risk-badge {
        margin-left: 10px;
        flex-shrink: 0;
      }
    }
  }
  
  .empty-state {
    .success-icon {
      color: #67C23A;
    }
    .sub-text {
      font-size: 13px;
      color: #909399;
      margin-top: 5px;
    }
  }
}
</style>
