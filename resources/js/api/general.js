import request from '@/utils/request';

export function processSMS() {
  return request({
    url: '/sendsms',
    method: 'get',
  });
}
export function processWhatsApp() {
  return request({
    url: '/send/whatsapp',
    method: 'get',
  });
}
export function updateSendStatusWhatsApp(message_ids, status) {
  return request({
    url: '/update/whatsapp/status',
    method: 'post',
      data: {
          message_ids: message_ids,
        status: status,
      }
  });
}
export function getDefaultMessageChannel() {
  return request({
    url: '/default/message/channel',
    method: 'get',
  });
}

export function completeSMS() {
  return request({
    url: '/change_status',
    method: 'post',
  });
}

export function deleteAllSMS() {
  return request({
    url: '/smsqueue/delete-all',
    method: 'delete',
  });
}
