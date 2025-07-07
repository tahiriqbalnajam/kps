import request from '@/utils/request';
import Resource from '@/api/resource';

class ComplaintResource extends Resource {
  constructor() {
    super('complaints');
  }

  getStudents(params) {
    return request({
      url: `/${this.uri}/students`,
      method: 'get',
      params,
    });
  }

  getTeachers(params) {
    return request({
      url: `/${this.uri}/teachers`,
      method: 'get',
      params,
    });
  }

  updateStatus(id, data) {
    return request({
      url: `/${this.uri}/${id}/status`,
      method: 'patch',
      data,
    });
  }
}

export { ComplaintResource as default };
