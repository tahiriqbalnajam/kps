import request from '@/utils/request';

export function getDailyClasswise(data) {
    return request({
      url: '/getdailyclasswise',
      method: 'post',
      data,
    });
}

export function getStudentAttendance(id) {
    return request({
      url: '/attendance_student_monthly/' + id,
      method: 'get',
    });
  }