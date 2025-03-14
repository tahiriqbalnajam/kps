<template>
    <el-dialog
      :modelValue="showcardprop"
      title="Teacher ID Card"
      width="350px"
      @close="handleClose"
    >
      <div class="print-actions">
        <el-button type="primary" @click="printIDCard">
          <el-icon><Printer /></el-icon> Print ID Card
        </el-button>
      </div>

      <div class="id-card-container" ref="idCardContent">
        <div class="id-card">
          <!-- Header with logo and school name in two columns -->
          <div class="id-card-header">
            <div class="header-content">
              <div class="logo-container">
                <img v-if="settings.school_logo" :src="settings.school_logo" alt="School Logo" class="school-logo"/>
              </div>
              <div class="school-info">
                <h2 class="school-name">{{ settings.school_name || 'School Name' }}</h2>
              </div>
            </div>
          </div>
          
          <!-- Card Title -->
          <div class="card-title-container">
            <h3 class="card-title">Teacher ID Card</h3>
          </div>
          
          <div class="id-card-body">
            <!-- Large QR Code in a single line -->
            <div class="qr-section">
              <vue-qrcode 
                :value="qrCodeUrl" 
                :size="125" 
                level="H" 
                class="qr-code"
              ></vue-qrcode>
            </div>
            
            <!-- Teacher Name -->
            <div class="teacher-info">
              <h3 class="teacher-name">{{ teacher.name || 'Teacher Name' }}</h3>
              <p v-if="teacher.teacher_special_id" class="teacher-code">ID: {{ teacher.teacher_special_id }}</p>
              <p v-if="teacher.designation" class="teacher-code"><b>Designation: {{ teacher.designation }}</b></p>
              <p v-if="teacher.class_name" class="teacher-class">Class Teacher: {{ teacher.class_name }}</p>
            </div>
            
            <!-- Footer with scan instruction and issue date -->
            <div class="id-card-footer">
              <p class="scan-instruction">Scan QR code for online verification</p>
              <p class="issue-date">Issued: {{ currentDate }}</p>
            </div>
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
  name: 'TeacherIdcard',
  components: {
    VueQrcode,
    Printer
  },
  props: {
    showcardprop: {
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
      this.$emit('closeAddSection');
    },
    printIDCard() {
      // Create a new window for printing
      const printWindow = window.open('', '_blank');
      
      // Get the QR code image from the DOM
      const qrCodeCanvas = document.querySelector('.qr-code canvas');
      let qrCodeDataUrl = '';
      
      // Convert canvas to data URL for reliable printing
      if (qrCodeCanvas) {
        qrCodeDataUrl = qrCodeCanvas.toDataURL('image/png');
      }
      
      // Get the school logo
      const schoolLogo = this.settings.school_logo || '';
      
      // Create the print HTML with inline styles
      const printContent = `
        <html>
        <head>
          <title>Teacher ID Card</title>
          <style>
            @page {
              size: 85mm 54mm;
              margin: 0;
            }
            body {
              margin: 0;
              padding: 0;
              width: 85mm;
              height: 54mm;
              font-family: Arial, sans-serif;
              font-size: 10px;
            }
            .id-card {
              width: 100%;
              height: 100%;
              box-sizing: border-box;
              background-color: white;
              display: flex;
              flex-direction: column;
            }
            .id-card-header {
              padding: 4px 8px;
              display: flex;
              align-items: center;
              border-bottom: 1px solid #eee;
            }
            .logo-container {
              width: 30%;
            }
            .school-logo {
              height: 40px;
              max-width: 100%;
            }
            .school-info {
              width: 70%;
              padding-left: 8px;
            }
            .school-name {
              font-size: 12px;
              font-weight: bold;
              margin: 0;
            }
            .card-title-container {
              text-align: center;
              padding: 2px 0;
              border-bottom: 1px solid #eee;
            }
            .card-title {
              font-size: 10px;
              text-transform: uppercase;
              margin: 0;
              font-weight: bold;
            }
            .id-card-body {
              flex-grow: 1;
              display: flex;
              flex-direction: column;
              align-items: center;
              padding: 4px 0;
            }
            .qr-section {
              text-align: center;
              margin: 4px 0;
            }
            .qr-code-img {
              width: 90px;
              height: 90px;
            }
            .teacher-info {
              text-align: center;
              width: 100%;
            }
            .teacher-name {
              font-size: 12px;
              font-weight: bold;
              margin: 3px 0;
            }
            .teacher-code, .teacher-class {
              font-size: 9px;
              margin: 2px 0;
            }
            .id-card-footer {
              background-color: #f5f7fa;
              text-align: center;
              padding: 3px 0;
              font-size: 8px;
              color: #606266;
              border-top: 1px solid #eee;
            }
            .scan-instruction, .issue-date {
              margin: 2px 0;
            }
          </style>
        </head>
        <body>
          <div class="id-card">
            <div class="id-card-header">
              <div class="logo-container">
                ${schoolLogo ? `<img src="${schoolLogo}" alt="School Logo" class="school-logo"/>` : ''}
              </div>
              <div class="school-info">
                <h2 class="school-name">${this.settings.school_name || 'School Name'}</h2>
              </div>
            </div>
            
            <div class="card-title-container">
              <h3 class="card-title">Teacher ID Card</h3>
            </div>
            
            <div class="id-card-body">
              <div class="qr-section">
                ${qrCodeDataUrl ? `<img src="${qrCodeDataUrl}" alt="QR Code" class="qr-code-img"/>` : ''}
              </div>
              
              <div class="teacher-info">
                <div class="teacher-name">${this.teacher.name || 'Teacher Name'}</div>
                ${this.teacher.teacher_special_id ? `<div class="teacher-code">ID: ${this.teacher.teacher_special_id}</div>` : ''}
                ${this.teacher.designation ? `<div class="teacher-code"><b>Designation: ${this.teacher.designation}</b></div>` : ''}
                ${this.teacher.class_name ? `<div class="teacher-class">Class Teacher: ${this.teacher.class_name}</div>` : ''}
              </div>
            </div>
            
            <div class="id-card-footer">
              <div class="scan-instruction">Scan QR code for online verification</div>
              <div class="issue-date">Issued: ${this.currentDate}</div>
            </div>
          </div>
        </body>
        </html>
      `;
      
      // Write to the new window
      printWindow.document.open();
      printWindow.document.write(printContent);
      printWindow.document.close();
      
      // Add onload event to print after resources are loaded
      printWindow.onload = function() {
        printWindow.focus();
        printWindow.print();
        setTimeout(function() {
          printWindow.close();
        }, 1000);
      };
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
  height: auto;
  background: white;
  border-radius: 10px;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  font-family: Arial, sans-serif;
}

.id-card-header {
  color: #000;
  padding: 4px 8px;
}

.header-content {
  display: flex;
  align-items: center;
}

.logo-container {
  width: 30%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.school-logo {
  height: 60px;
  max-width: 100%;
  object-fit: contain;
}

.school-info {
  width: 70%;
  padding-left: 8px;
}

.school-name {
  font-size: 15px;
  margin: 0;
  line-height: 1.2;
}

.card-title-container {
  color: #000;
  text-align: center;
  padding: 3px 0;
}

.card-title {
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin: 0;
}

.id-card-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 4px 0;
  justify-content: space-between;
}

.qr-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
  margin: 30px 0;
}

.qr-code {
  width: 85px !important;
  height: 85px !important;
}

.teacher-info {
  text-align: center;
  width: 100%;
  padding: 0 10px;
}

.teacher-name {
  font-size: 14px;
  font-weight: bold;
  margin: 2px 0;
  color: #333;
  word-wrap: break-word;
  line-height: 1.2;
}

.teacher-class {
  font-size: 10px;
  color: #666;
  margin: 2px 0;
  line-height: 1.1;
}

.id-card-footer {
  width: 100%;
  background: #f5f7fa;
  text-align: center;
  padding: 3px 0;
  margin-top: 3px;
}

.scan-instruction {
  font-size: 9px;
  color: #606266;
  margin: 1px 0;
}

.issue-date {
  font-size: 9px;
  font-weight: bold;
  color: #606266;
  margin: 1px 0;
}

.teacher-code {
  font-size: 9px;
  color: #606266;
  margin: 1px 0;
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
  
  /* Enhanced print styles */
  .qr-code {
    display: block !important;
    width: 85px !important;
    height: 85px !important;
  }
  
  .qr-code canvas,
  .qr-code img {
    width: 100% !important;
    height: 100% !important;
    display: block !important;
  }
}
</style>
