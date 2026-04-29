import request from '@/utils/request';
export function addAttendance(data) {
  return request({
    url: '/addattendance',
    method: 'post',
    data,
  });
}

export function editClass(stdData){
  return request({
    url: '/edit_class',
    method: 'post',
    data: stdData,
  });
}

export function getSubjectWiseScores(id) {
  return request({
    url: '/students/' + id + '/subject-wise-scores/',
    method: 'get',
  });
}

export function exportStudent(stdData){
  return request({
    url: 'students/export',
    method: 'post',
    data: stdData,
    responseType: 'blob', // Important for file downloads
  });
}

export function promoteStudents(data) {
  return request({
    url: 'students/promote',
    method: 'post',
    data,
  });
}

export function getProgressReport(id) {
  return request({
    url: '/students/' + id + '/progress-report',
    method: 'get',
  });
}