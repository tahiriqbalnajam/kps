<template>
    <el-dialog
      :modelValue="modelValue"
      title="Teacher ID Card"
      width="350px"
      :before-close="handleClose"
    >
      <div class="print-actions">
        <el-button type="primary" @click="printIDCard">
          <el-icon><Printer /></el-icon> Print ID Card
        </el-button>
      </div>

      <div class="id-card-container" ref="idCardContent">
        <div class="id-card">
          <div class="id-card-header">
            <img v-if="settings.school_logo" :src="settings.school_logo" alt="School Logo" class="school-logo"/>
            <h2 class="school-name">{{ settings.school_name || 'School Name' }}</h2>
            <div class="card-title">Teacher ID Card</div>
          </div>
          
          <div class="id-card-body">
            <div class="qr-section">
              <vue-qrcode 
                :value="qrCodeUrl" 
                :size="150" 
                level="H" 
                class="qr-code"
              ></vue-qrcode>
              <div class="teacher-code">{{ teacher.id || 'TC-0000' }}</div>
            </div>
            
            <div class="teacher-info">
              <h3 class="teacher-name">{{ teacher.name || 'Teacher Name' }}</h3>
              <p v-if="teacher.class_name" class="teacher-class">Class Teacher: {{ teacher.class_name }}</p>
            </div>
          </div>
          
          <div class="id-card-footer">
            <p>Scan QR code for online verification</p>
            <p class="issue-date">Issued: {{ currentDate }}</p>
          </div>
        </div>
      </div>
    </el-dialog>
</template>

<script>
import VueQrcode from 'qrcode.vue';
import Resource from '@/api/resource';
import { Printer } from '@element-plus/icons-vue';

export default {
  name: 'TeacherIDCard',
  components: {
    VueQrcode,
    Printer
  },
  props: {
    modelValue: {
      type: Boolean,
      default: false
    },
    teacher: {
      type: Object,
      required: true,
      default: () => ({})
    }
  },
  data() {
    return {
      settings: {},
      showmodal: true,
      settingsResource: new Resource('settings'),
      currentDateStr: new Date().toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
      })
    };
  },
  computed: {
    dialogVisible() {
      return this.modelValue;
    },
    qrCodeUrl() {
      return `${window.location.origin}/teacher/${this.teacher.id || ''}/online`;
    },
    currentDate() {
      return this.currentDateStr;
    }
  },
  mounted() {
    this.loadSettings();
  },
  methods: {
    async loadSettings() {
      try {
        const { data } = await this.settingsResource.list();
        this.settings = data.settings || {};
      } catch (error) {
        console.error('Failed to load settings:', error);
      }
    },
    updateDialog(value) {
      this.$emit('update:modelValue', value);
    },
    handleClose() {
      this.$emit('update:modelValue', false);
    },
    printIDCard() {
      const printContent = document.querySelector('.id-card-container').innerHTML;
      const originalContent = document.body.innerHTML;
      
      const printStyles = `
        <style>
          @page {
            size: 85mm 54mm; /* ID card standard size */
            margin: 0;
          }
          body {
            margin: 0;
            padding: 0;
          }
          .id-card {
            width: 85mm;
            height: 54mm;
            page-break-after: always;
            box-shadow: none;
          }
          .print-actions {
            display: none;
          }
        </style>
      `;
      
      document.body.innerHTML = printStyles + printContent;
      window.print();
      document.body.innerHTML = originalContent;
      window.location.reload();
    }
  }
}
</script>

<style scoped>
.print-actions {
  text-align: right;
  margin-bottom: 15px;
}

.id-card-container {
  display: flex;
  justify-content: center;
  padding: 15px 0;
}

.id-card {
  width: 85mm;
  height: 54mm;
  background: white;
  border-radius: 10px;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  font-family: Arial, sans-serif;
}

.id-card-header {
  background: #2c3e50;
  color: white;
  text-align: center;
  padding: 8px 0;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.school-logo {
  height: 25px;
  max-width: 80%;
  object-fit: contain;
}

.school-name {
  font-size: 14px;
  margin: 3px 0;
}

.card-title {
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 3px;
}

.id-card-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 5px 10px;
}

.qr-section {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.qr-code {
  width: 100px;
  height: 100px;
}

.teacher-code {
  font-size: 10px;
  color: #666;
  margin-top: 3px;
}

.teacher-info {
  text-align: center;
  margin-top: 5px;
}

.teacher-name {
  font-size: 16px;
  font-weight: bold;
  margin: 5px 0;
  color: #333;
}

.teacher-class {
  font-size: 12px;
  color: #666;
  margin: 3px 0;
}

.id-card-footer {
  background: #f5f7fa;
  text-align: center;
  padding: 5px 0;
  font-size: 8px;
  color: #606266;
}

.issue-date {
  font-weight: bold;
  margin-top: 2px;
}

@media print {
  .el-dialog {
    display: none !important;
  }
  
  .id-card-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    padding: 0;
    margin: 0;
    background: white;
  }
  
  .id-card {
    box-shadow: none;
    border: none;
    width: 85mm;
    height: 54mm;
    margin: 0;
    border-radius: 0;
  }
}
</style>
