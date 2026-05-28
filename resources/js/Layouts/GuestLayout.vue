<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-orange-50 via-white to-red-50 px-4 py-12 sm:px-6 lg:px-8"
    >
        <!-- Contenedor principal -->
        <div class="w-full max-w-md">
            <!-- Tarjeta del formulario -->
            <div
                class="rounded-2xl bg-white px-6 py-8 shadow-xl shadow-gray-200/50 ring-1 ring-gray-100 sm:px-10 sm:py-10"
            >
                <slot />
            </div>

            <!-- Footer -->
            <p class="mt-8 text-center text-xs text-gray-400">
                &copy; {{ new Date().getFullYear() }} Churrasquería — Todos los derechos
                reservados
            </p>
        </div>
    </div>
</template>
