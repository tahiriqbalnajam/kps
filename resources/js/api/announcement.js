import request from '@/utils/request'

export function getAnnouncementList(params) {
  return request({ url: '/announcements', method: 'get', params })
}

export function getAnnouncement(id) {
  return request({ url: `/announcements/${id}`, method: 'get' })
}

export function createAnnouncement(data) {
  return request({ url: '/announcements', method: 'post', data })
}

export function updateAnnouncement(id, data) {
  return request({ url: `/announcements/${id}`, method: 'put', data })
}

export function deleteAnnouncement(id) {
  return request({ url: `/announcements/${id}`, method: 'delete' })
}

export function toggleAnnouncementStatus(id) {
  return request({ url: `/announcements/${id}/toggle`, method: 'patch' })
}
