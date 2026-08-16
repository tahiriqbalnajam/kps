<template>
  <el-dialog
    :modelValue="showcardprop"
    title="Teacher ID Card"
    width="420px"
    @close="handleClose"
  >
    <div class="print-actions">
      <el-button type="primary" @click="printIDCard">
        <el-icon><Printer /></el-icon> Print ID Card
      </el-button>
    </div>

    <div class="id-card-container" ref="idCardContent">
      <div class="id-card">
        <!-- Indigo header band: logo + school name -->
        <div class="id-card-header">
          <div class="logo-box">
            <img v-if="resolvedLogo" :src="resolvedLogo" alt="School Logo" class="school-logo" />
          </div>
          <div class="school-name">{{ settings.school_name || 'School Name' }}</div>
        </div>

        <!-- Body: QR on the left, teacher details on the right -->
        <div class="id-card-body">
          <div class="qr-box">
            <vue-qrcode class="qr-code" :value="qrCodeUrl" :size="88" level="H" />
          </div>
          <div class="teacher-info">
            <div class="card-title">Teacher ID Card</div>
            <div class="teacher-name">{{ teacher.name || 'Teacher Name' }}</div>
            <div v-if="teacher.teacher_special_id" class="teacher-code">ID: {{ teacher.teacher_special_id }}</div>
            <div v-if="teacher.designation" class="teacher-code">Designation: {{ teacher.designation }}</div>
            <div v-if="teacher.class_name" class="teacher-class">Class Teacher: {{ teacher.class_name }}</div>
          </div>
        </div>

        <!-- Footer strip -->
        <div class="id-card-footer">
          <span class="scan-instruction">Scan QR code for online verification</span>
          <span class="issue-date">Issued: {{ currentDate }}</span>
        </div>
      </div>
    </div>
  </el-dialog>
</template>

<script>
import VueQrcode from 'qrcode.vue'
import Resource from '@/api/resource'
import { Printer } from '@element-plus/icons-vue'

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
      settingsResource: new Resource('settings'),
      currentDateStr: new Date().toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
      })
    }
  },
  computed: {
    qrCodeUrl() {
      return `/teacher/${this.teacher.id || ''}/online`
    },
    currentDate() {
      return this.currentDateStr
    },
    // The logo may be a relative path — resolve it against the app origin so
    // it also loads inside the print window (about:blank would break it)
    resolvedLogo() {
      const logo = this.settings.school_logo
      if (!logo) return ''
      try {
        return new URL(logo, window.location.origin).href
      } catch (e) {
        return logo
      }
    }
  },
  mounted() {
    this.loadSettings()
  },
  methods: {
    async loadSettings() {
      try {
        const { data } = await this.settingsResource.list()
        this.settings = data.settings || {}
      } catch (error) {
        console.error('Failed to load settings:', error)
      }
    },
    handleClose() {
      this.$emit('closeAddSection')
    },
    printIDCard() {
      // vue-qrcode renders a <canvas> as its root element, so .qr-code IS
      // the canvas — grabbing .qr-code canvas was the old bug (empty QR).
      const qrCanvas = document.querySelector('.qr-code')
      let qrCodeDataUrl = ''
      if (qrCanvas && typeof qrCanvas.toDataURL === 'function') {
        qrCodeDataUrl = qrCanvas.toDataURL('image/png')
      }

      const logo = this.resolvedLogo
      const schoolName = this.settings.school_name || 'School Name'
      const t = this.teacher

      const printContent = `
        <html>
        <head>
          <title>Teacher ID Card</title>
          <style>
            @page { size: 85mm 54mm; margin: 0; }
            * { box-sizing: border-box; margin: 0; padding: 0; }
            html, body { width: 85mm; height: 54mm; }
            body { font-family: Arial, sans-serif; }
            .id-card {
              width: 85mm; height: 54mm;
              background: #fff;
              display: flex; flex-direction: column;
              overflow: hidden;
              -webkit-print-color-adjust: exact;
              print-color-adjust: exact;
            }
            .id-card-header {
              background: linear-gradient(135deg, #4f46e5, #818cf8);
              color: #fff;
              display: flex; align-items: center; gap: 3mm;
              padding: 2.5mm 4mm;
            }
            .logo-box {
              width: 11mm; height: 11mm; flex-shrink: 0;
              background: #fff; border-radius: 2mm;
              display: flex; align-items: center; justify-content: center;
              overflow: hidden;
            }
            .school-logo { max-width: 100%; max-height: 100%; object-fit: contain; }
            .school-name {
              font-size: 4mm; font-weight: 700; letter-spacing: 0.3px;
              white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            }
            .id-card-body {
              flex: 1; min-height: 0;
              display: flex; align-items: center;
              padding: 3.5mm 4mm; gap: 5mm;
            }
            .qr-box {
              flex-shrink: 0;
              border: 0.4mm solid #e0e7ff; border-radius: 2mm; padding: 2mm;
            }
            .qr-code-img { width: 88px; height: 88px; display: block; }
            .teacher-info { flex: 1; min-width: 0; }
            .card-title {
              font-size: 3mm; font-weight: 800; color: #4f46e5;
              text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1.5mm;
            }
            .teacher-name {
              font-size: 4.5mm; font-weight: 700; color: #1e293b;
              line-height: 1.2; margin-bottom: 1mm;
              white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            }
            .teacher-code, .teacher-class { font-size: 3mm; color: #475569; line-height: 1.5; }
            .id-card-footer {
              background: #eef2ff; color: #4f46e5;
              display: flex; justify-content: space-between; align-items: center;
              padding: 2mm 4mm; font-size: 2.8mm;
            }
          </style>
        </head>
        <body>
          <div class="id-card">
            <div class="id-card-header">
              <div class="logo-box">
                ${logo ? `<img src="${logo}" alt="School Logo" class="school-logo"/>` : ''}
              </div>
              <div class="school-name">${schoolName}</div>
            </div>

            <div class="id-card-body">
              <div class="qr-box">
                ${qrCodeDataUrl ? `<img src="${qrCodeDataUrl}" alt="QR Code" class="qr-code-img"/>` : ''}
              </div>
              <div class="teacher-info">
                <div class="card-title">Teacher ID Card</div>
                <div class="teacher-name">${t.name || 'Teacher Name'}</div>
                ${t.teacher_special_id ? `<div class="teacher-code">ID: ${t.teacher_special_id}</div>` : ''}
                ${t.designation ? `<div class="teacher-code">Designation: ${t.designation}</div>` : ''}
                ${t.class_name ? `<div class="teacher-class">Class Teacher: ${t.class_name}</div>` : ''}
              </div>
            </div>

            <div class="id-card-footer">
              <span>Scan QR code for online verification</span>
              <span>Issued: ${this.currentDate}</span>
            </div>
          </div>
        </body>
        </html>
      `

      const printWindow = window.open('', '_blank')
      printWindow.document.open()
      printWindow.document.write(printContent)
      printWindow.document.close()

      printWindow.onload = function () {
        printWindow.focus()
        printWindow.print()
        setTimeout(function () {
          printWindow.close()
        }, 1000)
      }
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

/* 85mm × 54mm credit-card proportions, rendered 1:1 with mm units so the
   on-screen preview is exactly what prints */
.id-card {
  width: 85mm;
  border-radius: 4mm;
  overflow: hidden;
  background: #fff;
  border: 1px solid #e4e7ed;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.12);
  font-family: Arial, sans-serif;
}

.id-card-header {
  background: linear-gradient(135deg, #4f46e5, #818cf8);
  color: #fff;
  display: flex;
  align-items: center;
  gap: 3mm;
  padding: 2.5mm 4mm;
}

.logo-box {
  width: 11mm;
  height: 11mm;
  flex-shrink: 0;
  background: #fff;
  border-radius: 2mm;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.school-logo {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.school-name {
  font-size: 4mm;
  font-weight: 700;
  letter-spacing: 0.3px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.id-card-body {
  min-height: 32mm;
  display: flex;
  align-items: center;
  padding: 3.5mm 4mm;
  gap: 5mm;
}

.qr-box {
  flex-shrink: 0;
  border: 0.4mm solid #e0e7ff;
  border-radius: 2mm;
  padding: 2mm;
  line-height: 0;
}

.teacher-info {
  flex: 1;
  min-width: 0;
}

.card-title {
  font-size: 3mm;
  font-weight: 800;
  color: #4f46e5;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 1.5mm;
}

.teacher-name {
  font-size: 4.5mm;
  font-weight: 700;
  color: #1e293b;
  line-height: 1.2;
  margin-bottom: 1mm;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.teacher-code,
.teacher-class {
  font-size: 3mm;
  color: #475569;
  line-height: 1.5;
}

.id-card-footer {
  background: #eef2ff;
  color: #4f46e5;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 2mm 4mm;
  font-size: 2.8mm;
}
</style>
