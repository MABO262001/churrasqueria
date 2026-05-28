<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Crear cuenta" />

        <div class="w-full max-w-md space-y-8">
            <!-- Logo / Marca -->
            <div class="text-center">
                <div
                    class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-orange-400 to-red-500 shadow-lg"
                >
                    <!-- Icono de parrilla/fuego -->
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-10 w-10 text-white"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                    >
                        <path
                            d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm0 18c-4.41 0-8-3.59-8-8 0-2.66 1.33-5.02 3.35-6.44l.71 1.78c.6-.3 1.26-.34 1.88-.22.83.15 1.55.67 2 1.37.45-.7 1.17-1.22 2-1.37.62-.12 1.28-.08 1.88.22l.71-1.78C18.67 6.98 20 9.34 20 12c0 4.41-3.59 8-8 8zm-1-5h2v2h-2zm0-8h2v6h-2z"
                        />
                    </svg>
                </div>
                <h2
                    class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900"
                >
                    Crear cuenta
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Únete a la mejor churrasquería
                </p>
            </div>

            <!-- Formulario -->
            <form class="space-y-5" @submit.prevent="submit">
                <!-- Nombre -->
                <div>
                    <InputLabel
                        for="name"
                        value="Nombre completo"
                        class="text-sm font-medium text-gray-700"
                    />
                    <div class="relative mt-1">
                        <div
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                        >
                            <!-- Icono de usuario -->
                            <svg
                                class="h-5 w-5 text-gray-400"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <TextInput
                            id="name"
                            type="text"
                            class="block w-full rounded-lg border-gray-300 pl-10 shadow-sm transition duration-150 ease-in-out focus:border-orange-500 focus:ring-2 focus:ring-orange-500 focus:ring-opacity-50"
                            placeholder="Grupo 017 SC"
                            v-model="form.name"
                            required
                            autofocus
                            autocomplete="name"
                        />
                    </div>
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <!-- Email -->
                <div>
                    <InputLabel
                        for="email"
                        value="Correo electrónico"
                        class="text-sm font-medium text-gray-700"
                    />
                    <div class="relative mt-1">
                        <div
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                        >
                            <!-- Icono de correo -->
                            <svg
                                class="h-5 w-5 text-gray-400"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"
                                />
                                <path
                                    d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"
                                />
                            </svg>
                        </div>
                        <TextInput
                            id="email"
                            type="email"
                            class="block w-full rounded-lg border-gray-300 pl-10 shadow-sm transition duration-150 ease-in-out focus:border-orange-500 focus:ring-2 focus:ring-orange-500 focus:ring-opacity-50"
                            placeholder="grupo017sc@tecnoweb.org.bo"
                            v-model="form.email"
                            required
                            autocomplete="username"
                        />
                    </div>
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <!-- Contraseña -->
                <div>
                    <InputLabel
                        for="password"
                        value="Contraseña"
                        class="text-sm font-medium text-gray-700"
                    />
                    <div class="relative mt-1">
                        <div
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                        >
                            <!-- Icono de candado -->
                            <svg
                                class="h-5 w-5 text-gray-400"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <TextInput
                            id="password"
                            type="password"
                            class="block w-full rounded-lg border-gray-300 pl-10 shadow-sm transition duration-150 ease-in-out focus:border-orange-500 focus:ring-2 focus:ring-orange-500 focus:ring-opacity-50"
                            placeholder="••••••••"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                        />
                    </div>
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <!-- Confirmar contraseña -->
                <div>
                    <InputLabel
                        for="password_confirmation"
                        value="Confirmar contraseña"
                        class="text-sm font-medium text-gray-700"
                    />
                    <div class="relative mt-1">
                        <div
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                        >
                            <!-- Icono de doble verificación -->
                            <svg
                                class="h-5 w-5 text-gray-400"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="block w-full rounded-lg border-gray-300 pl-10 shadow-sm transition duration-150 ease-in-out focus:border-orange-500 focus:ring-2 focus:ring-orange-500 focus:ring-opacity-50"
                            placeholder="••••••••"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                        />
                    </div>
                    <InputError
                        class="mt-2"
                        :message="form.errors.password_confirmation"
                    />
                </div>

                <!-- Botón de registro -->
                <div class="pt-2">
                    <button
                        type="submit"
                        class="group relative flex w-full items-center justify-center rounded-lg bg-gradient-to-r from-orange-500 to-red-500 px-4 py-3 text-sm font-semibold text-white shadow-lg transition duration-150 ease-in-out hover:from-orange-600 hover:to-red-600 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        <!-- Icono -->
                        <span
                            v-if="!form.processing"
                            class="absolute left-4"
                        >
                            <svg
                                class="h-5 w-5 text-orange-200"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"
                                />
                            </svg>
                        </span>
                        <!-- Spinner -->
                        <span
                            v-else
                            class="absolute left-4"
                        >
                            <svg
                                class="h-5 w-5 animate-spin text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                                ></path>
                            </svg>
                        </span>
                        {{ form.processing ? 'Creando cuenta...' : 'Crear cuenta' }}
                    </button>
                </div>
            </form>

            <!-- Enlace a inicio de sesión -->
            <p class="text-center text-sm text-gray-600">
                ¿Ya tienes una cuenta?
                <Link
                    :href="route('login')"
                    class="font-medium text-orange-600 transition duration-150 ease-in-out hover:text-orange-500"
                >
                    Inicia sesión aquí
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>
