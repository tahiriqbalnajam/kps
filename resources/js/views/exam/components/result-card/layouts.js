import StandardResultCard from './StandardResultCard.vue';
import WideMarksSheet from './WideMarksSheet.vue';

/**
 * Result card layout registry.
 *
 * Adding a layout = add the component + a `printCss` string + one entry below. The Settings
 * dropdown and the print-time picker both read this list, so nothing else needs touching.
 *
 * `printCss` is injected into the print window by `PrintReports.printReports()` because that
 * window is built with `document.write()` from `reportsRef.innerHTML` — an SFC `<style scoped>`
 * block lives in the app bundle and never reaches it. Each component also carries a matching
 * `<style scoped>` block for the on-screen drawer preview; keep the two in sync.
 */

/* Moved verbatim from PrintReports.vue so the standard printed output is unchanged. */
export const STANDARD_PRINT_CSS = `
@page {
  size: A4;
  margin: 0.5cm;  /* Reduced margins */
}
@media print {
.report-container {
  padding: 20px;
}
.controls {
  margin-bottom: 20px;
}
.report-card {
  padding: 15px;
  margin-bottom: 20px;
  page-break-inside: avoid;
  min-height: auto;
  max-height: 297mm;
  display: flex;
  flex-direction: column;
}
.report-header {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
  padding-bottom: 15px;
  border-bottom: 2px solid #333;
}
.logo-section img {
  height: 100px;
  margin-right: 15px;
}
.school-info {
  flex-grow: 1;
  text-align: center;
}
.school-name {
  font-size: 46px;
  font-weight: bold;
  margin-bottom: 5px;
}
.student-info-section {
  margin-bottom: 15px;
}
.info-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}
.results-section {
  display: flex;
  gap: 15px;
  margin-bottom: 15px;
}
.marks-section {
  flex: 2;
}
.assessment-section {
  flex: 1;
  padding: 15px;
  background: #f9f9f9;
  max-height: 200px;
}
.marks-table {
  width: 100%;
  border-collapse: collapse;
}
.marks-table th,
.marks-table td {
  border: 1px solid #ddd;
  padding: 4px 0;
  text-align: center;
  font-size: 26px !important;
}
.total-row {
  font-weight: bold;
  background: #f5f5f5;
}
.footer-section {
  margin-top: 15px;
}
.remarks {
  margin-bottom: 30px;
}
.signatures {
  display: flex;
  justify-content: space-between;
  margin-top: 20px;
}
.signature-item {
  text-align: center;
}
.signature-item .line {
  display: block;
  margin-bottom: 5px;
}
@media print {
  .controls {
    display: none;
  }

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
  .reports {
    max-width: 21cm;
    margin: 0 auto;
    background: white;
  }

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
}
`;

/** A4 landscape "Marks Sheet", per the school's Result Card 27 format. */
export const WIDE_PRINT_CSS = `
@page {
  size: A4 landscape;
  margin: 8mm;
}
@media print {
.controls {
  display: none;
}
}
.wide-sheet {
  border: 4px double #333;
  padding: 6mm 7mm;
  box-sizing: border-box;
  page-break-inside: avoid;
  page-break-after: always;
}
.wide-sheet .sheet-header {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  text-align: center;
  border-bottom: 2px solid #333;
  padding-bottom: 8px;
}
.wide-sheet .sheet-header img {
  height: 62px;
}
.wide-sheet .school-name {
  font-size: 30px;
  font-weight: 800;
  margin: 0;
  letter-spacing: 0.5px;
}
.wide-sheet .school-meta {
  margin: 2px 0 0;
  font-size: 13px;
}
/* padding, not whitespace — spaces around an inline element collapse away */
.wide-sheet .school-meta .meta-sep {
  padding: 0 6px;
}
.wide-sheet .sheet-title {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  font-size: 20px;
  font-weight: 700;
  margin: 10px 0 8px;
}
.wide-sheet .sheet-student {
  font-size: 15px;
  margin-bottom: 10px;
}
.wide-sheet .sheet-student .info-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 6px;
}
.wide-sheet .sheet-student .info-item {
  display: flex;
  align-items: baseline;
  gap: 6px;
  width: 48%;
}
.wide-sheet .sheet-student .label {
  white-space: nowrap;
}
.wide-sheet .sheet-student .value {
  flex: 1;
  border-bottom: 1px solid #999;
  font-weight: 700;
  padding: 0 4px 2px;
}
.wide-sheet .marks-table {
  width: 100%;
  border-collapse: collapse;
}
.wide-sheet .marks-table th,
.wide-sheet .marks-table td {
  border: 1px solid #333;
  text-align: center;
  padding: 6px 8px;
  font-size: 15px;
}
.wide-sheet .marks-table thead th {
  background: #f0f0f0;
  font-weight: 700;
}
.wide-sheet .marks-table .col-sr {
  width: 8%;
}
.wide-sheet .marks-table .col-subject {
  width: 52%;
}
.wide-sheet .marks-table td.subject-cell {
  text-align: left;
}
.wide-sheet .marks-table tbody td {
  height: 30px;
}
.wide-sheet .marks-table .total-row {
  font-weight: 700;
  background: #f5f5f5;
}
.wide-sheet .sheet-footer {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: 8px 10px;
  margin-top: 12px;
  font-size: 15px;
}
/* the six footer items sit on one line: they share the free space, so the fill-in rules
   stretch instead of the row wrapping to a second line */
.wide-sheet .sheet-footer .footer-item {
  display: flex;
  align-items: baseline;
  gap: 6px;
  flex: 1 1 auto;
}
.wide-sheet .sheet-footer .footer-item .label {
  white-space: nowrap;
}
.wide-sheet .sheet-footer .blank {
  flex: 1 1 0;
  min-width: 45px;
}
.wide-sheet .sheet-footer .value {
  font-weight: 700;
}
.wide-sheet .sheet-remarks {
  display: flex;
  align-items: baseline;
  gap: 6px;
  margin-top: 14px;
  font-size: 15px;
}
.wide-sheet .blank {
  display: inline-block;
  border-bottom: 1px solid #999;
  min-width: 90px;
}
.wide-sheet .sheet-remarks .remarks-line {
  flex: 1;
}
.wide-sheet .sheet-signatures {
  display: flex;
  justify-content: space-between;
  margin-top: 34px;
}
.wide-sheet .sheet-signatures .sig {
  width: 30%;
  text-align: center;
}
.wide-sheet .sheet-signatures .sig .line {
  display: block;
  border-top: 1px solid #333;
  margin-bottom: 4px;
}
@media print {
  /* no trailing blank landscape sheet after the last student */
  .wide-sheet:last-child {
    page-break-after: auto;
  }
}
@media screen {
  .reports {
    max-width: 297mm;
    margin: 0 auto;
    background: white;
  }
  .wide-sheet {
    background: #fff;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    margin-bottom: 24px;
  }
}
`;

export const RESULT_CARD_LAYOUTS = [
  {
    key: 'standard',
    label: 'Standard (A4 Portrait)',
    component: StandardResultCard,
    printCss: STANDARD_PRINT_CSS,
  },
  {
    key: 'wide_marks_sheet',
    label: 'Wide Marks Sheet (A4 Landscape)',
    component: WideMarksSheet,
    printCss: WIDE_PRINT_CSS,
  },
];

export const DEFAULT_RESULT_CARD_LAYOUT = 'standard';

/** Resolve a layout key, falling back to the default for unknown/removed keys. */
export function getResultCardLayout(key) {
  return RESULT_CARD_LAYOUTS.find((layout) => layout.key === key) || RESULT_CARD_LAYOUTS[0];
}

/** localStorage key for the print-time override (mirrors the fee voucher orientation pattern). */
export const RESULT_CARD_LAYOUT_STORAGE_KEY = 'result_card_layout';
