<template>
  <div class="report-card">
    <div class="report-header">
      <div class="logo-section">
        <img v-if="schoolInfo.school_logo" :src="`/${schoolInfo.school_logo}`" alt="School Logo" />
      </div>
      <div class="school-info">
        <h1 class="school-name">{{ schoolInfo.school_name }}</h1>
        <p class="school-address">{{ schoolInfo.address }}</p>
        <p class="school-contact">Phone: {{ schoolInfo.phone }}</p>
      </div>
    </div>

    <div class="student-info-section">
      <div class="info-row">
        <div class="info-item">
          <span class="label">Student Name:</span>
          <span class="value" style="font-size: 26px;"><b>{{ student.name }}</b></span>
        </div>
        <div class="info-item">
          <span class="label">Father's Name:</span>
          <span class="value" style="font-size: 26px;"><b>{{ student.parents.name }}</b></span>
        </div>
      </div>
      <div class="info-row">
        <div class="info-item">
          <span class="label">Class:</span>
          <span class="value" style="font-size: 26px;"><b>{{ exam.classes.name }}</b></span>
        </div>
        <div class="info-item">
          <span class="label">Roll Number:</span>
          <span class="value" style="font-size: 26px;"><b>{{ student.roll_no }}</b></span>
        </div>
      </div>
    </div>

    <div class="results-section">
      <div class="marks-section">
        <table class="marks-table">
          <thead>
            <tr>
              <th>Subject</th>
              <th>Total Marks</th>
              <th>Obtained Marks</th>
              <th>Percentage</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="mark in rows" :key="mark.subject">
              <td>{{ mark.subject }}</td>
              <td>{{ mark.total_marks }}</td>
              <td>{{ mark.obtained_marks }}</td>
              <td>{{ mark.percentage }}%</td>
            </tr>
            <tr class="total-row">
              <td>Total</td>
              <td>{{ totalMarks }}</td>
              <td>{{ obtainedMarks }}</td>
              <td>{{ percentage }}%</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="results-section">
      <div class="assessment-section">
        <h3 style="font-size: 26px;">Performance Assessment</h3>
        <div class="assessment-item">
          <span class="label">Grade:</span>
          <span class="line value asses">{{ grade }}</span>
        </div>
        <div class="assessment-item">
          <span class="label">Attendance:</span>
          <span class="line value asses"></span>
        </div>
        <div class="assessment-item">
          <span class="label">Uniform:</span>
          <span class="line value asses"></span>
        </div>
        <div class="assessment-item">
          <span class="label">Behavior:</span>
          <span class="line value asses"></span>
        </div>
      </div>
    </div>

    <div class="footer-section">
      <div class="remarks">
        <span class="label">Remarks:</span>
        <span class="line remarks"></span>
      </div>
      <div class="signatures">
        <div class="signature-item">
          <span class="line w60 "></span>
          <span class="bborder label">Class Teacher</span>
        </div>
        <div class="signature-item">
          <span class="line w60"></span>
          <span class="label">Principal</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
/**
 * Standard A4 portrait result card — the original layout, unchanged.
 *
 * The markup and the <style scoped> block below were moved verbatim out of
 * PrintReports.vue so the printed output stays identical.
 *
 * NOTE: the on-screen styles here mirror the `standard` printCss string in `layouts.js`.
 * They are two copies on purpose — `printReports()` writes `reportsRef.innerHTML` plus that
 * CSS string into a new window, so a scoped block never reaches the printout. Keep them in sync.
 */
export default {
  name: 'StandardResultCard',
  props: {
    student: {
      type: Object,
      required: true,
    },
    schoolInfo: {
      type: Object,
      default: () => ({}),
    },
    exam: {
      type: Object,
      default: () => ({}),
    },
    rows: {
      type: Array,
      default: () => [],
    },
    totalMarks: {
      type: Number,
      default: 0,
    },
    obtainedMarks: {
      type: Number,
      default: 0,
    },
    percentage: {
      type: Number,
      default: 0,
    },
    grade: {
      type: String,
      default: '',
    },
  },
};
</script>

<style scoped>
.report-card {
  padding: 15px;
  margin-bottom: 20px;
  page-break-inside: avoid;
  min-height: auto;  /* Changed from calc(100vh - 40px) */
  display: flex;
  flex-direction: column;
  max-height: 297mm; /* A4 height */
  border: 1px solid #eee;
}
.report-header {
  display: flex;
  align-items: center;
  margin-bottom: 15px;  /* Reduced from 20px */
  padding-bottom: 15px;
  border-bottom: 2px solid #333;
}
.logo-section img {
  height: 100px;  /* Reduced from 80px */
  margin-right: 15px;
}
.school-info {
  flex-grow: 1;
  text-align: center;
}
.school-name {
  font-size: 24px;
  font-weight: bold;
  margin-bottom: 5px;
}
.student-info-section {
  margin-bottom: 15px;  /* Reduced from 20px */
}
.info-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}
.results-section {
  display: flex;
  gap: 15px;  /* Reduced from 20px */
  margin-bottom: 15px;  /* Reduced from 20px */
}
.marks-section {
  flex: 2;
}
.assessment-section {
  flex: 1;
  padding: 15px;
  background: #f9f9f9;
  max-height: 200px;  /* Reduced from 250px */
}
.marks-table {
  width: 100%;
  border-collapse: collapse;
}
.marks-table th,
.marks-table td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: center;
  font-size: 18px;
}
.total-row {
  font-weight: bold;
  background: #f5f5f5;
}
.footer-section {
  margin-top: 15px;  /* Reduced from 40px */
}
.remarks {
  margin-bottom: 30px;
}
.signatures {
  display: flex;
  justify-content: space-between;
  margin-top: 20px;  /* Reduced from 40px */
}
.signature-item {
  text-align: center;
}
.signature-item .line {
  display: block;
  margin-bottom: 5px;
}
@media print {
  .report-card {
    page-break-after: always;
  }
}

/* Additional styles for better print preview */
.report-card {
  border: 1px solid #eee;
  margin-bottom: 30px;
  min-height: calc(100vh - 40px);
  display: flex;
  flex-direction: column;
}

.results-section {
  flex: 1;
}

.footer-section {
  margin-top: auto;
  padding-top: 20px;
}

@media screen {
  .report-card {
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
  }
}
.label {
  margin-right: 5px;
  font-size: 18px;
}
.value {

}
.line {
  border-bottom: 1px solid #ccc;
}
.school-address{
  margin: 0;
}
.school-contact {
  margin: 0;
}
.remarks {
  width: 90%;
}
.asses {
  width: 50%;
  font-size: 18px;
  font-weight: bold;
}
.assessment-item {
    margin-bottom: 10px;
}
</style>
