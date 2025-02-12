import request from '@/utils/request';

export function addExam(data) {
  return request({
    url: '/exam',
    method: 'post',
    data,
  });
}

export function getDailyClasswise(data) {
  return request({
    url: '/getdailyclasswise',
    method: 'post',
    data,
  });
}

export function editClass(stdData) {
  return request({
    url: '/edit_class',
    method: 'post',
    data: stdData,
  });
}

export function fetchClasses() {
  return request({
    url: '/classes',
    method: 'get',
  });
}

export function fetchSubjectsByClass(class_id) {
  return request({
    url: `/subject_class/${class_id}`,
    method: 'get',
  });
}

export function createExam(data) {
  return request({
    url: '/exams',
    method: 'post',
    data,
  });
}

export function fetchStudentsByClass(class_id) {
  return request({
    url: `/students/class/${class_id}`,
    method: 'get',
  });
}

export function fetchExamSubjects(exam_id) {
  return request({
    url: `/exams/${exam_id}/subjects`,
    method: 'get',
  });
}

export function submitMarks(exam_id, marks) {
  return request({
    url: `/exams/${exam_id}/marks`,
    method: 'post',
    data: { marks },
  });
}

export function examMarks(data) {
  return request({
    url: `/exams/marks`,
    method: 'post',
    data: data,
  });
}

export function getSubjectsMarksByExamId(exam_id) {
  return request({
    url: `/exams/${exam_id}/subjects/marks`,
    method: 'get'
  });
}

export function getExamReports(examId) {
  return request({
    url: `/exams/${examId}/reports`,
    method: 'get',
  });
}