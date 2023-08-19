export function getAttendacne(data) {
    return request({
      url: '/get_teacher_attandance',
      method: 'post',
      data,
    });
  }