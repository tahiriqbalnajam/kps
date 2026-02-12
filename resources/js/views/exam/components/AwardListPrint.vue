<template>
  <el-dialog
    v-model="visible"
    title="Award List"
    width="95%"
    :before-close="handleClose"
    fullscreen
  >
    <div v-loading="loading">
      <div class="print-actions no-print">
        <el-button type="primary" @click="printAwardList">
          <el-icon><Printer /></el-icon> Print
        </el-button>
        <el-button @click="handleClose">Close</el-button>
      </div>

      <div id="award-list-print" class="award-list-container">
        <div class="award-list-header">
          <el-row>
            <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl ="8" style="text-align: left;">
              <h3>Award List</h3>
            </el-col>
            <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl ="8">
              <h3>{{ examData.title }}</h3>
            </el-col>
            <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl ="8" style="text-align: right;">
              <h3>Class: {{ examData.classes?.name }}</h3>
            </el-col>
          </el-row>
        </div>

        <table class="award-list-table">
          <thead>
            <tr>
              <th class="col-roll">Roll No</th>
              <th class="col-student">Student Name</th>
              <th class="col-father">Father Name</th>
              <th 
                v-for="subject in examData.exam_subjects" 
                :key="subject.id" 
                class="col-subject"
              >
                {{ subject.subject.title }}<br>
                <span class="marks-total">({{ subject.total_marks }})</span>
              </th>
              <th class="col-total">Total<br><span class="marks-total">({{ totalPossibleMarks }})</span></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="student in students" :key="student.id">
              <td class="text-center">{{ student.roll_no || '-' }}</td>
              <td>{{ student.name }}</td>
              <td>{{ student.parents?.name || 'N/A' }}</td>
              <td 
                v-for="subject in examData.exam_subjects" 
                :key="'s-' + subject.id" 
                class="marks-cell"
              >
                &nbsp;
              </td>
              <td class="marks-cell total-cell">&nbsp;</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </el-dialog>
</template>

<script>
import { Printer } from '@element-plus/icons-vue';

export default {
  name: 'AwardListPrint',
  components: {
    Printer,
  },
  props: {
    modelValue: {
      type: Boolean,
      default: false,
    },
    examData: {
      type: Object,
      required: true,
    },
    students: {
      type: Array,
      default: () => [],
    },
    totalPossibleMarks: {
      type: Number,
      default: 0,
    },
  },
  data() {
    return {
      loading: false,
    };
  },
  computed: {
    visible: {
      get() {
        return this.modelValue;
      },
      set(value) {
        this.$emit('update:modelValue', value);
      },
    },
  },
  methods: {
    printAwardList() {
      window.print();
    },
    handleClose() {
      this.$emit('update:modelValue', false);
      this.$emit('close');
    },
  },
};
</script>

<style scoped>
.print-actions {
  margin-bottom: 20px;
  text-align: right;
}

.award-list-container {
  background: white;
  padding: 40px;
  max-width: 100%;
}

.award-list-header {
  text-align: center;
  margin-bottom: 20px;
}

.award-list-header h1 {
  margin: 0;
  font-size: 18px;
  color: #000;
  font-weight: bold;
}

.award-list-table {
  width: 100%;
  border-collapse: collapse;
  margin: 20px 0;
  font-size: 12px;
  color: #000;
}

.award-list-table th,
.award-list-table td {
  border: 1px solid #000;
  padding: 8px 5px;
  text-align: left;
  color: #000;
}

.award-list-table thead th {
  background-color: #e8e8e8;
  font-weight: bold;
  text-align: center;
  vertical-align: middle;
  color: #000;
}

.col-roll {
  width: 60px;
}

.col-student {
  width: 150px;
}

.col-father {
  width: 150px;
}

.col-subject {
  width: 80px;
  min-width: 60px;
}

.col-total {
  width: 80px;
  background-color: #e8e8e8;
}

.marks-total {
  font-size: 10px;
  font-weight: normal;
  color: #000;
}

.text-center {
  text-align: center;
}

.marks-cell {
  text-align: center;
  height: 30px;
  background-color: #f5f5f5;
  color: #000;
}

.total-cell {
  background-color: #e8e8e8;
  font-weight: bold;
  color: #000;
}

.footer-note {
  margin-top: 30px;
  padding: 15px;
  background-color: #f0f0f0;
  border-left: 4px solid #000;
}

.footer-note p {
  margin: 0;
  font-size: 13px;
  color: #000;
  font-weight: 500;
}

/* Print styles */
@media print {
  /* Set page to landscape and remove default margins */
  @page {
    size: A4 landscape;
    margin: 8mm;
  }

  /* Hide non-printable elements */
  .no-print {
    display: none !important;
  }

  /* Hide dialog header, title, and close button */
  .el-dialog__header,
  .el-dialog__headerbtn,
  .el-dialog__close {
    display: none !important;
  }

  /* Completely reset and remove all scrollbars */
  * {
    overflow: visible !important;
  }

  html, body {
    width: 100% !important;
    height: auto !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: hidden !important;
  }

  /* Force all Element Plus components to behave for print */
  .el-overlay,
  .el-overlay-dialog,
  .el-dialog,
  .el-dialog__wrapper,
  .el-dialog__body,
  .el-dialog__footer {
    position: static !important;
    overflow: visible !important;
    height: auto !important;
    max-height: none !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    transform: none !important;
  }

  .award-list-container {
    padding: 5px 0 0 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    overflow: visible !important;
    page-break-after: auto;
    box-sizing: border-box;
  }

  .award-list-header {
    margin-bottom: 5px !important;
  }

  .award-list-header h3 {
    font-size: 12px !important;
    margin: 2px 0 !important;
    line-height: 1.2;
  }

  .award-list-table {
    width: 100% !important;
    max-width: 100% !important;
    font-size: 8px !important;
    color: #000 !important;
    border-collapse: collapse;
    table-layout: fixed;
    page-break-inside: avoid !important;
    transform-origin: top left;
  }

  .award-list-table th,
  .award-list-table td {
    padding: 3px 2px !important;
    color: #000 !important;
    border: 1px solid #000 !important;
    word-wrap: break-word;
    line-height: 1.2;
  }

  .award-list-table thead {
    display: table-header-group;
  }

  .award-list-table thead th {
    background-color: #e8e8e8 !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
    font-size: 8px !important;
    padding: 2px !important;
  }

  .award-list-header h1,
  .award-list-header h2,
  .award-list-header h3,
  .footer-note p {
    color: #000 !important;
  }

  .footer-note {
    page-break-inside: avoid;
    background-color: #fff !important;
    border-left: 2px solid #000 !important;
  }

  /* Prevent bad page breaks - force single page */
  .award-list-table,
  .award-list-table tbody,
  .award-list-table tr {
    page-break-inside: avoid !important;
    page-break-after: avoid !important;
  }

  .award-list-table tbody tr {
    page-break-inside: avoid !important;
  }

  /* Column width adjustments for print - use percentages to fit page */
  .col-roll {
    width: 6% !important;
  }

  .col-student {
    width: 18% !important;
  }

  .col-father {
    width: 18% !important;
  }

  .col-subject {
    width: auto !important;
    min-width: 0 !important;
  }

  .col-total {
    width: 8% !important;
  }

  .marks-total {
    font-size: 7px !important;
    line-height: 1;
  }

  .marks-cell {
    background-color: #f5f5f5 !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

  .total-cell {
    background-color: #e8e8e8 !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
}
</style>
