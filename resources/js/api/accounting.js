import Resource from '@/api/resource';
import request from '@/utils/request';

class AccountingResource extends Resource {
  constructor() {
    super('accounting');
  }

  getDashboard() {
    return request({
      url: '/' + this.uri + '/dashboard',
      method: 'get',
    });
  }

  getCategories(type = null) {
    const params = type ? { type } : {};
    return request({
      url: '/' + this.uri + '/categories',
      method: 'get',
      params,
    });
  }
}

export { AccountingResource as default };
