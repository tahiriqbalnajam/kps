import request from '@/utils/request';

store(data) {
    return request({
      url: '/' + this.uri,
      method: 'post',
      data: resource,
    });
  }