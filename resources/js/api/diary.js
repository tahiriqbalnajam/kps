import request from '@/utils/request'

export function getDiaryList(params) {
  return request({ url: '/diary/', method: 'get', params })
}

export function getSubjectsByClass(class_id) {
  return request({ url: '/diary/subjects', method: 'get', params: { class_id } })
}

export function getDiaryByDate(params) {
  return request({ url: '/diary/by-date', method: 'get', params })
}

export function getDiaryDates(params) {
  return request({ url: '/diary/dates', method: 'get', params })
}

export function saveDiary(data) {
  return request({ url: '/diary/save', method: 'post', data })
}

export function deleteDiaryGroup(params) {
  return request({ url: '/diary/group', method: 'delete', params })
}

export function getStudentDiary(params) {
  return request({ url: '/diary/student-view', method: 'get', params })
}
