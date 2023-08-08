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

export function editClass(stdData){
  return request({
    url: '/edit_class',
    method: 'post',
    data: stdData,
  });
}