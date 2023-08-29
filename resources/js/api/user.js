import request from '@/utils/request';
import Resource from '@/api/resource';

class UserResource extends Resource {
  constructor() {
    super('users');
  }

  permissions(id) {
    return request({
      url: '/' + this.uri + '/' + id + '/permissions',
      method: 'get',
    });
  }

  updatePermission(id, permissions) {
    return request({
      url: '/' + this.uri + '/' + id + '/permissions',
      method: 'put',
      data: permissions,
    });
  }

  logs(id, params) {
    return request({
      url: '/' + this.uri + '/' + id + '/logs',
      method: 'get',
      params: params
    });
  }

  fetchAccDetails() {
    return request({
      url: '/get_accounts',
      method: 'get',
    });
  }
}

export { UserResource as default };
