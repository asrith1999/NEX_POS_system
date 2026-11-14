import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  headers: { Accept: 'application/json' },
  withCredentials: true,
})

export async function refreshCsrf() {
  const { data } = await api.get('/csrf')
  api.defaults.headers.common['X-CSRF-TOKEN'] = data.token
}

export default api
