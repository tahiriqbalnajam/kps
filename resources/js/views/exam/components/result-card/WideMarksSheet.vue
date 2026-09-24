<template>
  <div class="wide-sheet">
    <div class="sheet-header">
      <img v-if="schoolInfo.school_logo" :src="`/${schoolInfo.school_logo}`" alt="School Logo" />
      <div class="header-text">
        <h1 class="school-name">{{ schoolInfo.school_name }}</h1>
        <p v-if="schoolInfo.address || schoolInfo.phone" class="school-meta">
          <span v-if="schoolInfo.address">{{ schoolInfo.address }}</span>
          <span v-if="schoolInfo.address && schoolInfo.phone" class="meta-sep">·</span>
          <span v-if="schoolInfo.phone">Phone: {{ schoolInfo.phone }}</span>
        </p>
      </div>
    </div>

    <div class="sheet-title">
      <span>{{ exam.title }} Marks Sheet</span>
      <span>Session {{ sessionName }}</span>
    </div>

    <div class="sheet-student">
      <div class="info-row">
        <span class="info-item">
          <span class="label">Student Name:</span>
          <span class="value">{{ student.name }}</span>
        </span>
        <span class="info-item">
          <span class="label">Father Name:</span>
          <span class="value">{{ student.parents?.name }}</span>
        </span>
      </div>
      <div class="info-row">
        <span class="info-item">
          <span class="label">Class:</span>
          <span class="value">{{ exam.classes?.name }}</span>
        </span>
        <span class="info-item">
          <span class="label">Roll No:</span>
          <span class="value">{{ student.roll_no }}</span>
        </span>
      </div>
    </div>

    <div class="sheet-body">
      <div class="sheet-main">
        <table class="marks-table">
          <thead>
            <tr>
              <th class="col-sr">Sr.#</th>
              <th class="col-subject">Subject</th>
              <th colspan="2">{{ exam.title }}</th>
            </tr>
            <tr>
              <th></th>
              <th></th>
              <th class="col-total">Total Marks</th>
              <th class="col-obtained">Obtained Marks</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(mark, index) in rows" :key="mark.subject">
              <td>{{ index + 1 }}</td>
              <td class="subject-cell">{{ mark.subject }}</td>
              <td>{{ mark.total_marks }}</td>
              <td>{{ mark.obtained_marks }}</td>
            </tr>
            <tr class="total-row">
              <td colspan="2">Total</td>
              <td>{{ totalMarks }}</td>
              <td>{{ obtainedMarks }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="sheet-side">
        <h2 class="side-title">Performance Assessment</h2>
        <div class="side-list">
          <div class="side-item">
            <span class="label">{{ exam.title }} Percentage:</span>
            <span class="value">{{ percentage }}%</span>
          </div>
          <div class="side-item">
            <span class="label">Grade:</span>
            <span class="value">{{ grade }}</span>
          </div>
          <div class="side-item">
            <span class="label">Attendance:</span>
            <span class="blank"></span>
          </div>
          <div class="side-item">
            <span class="label">Uniform:</span>
            <span class="blank"></span>
          </div>
          <div class="side-item">
            <span class="label">Behavior:</span>
            <span class="blank"></span>
          </div>
          <div class="side-item">
            <span class="label">Parent's Signature:</span>
            <span class="blank"></span>
          </div>
        </div>
      </div>
    </div>

    <div class="sheet-remarks">
      <span class="label">Remarks:</span>
      <span class="blank remarks-line"></span>
    </div>

    <div class="sheet-signatures">
      <div class="sig">
        <span class="line"></span>
        <span class="label">Teacher Signature</span>
      </div>
      <div class="sig">
        <span class="line"></span>
        <span class="label">Coordinator Signature</span>
      </div>
      <div class="sig">
        <span class="line"></span>
        <span class="label">Principal Signature</span>
      </div>
    </div>
  </div>
</template>

<script>
/**
 * Wide marks sheet — A4 landscape, per the school's "Result Card 27" format.
 *
 * Marks come from the exam; Attendance / Uniform / Behavior / Parent's Signature are left as
 * blank rules for teachers to fill in by hand.
 *
 * NOTE: the on-screen styles here mirror the `wide_marks_sheet` printCss string in `layouts.js`.
 * They are two copies on purpose — `printReports()` writes `reportsRef.innerHTML` plus that
 * CSS string into a new window, so a scoped block never reaches the printout. Keep them in sync.
 */
export default {
  name: 'WideMarksSheet',
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
    sessionName: {
      type: String,
      default: '',
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
.wide-sheet {
  border: 4px double #333;
  padding: 6mm 7mm;
  box-sizing: border-box;
  page-break-inside: avoid;
  background: #fff;
}
.sheet-header {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  text-align: center;
  border-bottom: 2px solid #333;
  padding-bottom: 8px;
}
.sheet-header img {
  height: 62px;
}
.school-name {
  font-size: 30px;
  font-weight: 800;
  margin: 0;
  letter-spacing: 0.5px;
}
.school-meta {
  margin: 2px 0 0;
  font-size: 13px;
}
/* padding, not whitespace — spaces around an inline element collapse away */
.school-meta .meta-sep {
  padding: 0 6px;
}
.sheet-title {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  font-size: 20px;
  font-weight: 700;
  margin: 10px 0 8px;
}
.sheet-student {
  font-size: 15px;
  margin-bottom: 10px;
}
.sheet-student .info-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 6px;
}
.sheet-student .info-item {
  display: flex;
  align-items: baseline;
  gap: 6px;
  width: 48%;
}
.sheet-student .label {
  white-space: nowrap;
}
.sheet-student .value {
  flex: 1;
  border-bottom: 1px solid #999;
  font-weight: 700;
  padding: 0 4px 2px;
}
.marks-table {
  width: 100%;
  border-collapse: collapse;
}
.marks-table th,
.marks-table td {
  border: 1px solid #333;
  text-align: center;
  padding: 6px 8px;
  font-size: 15px;
}
.marks-table thead th {
  background: #f0f0f0;
  font-weight: 700;
}
/* the table now shares the page with the side panel, so the two mark columns get more room
   than the full-width version needed */
.marks-table .col-sr {
  width: 7%;
}
.marks-table .col-subject {
  width: 43%;
}
/* even mark columns — auto layout would otherwise size them to their header text */
.marks-table .col-total,
.marks-table .col-obtained {
  width: 25%;
}
.marks-table td.subject-cell {
  text-align: left;
}
.marks-table tbody td {
  height: 30px;
}
.marks-table .total-row {
  font-weight: 700;
  background: #f5f5f5;
}
/* marks table on the left, assessment block on the right — the landscape page has the room */
.sheet-body {
  display: flex;
  align-items: stretch;
  gap: 8mm;
}
.sheet-main {
  flex: 1 1 58%;
  min-width: 0;
}
.sheet-side {
  flex: 1 1 42%;
  display: flex;
  flex-direction: column;
  border: 1px solid #333;
  background: #f9f9f9;
  padding: 8px 12px;
}
.side-title {
  font-size: 17px;
  font-weight: 700;
  text-align: center;
  margin: 0 0 8px;
  padding-bottom: 6px;
  border-bottom: 1px solid #ccc;
}
/* spread the rows down to the table's height so both columns end together */
.side-list {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 10px;
}
.side-item {
  display: flex;
  align-items: baseline;
  gap: 6px;
  font-size: 15px;
}
.side-item .label {
  white-space: nowrap;
}
.side-item .value {
  font-weight: 700;
}
.side-item .blank {
  flex: 1 1 0;
  min-width: 90px;
}
.sheet-remarks {
  display: flex;
  align-items: baseline;
  gap: 6px;
  margin-top: 14px;
  font-size: 15px;
}
.blank {
  display: inline-block;
  border-bottom: 1px solid #999;
  min-width: 90px;
}
.sheet-remarks .remarks-line {
  flex: 1;
}
.sheet-signatures {
  display: flex;
  justify-content: space-between;
  margin-top: 34px;
}
.sheet-signatures .sig {
  width: 30%;
  text-align: center;
}
.sheet-signatures .sig .line {
  display: block;
  border-top: 1px solid #333;
  margin-bottom: 4px;
}
@media print {
  .wide-sheet {
    page-break-after: always;
  }
  /* no trailing blank landscape sheet after the last student */
  .wide-sheet:last-child {
    page-break-after: auto;
  }
}
@media screen {
  .wide-sheet {
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    margin-bottom: 24px;
  }
}
</style>
