import request from '@/utils/request';

export function processSMS() {
  return request({
    url: '/sendsms',
    method: 'get',
  });
}

export function completeSMS() {
  return request({
    url: '/change_status',
    method: 'post',
  });
}
