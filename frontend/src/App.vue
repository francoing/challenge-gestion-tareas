<!--
  Componente raíz: layout general y punto de montaje de las vistas.
-->
<template>
  <div class="min-h-screen bg-slate-50">
    <header class="border-b border-slate-200 bg-white">
      <div class="mx-auto max-w-6xl px-4 py-4 sm:px-6">
        <h1 class="text-lg font-semibold tracking-tight text-slate-900">Gestión de Tareas</h1>
      </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
      <RouterView />
    </main>
  </div>
</template>
