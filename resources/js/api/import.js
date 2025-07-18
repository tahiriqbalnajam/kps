import request from '@/utils/request';

export function uploadFile(data) {
  return request({
    url: '/import/upload',
    method: 'post',
    data,
  });
}

export function importData(data) {
  return request({
    url: '/import/process',
    method: 'post',
    data,
  });
}