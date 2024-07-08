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