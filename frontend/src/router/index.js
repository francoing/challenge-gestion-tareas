import { createRouter, createWebHistory } from 'vue-router'

import TareasView from '@/features/tareas/views/TareasView.vue'

const routes = [
  {
    path: '/',
    name: 'tareas',
    component: TareasView,
  },
  // Cualquier ruta desconocida vuelve al listado.
  {
    path: '/:rutaInexistente(.*)*',
    redirect: { name: 'tareas' },
  },
]

export default createRouter({
  history: createWebHistory(),
  routes,
})
