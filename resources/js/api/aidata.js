import request from '@/utils/request';

/**
 * Run a natural-language query against the AI Data Agent.
 * Returns the Axios response body: { success, data, error } where `data`
 * is the agent's structured payload { summary, highlights, table, chart, sources }.
 */
export function queryAgent(message) {
  return request({
    url: '/ai-agent/query',
    method: 'post',
    data: { message },
  });
}