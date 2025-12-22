import axios from 'axios'

// Configure axios defaults for API requests
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.post['Content-Type'] = 'application/json'
axios.defaults.headers.put['Content-Type'] = 'application/json'
axios.defaults.headers.patch['Content-Type'] = 'application/json'

// Set base URL
axios.defaults.baseURL = import.meta.env.VITE_API_BASE_URL || ''

export default axios