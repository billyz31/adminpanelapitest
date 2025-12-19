import { defineStore } from 'pinia'
import axios from 'axios'
import router from '../router'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as any,
    token: localStorage.getItem('token') || '',
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
  },
  actions: {
    async login(credentials: any) {
      try {
        const response = await axios.post('/api/login', credentials)
        this.token = response.data.token
        this.user = response.data.user
        localStorage.setItem('token', this.token)
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
        router.push('/lobby')
        return response
      } catch (error) {
        throw error
      }
    },
    async register(data: any) {
      try {
        const response = await axios.post('/api/register', data, {
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        })
        this.token = response.data.token
        this.user = response.data.user
        localStorage.setItem('token', this.token)
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
        router.push('/lobby')
        return response
      } catch (error) {
        throw error
      }
    },
    async logout() {
      try {
        await axios.post('/api/logout')
      } catch (error) {
        console.error(error)
      } finally {
        this.token = ''
        this.user = null
        localStorage.removeItem('token')
        delete axios.defaults.headers.common['Authorization']
        router.push('/login')
      }
    },
    async fetchUser() {
        if (!this.token) return
        try {
            axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
            const response = await axios.get('/api/me')
            this.user = response.data
        } catch (error) {
            this.logout()
        }
    }
  }
})
