import request from '@/utils/request';
export function getAttendacne(data) {
  return request({
    url: '/get_teacher_attandance',
    method: 'post',
    data,
  });
}

export function allTeachersPay(data) {
  return request({
    url: '/teacher/all-teaches-pay',
    method: 'post',
    data,
  });
}

export function findSavedPay(data) {
  return request({
    url: '/teacher/find_save_salary',
    method: 'post',
    data,
  });
}

export function checkSalaryGenerated(data) {
  return request({
    url: '/check_salary_generated',
    method: 'post',
    data,
  });
}

export function generatePay(data) {
  return request({
    url: '/generate_pay',
    method: 'post',
    data,
  });
}
export function saveSalary(data) {
  return request({
    url: '/save_salary',
    method: 'post',
    data,
  });
}