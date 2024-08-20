import request from '@/utils/request';


export function getStudentAttendance(id) {
    return request({
      url: '/attendance_student_monthly/' + id,
      method: 'get',
    });
}

export function studentAttMarked(data) {
    return request({
      url: '/student_attendance_marked',
      method: 'post',
      data,
    });
}

export function studentAttMonthlyReport(data) {
    return request({
      url: '/student_monthly_attendance_report',
      method: 'post',
      data,
    });
}

export function getDailyClasswise(data) {
  return request({
    url: '/student_daily_classwise_attendance_report',
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

export function absentForeachClass(data) {
  return request({
    url: '/absent_student_each_class',
    method: 'get',
    params: data,
  });
}
export function addAbsentComment(data) {
  return request({
    url: '/absent_comment',
    method: 'post',
    data,
  });
}

export function getStudentAttTotals(id) {
  return request({
    url: '/student_attendance_total'+ '/' + id,
    method: 'get',
  });
}

export function teacherMonthlyAttReport(data) {
  return request({
    url: '/teachers_monthly_att_report',
    method: 'post',
    data,
  });
}
