import request from '@/utils/request';
export function addAttendance(data) {
  return request({
    url: '/addattendance',
    method: 'post',
    data,
  });
}

export function studentAttReport(data) {
  return request({
    url: '/student_att_report',
    method: 'get',
    params: data,
  });
}

export function editClass(stdData){
  return request({
    url: '/edit_class',
    method: 'post',
    data: stdData,
  });
}