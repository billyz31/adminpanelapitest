import axios from 'axios'

// Configure axios defaults for API requests
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.post['Content-Type'] = 'application/json'
axios.defaults.headers.put['Content-Type'] = 'application/json'
axios.defaults.headers.patch['Content-Type'] = 'application/json'

// Set base URL
axios.defaults.baseURL = import.meta.env.VITE_API_BASE_URL || ''

// Add request interceptor to include token
axios.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('admin_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

export default axios
