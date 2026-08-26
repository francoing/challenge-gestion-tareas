import { createApp } from 'vue'
import { createPinia } from 'pinia'

import { alFallar } from '@/api/http'
import { useNotificacionesStore } from '@/stores/notificaciones'

import App from './App.vue'
import router from './router'
import './style.css'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

// Puente entre el interceptor HTTP y el store de avisos.
// Se arma acá, en la raíz de composición, y no dentro de http.js: si el
// interceptor importara el store se formaría un ciclo
// (store -> service -> http -> store). Así la dependencia va en un solo
// sentido y http.js sigue sin conocer Pinia
const notificaciones = useNotificacionesStore(pinia)
alFallar((error) => notificaciones.error(error.message))

app.mount('#app')
