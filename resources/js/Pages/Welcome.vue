<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});

const loaded = ref(false);

onMounted(() => {
    setTimeout(() => {
        loaded.value = true;
    }, 100);
});
</script>

<template>
    <Head title="Churrasquería - El Arte del Fuego" />

    <div class="relative min-h-screen overflow-hidden bg-white">
        <!-- Fondo decorativo: degradado cálido -->
        <div class="absolute inset-0 bg-gradient-to-br from-orange-50 via-white to-red-50"></div>

        <!-- Círculos decorativos de fondo -->
        <div
            class="absolute -top-40 -right-40 h-96 w-96 rounded-full bg-orange-100/50 blur-3xl"
        ></div>
        <div
            class="absolute -bottom-40 -left-40 h-96 w-96 rounded-full bg-red-100/50 blur-3xl"
        ></div>

        <!-- Contenido principal -->
        <div class="relative flex min-h-screen flex-col">
            <!-- Header / Navbar -->
            <header
                class="border-b border-gray-100 bg-white/80 backdrop-blur-md"
            >
                <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                    <!-- Logo -->
                    <Link href="/" class="flex items-center gap-3 group">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-orange-400 to-red-500 shadow-md transition-transform duration-300 group-hover:scale-110"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6 text-white"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                            >
                                <path
                                    d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm0 18c-4.41 0-8-3.59-8-8 0-2.66 1.33-5.02 3.35-6.44l.71 1.78c.6-.3 1.26-.34 1.88-.22.83.15 1.55.67 2 1.37.45-.7 1.17-1.22 2-1.37.62-.12 1.28-.08 1.88.22l.71-1.78C18.67 6.98 20 9.34 20 12c0 4.41-3.59 8-8 8zm-1-5h2v2h-2zm0-8h2v6h-2z"
                                />
                            </svg>
                        </div>
                        <span
                            class="text-xl font-bold text-gray-900 transition-colors duration-300 group-hover:text-orange-600"
                        >
                            Churrasquería
                        </span>
                    </Link>

                    <!-- Navegación -->
                    <nav v-if="canLogin" class="flex items-center gap-3">
                        <template v-if="$page.props.auth.user">
                            <Link
                                :href="route('dashboard')"
                                class="rounded-lg bg-gradient-to-r from-orange-500 to-red-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md transition-all duration-300 hover:from-orange-600 hover:to-red-600 hover:shadow-lg"
                            >
                                Dashboard
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition-all duration-300 hover:border-orange-300 hover:text-orange-600 hover:shadow-md"
                            >
                                Iniciar sesión
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="rounded-lg bg-gradient-to-r from-orange-500 to-red-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md transition-all duration-300 hover:from-orange-600 hover:to-red-600 hover:shadow-lg"
                            >
                                Registrarse
                            </Link>
                        </template>
                    </nav>
                </div>
            </header>

            <!-- Hero Section -->
            <main class="flex flex-1 items-center">
                <div
                    class="mx-auto w-full max-w-7xl px-4 py-16 sm:px-6 lg:px-8"
                >
                    <div
                        class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16"
                    >
                        <!-- Texto principal -->
                        <div
                            class="space-y-8 transition-all duration-1000"
                            :class="{
                                'translate-y-0 opacity-100': loaded,
                                'translate-y-8 opacity-0': !loaded,
                            }"
                        >
                            <!-- Badge -->
                            <div class="inline-flex items-center gap-2 rounded-full bg-orange-100 px-4 py-1.5">
                                <span
                                    class="relative flex h-2 w-2"
                                >
                                    <span
                                        class="absolute inline-flex h-full w-full animate-ping rounded-full bg-orange-400 opacity-75"
                                    ></span>
                                    <span
                                        class="relative inline-flex h-2 w-2 rounded-full bg-orange-500"
                                    ></span>
                                </span>
                                <span class="text-sm font-medium text-orange-700"
                                    >El arte del fuego</span
                                >
                            </div>

                            <!-- Título -->
                            <h1
                                class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl lg:text-6xl"
                            >
                                Donde el
                                <span
                                    class="bg-gradient-to-r from-orange-500 to-red-500 bg-clip-text text-transparent"
                                >
                                    fuego
                                </span>
                                encuentra el
                                <span
                                    class="bg-gradient-to-r from-orange-500 to-red-500 bg-clip-text text-transparent"
                                >
                                    sabor
                                </span>
                            </h1>

                            <!-- Descripción -->
                            <p class="text-lg leading-relaxed text-gray-600">
                                Bienvenido a la mejor experiencia gastronómica.
                                Disfruta de los cortes más selectos, asados a la
                                perfección en brasas de quebracho. Una tradición
                                que se enciende en cada plato.
                            </p>

                            <!-- Características -->
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div
                                    class="flex items-center gap-3 rounded-xl bg-white p-4 shadow-md ring-1 ring-gray-100"
                                >
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-full bg-orange-100"
                                    >
                                        <svg
                                            class="h-5 w-5 text-orange-600"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700"
                                        >Fuego real</span
                                    >
                                </div>
                                <div
                                    class="flex items-center gap-3 rounded-xl bg-white p-4 shadow-md ring-1 ring-gray-100"
                                >
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100"
                                    >
                                        <svg
                                            class="h-5 w-5 text-red-600"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"
                                            />
                                            <path
                                                fill-rule="evenodd"
                                                d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700"
                                        >Cortes premium</span
                                    >
                                </div>
                                <div
                                    class="flex items-center gap-3 rounded-xl bg-white p-4 shadow-md ring-1 ring-gray-100"
                                >
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100"
                                    >
                                        <svg
                                            class="h-5 w-5 text-amber-600"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700"
                                        >Calidad garantizada</span
                                    >
                                </div>
                            </div>

                            <!-- Botones CTA -->
                            <div class="flex flex-wrap gap-4">
                                <Link
                                    v-if="canRegister && !$page.props.auth.user"
                                    :href="route('register')"
                                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-red-500 px-8 py-4 text-base font-bold text-white shadow-xl transition-all duration-300 hover:from-orange-600 hover:to-red-600 hover:shadow-2xl hover:scale-105"
                                >
                                    Comenzar ahora
                                    <svg
                                        class="h-5 w-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13 7l5 5m0 0l-5 5m5-5H6"
                                        />
                                    </svg>
                                </Link>
                                <Link
                                    v-else-if="canLogin && !$page.props.auth.user"
                                    :href="route('login')"
                                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-red-500 px-8 py-4 text-base font-bold text-white shadow-xl transition-all duration-300 hover:from-orange-600 hover:to-red-600 hover:shadow-2xl hover:scale-105"
                                >
                                    Iniciar sesión
                                    <svg
                                        class="h-5 w-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"
                                        />
                                    </svg>
                                </Link>
                                <Link
                                    v-else
                                    :href="route('dashboard')"
                                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-red-500 px-8 py-4 text-base font-bold text-white shadow-xl transition-all duration-300 hover:from-orange-600 hover:to-red-600 hover:shadow-2xl hover:scale-105"
                                >
                                    Ir al Dashboard
                                    <svg
                                        class="h-5 w-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13 7l5 5m0 0l-5 5m5-5H6"
                                        />
                                    </svg>
                                </Link>
                                <a
                                    href="#menu"
                                    class="inline-flex items-center gap-2 rounded-xl border-2 border-gray-300 bg-white px-8 py-4 text-base font-bold text-gray-700 shadow-md transition-all duration-300 hover:border-orange-300 hover:text-orange-600 hover:shadow-lg"
                                >
                                    Ver menú
                                    <svg
                                        class="h-5 w-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 9l-7 7-7-7"
                                        />
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <!-- Imagen hero / Parrilla -->
                        <div
                            class="relative transition-all duration-1000 delay-300"
                            :class="{
                                'translate-y-0 opacity-100': loaded,
                                'translate-y-8 opacity-0': !loaded,
                            }"
                        >
                            <!-- Contenedor de la imagen -->
                            <div
                                class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-orange-400 via-red-400 to-amber-500 p-1 shadow-2xl"
                            >
                                <div
                                    class="relative aspect-[4/3] overflow-hidden rounded-[1.35rem] bg-gray-900"
                                >
                                    <!-- Imagen placeholder de parrilla -->
                                    <div
                                        class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-gray-800 to-gray-900"
                                    >
                                        <!-- SVG de parrilla grande -->
                                        <div class="text-center">
                                            <svg
                                                class="mx-auto h-32 w-32 text-orange-500"
                                                fill="currentColor"
                                                viewBox="0 0 64 64"
                                            >
                                                <path
                                                    d="M32 4C16.536 4 4 16.536 4 32s12.536 28 28 28 28-12.536 28-28S47.464 4 32 4zm0 52C18.745 56 8 45.255 8 32S18.745 8 32 8s24 10.745 24 24-10.745 24-24 24z"
                                                />
                                                <path
                                                    d="M32 12c-11.028 0-20 8.972-20 20s8.972 20 20 20 20-8.972 20-20-8.972-20-20-20zm0 36c-8.837 0-16-7.163-16-16s7.163-16 16-16 16 7.163 16 16-7.163 16-16 16z"
                                                />
                                                <path d="M12 28h40v4H12z" />
                                                <path d="M16 20h4v24h-4z" />
                                                <path d="M28 14h4v36h-4z" />
                                                <path d="M40 20h4v24h-4z" />
                                                <path d="M52 28h4v4h-4z" />
                                                <!-- Llamas -->
                                                <path
                                                    d="M28 48c-2 0-4-1-4-3s1.5-4 4-6c2.5 2 4 4 4 6s-2 3-4 3z"
                                                    fill="#f97316"
                                                />
                                                <path
                                                    d="M38 50c-1.5 0-3-1-3-2.5s1-3.5 3-5c2 1.5 3 3.5 3 5S39.5 50 38 50z"
                                                    fill="#ef4444"
                                                />
                                                <path
                                                    d="M22 44c-1 0-2-.5-2-1.5s1-2.5 2-3.5c1 1 2 2.5 2 3.5s-1 1.5-2 1.5z"
                                                    fill="#fbbf24"
                                                />
                                            </svg>
                                            <p
                                                class="mt-4 text-lg font-semibold text-orange-400"
                                            >
                                                ¡Brasas encendidas!
                                            </p>
                                            <p class="text-sm text-gray-400">
                                                Listo para servir
                                            </p>
                                        </div>
                                    </div>
                                    <!-- Overlay con gradiente -->
                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-orange-900/40 to-transparent"
                                    ></div>
                                </div>
                            </div>

                            <!-- Elementos flotantes decorativos -->
                            <div
                                class="absolute -left-6 top-12 rounded-2xl bg-white p-4 shadow-xl ring-1 ring-gray-100 animate-bounce-slow"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-100"
                                    >
                                        <svg
                                            class="h-6 w-6 text-orange-600"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900">+500</p>
                                        <p class="text-xs text-gray-500">Clientes felices</p>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="absolute -right-4 -bottom-6 rounded-2xl bg-white p-4 shadow-xl ring-1 ring-gray-100 animate-bounce-slow"
                                style="animation-delay: 0.5s"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100"
                                    >
                                        <svg
                                            class="h-6 w-6 text-red-600"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900">100%</p>
                                        <p class="text-xs text-gray-500">Satisfacción</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="border-t border-gray-100 bg-white/80 backdrop-blur-md">
                <div
                    class="mx-auto flex max-w-7xl items-center justify-between px-4 py-6 sm:px-6 lg:px-8"
                >
                    <p class="text-sm text-gray-500">
                        &copy; {{ new Date().getFullYear() }} Churrasquería — El
                        arte del fuego
                    </p>
                    <div class="flex items-center gap-4">
                        <a
                            href="#"
                            class="text-gray-400 transition-colors hover:text-orange-600"
                        >
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"
                                />
                            </svg>
                        </a>
                        <a
                            href="#"
                            class="text-gray-400 transition-colors hover:text-orange-600"
                        >
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"
                                />
                            </svg>
                        </a>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>

<style scoped>
@keyframes bounce-slow {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

.animate-bounce-slow {
    animation: bounce-slow 3s ease-in-out infinite;
}
</style>
