import request from '@/utils/request';

const syllabusApi = {
  getClasses:        ()          => request({ url: '/classes', method: 'get', params: { limit: 1000 } }),
  getSubjects:       (classId)   => request({ url: `/syllabus/subjects/${classId}`, method: 'get' }),
  getChapters:       (params)    => request({ url: '/syllabus/chapters', method: 'get', params }),
  createChapter:     (data)      => request({ url: '/syllabus/chapters', method: 'post', data }),
  updateChapter:     (id, data)  => request({ url: `/syllabus/chapters/${id}`, method: 'put', data }),
  deleteChapter:     (id)        => request({ url: `/syllabus/chapters/${id}`, method: 'delete' }),
  createTopic:       (data)      => request({ url: '/syllabus/topics', method: 'post', data }),
  updateTopic:       (id, data)  => request({ url: `/syllabus/topics/${id}`, method: 'put', data }),
  deleteTopic:       (id)        => request({ url: `/syllabus/topics/${id}`, method: 'delete' }),
  toggleTopic:       (id)        => request({ url: `/syllabus/topics/${id}/toggle`, method: 'put' }),
  getReport:         (params)    => request({ url: '/syllabus/report', method: 'get', params }),
};

export default syllabusApi;
