<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import SidebarLayout from '@/Layouts/SidebarLayout.vue';

const props = defineProps({
    reservations: {
        type: Object,
        required: true,
    },
    today: {
        type: String,
        default: '',
    },
    currentTime: {
        type: String,
        default: '',
    },
});

const page = usePage();

const modalOpen = ref(false);
const listLoading = ref(false);
const availableTables = ref([]);
const loadingTables = ref(false);

const cancelModalOpen = ref(false);
const reservationToCancel = ref(null);

const rows = computed(() => props.reservations?.data ?? []);
const meta = computed(() => props.reservations?.meta ?? {});
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

let tablesTimeout = null;

const form = useForm({
    descriptions: '',
    date: '',
    hour: '',
    tables: [],
});

const cancelForm = useForm({});

const resetForm = () => {
    form.reset();
    form.clearErrors();
    availableTables.value = [];
};

const openCreate = () => {
    resetForm();
    modalOpen.value = true;
};

const closeModal = () => {
    if (form.processing) {
        return;
    }

    modalOpen.value = false;
    resetForm();
};

const reloadList = (pageNumber = null) => {
    listLoading.value = true;

    const payload = {};

    if (pageNumber) {
        payload.page = pageNumber;
    }

    router.get(route('client.reservations.index'), payload, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['reservations'],
        onFinish: () => {
            listLoading.value = false;
        },
    });
};

const searchAvailableTables = async () => {
    if (!modalOpen.value) {
        return;
    }

    if (!form.date || !form.hour) {
        availableTables.value = [];
        return;
    }

    loadingTables.value = true;
    form.clearErrors('tables');

    try {
        const url = new URL(route('client.reservations.available-tables'));

        url.searchParams.append('date', form.date);
        url.searchParams.append('hour', form.hour);

        const response = await fetch(url);
        const data = await response.json();

        if (!response.ok) {
            form.setError('tables', data.message ?? 'No se pudieron cargar las mesas disponibles.');
            availableTables.value = [];
            form.tables = [];
            return;
        }

        availableTables.value = data;
        form.tables = form.tables.filter((id) => {
            return data.some((table) => Number(table.id) === Number(id));
        });
    } catch (error) {
        form.setError('tables', 'Error al buscar mesas disponibles.');
        availableTables.value = [];
        form.tables = [];
    } finally {
        loadingTables.value = false;
    }
};

watch(
    () => [form.date, form.hour],
    () => {
        clearTimeout(tablesTimeout);

        tablesTimeout = setTimeout(() => {
            searchAvailableTables();
        }, 350);
    }
);

const submit = () => {
    form.post(route('client.reservations.store'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            reloadList();
        },
    });
};

const askCancel = (reservation) => {
    reservationToCancel.value = reservation;
    cancelModalOpen.value = true;
};

const closeCancelModal = () => {
    if (cancelForm.processing) {
        return;
    }

    reservationToCancel.value = null;
    cancelModalOpen.value = false;
};

const cancelReservation = () => {
    if (!reservationToCancel.value) {
        return;
    }

    cancelForm.patch(route('client.reservations.cancel', reservationToCancel.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeCancelModal();
            reloadList();
        },
    });
};

const goToPage = (pageNumber) => {
    reloadList(pageNumber);
};

const canCancel = (reservation) => {
    return ['Pendiente', 'Confirmada'].includes(reservation.state);
};

const stateClass = (value) => {
    if (value === 'Pendiente') return 'bg-yellow-500/10 text-yellow-600';
    if (value === 'Confirmada') return 'bg-blue-500/10 text-blue-600';
    if (value === 'En Proceso') return 'bg-purple-500/10 text-purple-600';
    if (value === 'Completada') return 'bg-green-500/10 text-green-600';
    if (value === 'Cancelada') return 'bg-red-500/10 text-red-500';

    return 'bg-gray-500/10 text-gray-600';
};
</script>

<template>

    <Head title="Mis reservas" />

    <SidebarLayout title="Mis reservas" subtitle="Reserva mesas y consulta tu historial">
        <div class="space-y-6">
            <div v-if="flashSuccess"
                class="rounded-3xl border border-green-500/20 bg-green-500/10 px-5 py-4 text-sm font-black text-green-600">
                {{ flashSuccess }}
            </div>

            <div v-if="flashError"
                class="rounded-3xl border border-red-500/20 bg-red-500/10 px-5 py-4 text-sm font-black text-red-600">
                {{ flashError }}
            </div>

            <section class="rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-6 shadow-sm">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.18em] text-[var(--app-primary)]">
                            Reservas
                        </p>
                        <h1 class="mt-2 text-3xl font-black text-[var(--app-text)]">
                            Historial de reservas
                        </h1>
                        <p class="mt-1 text-sm font-semibold text-[var(--app-muted)]">
                            Puedes reservar una o varias mesas según disponibilidad.
                        </p>
                    </div>

                    <button type="button"
                        class="rounded-2xl bg-[var(--app-primary)] px-5 py-3 text-sm font-black text-white shadow-sm transition hover:opacity-90"
                        @click="openCreate">
                        Nueva reserva
                    </button>
                </div>
            </section>

            <section
                class="relative overflow-hidden rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] shadow-sm">
                <div v-if="listLoading"
                    class="absolute inset-0 z-30 flex items-center justify-center bg-[var(--app-card)]/70 backdrop-blur-sm">
                    <div
                        class="flex flex-col items-center gap-3 rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] px-7 py-6 shadow-xl">
                        <div
                            class="h-12 w-12 animate-spin rounded-full border-4 border-[var(--app-primary-soft)] border-t-[var(--app-primary)]">
                        </div>
                        <p class="text-sm font-black text-[var(--app-text)]">Actualizando reservas...</p>
                    </div>
                </div>

                <div
                    class="flex flex-col gap-3 border-b border-[var(--app-border)] px-6 py-5 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-xl font-black text-[var(--app-text)]">Mis reservas</h2>
                        <p class="mt-1 text-sm font-bold text-[var(--app-muted)]">
                            Mostrando {{ meta.from ?? 0 }} - {{ meta.to ?? 0 }} de {{ meta.total ?? 0 }}
                        </p>
                    </div>

                    <p class="text-sm font-black text-[var(--app-primary)]">
                        Cantidad del listado: {{ rows.length }}
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left">
                        <thead>
                            <tr
                                class="border-b border-[var(--app-border)] bg-[var(--app-surface-soft)] text-xs font-black uppercase tracking-[0.16em] text-[var(--app-muted)]">
                                <th class="px-6 py-4">Reserva</th>
                                <th class="px-6 py-4">Fecha / hora</th>
                                <th class="px-6 py-4">Mesas</th>
                                <th class="px-6 py-4">Estado</th>
                                <th class="px-6 py-4 text-right">Acciones</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-[var(--app-border)]">
                            <tr v-for="reservation in rows" :key="reservation.id"
                                class="transition hover:bg-[var(--app-surface-soft)]">
                                <td class="px-6 py-5">
                                    <p class="text-xs font-semibold text-[var(--app-muted)]">{{ reservation.descriptions
                                        }}</p>
                                </td>

                                <td class="px-6 py-5">
                                    <p class="font-black text-[var(--app-text)]">{{ reservation.date_formatted }}</p>
                                    <p class="text-xs font-semibold text-[var(--app-muted)]">{{ reservation.hour }}</p>
                                </td>

                                <td class="px-6 py-5">
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="table in reservation.tables" :key="table.id"
                                            class="rounded-xl bg-[var(--app-surface-soft)] px-3 py-1 text-xs font-black text-[var(--app-text)]">
                                            {{ table.name }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-6 py-5">
                                    <span class="rounded-xl px-3 py-1 text-xs font-black"
                                        :class="stateClass(reservation.state)">
                                        {{ reservation.state }}
                                    </span>
                                </td>

                                <td class="px-6 py-5">
                                    <div class="flex justify-end">
                                        <button v-if="canCancel(reservation)" type="button"
                                            class="rounded-xl bg-red-500/10 px-4 py-2 text-sm font-black text-red-500 transition hover:bg-red-500/20"
                                            @click="askCancel(reservation)">
                                            Cancelar
                                        </button>

                                        <span v-else class="text-xs font-black text-[var(--app-muted)]">
                                            Sin acción
                                        </span>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="rows.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <p class="text-lg font-black text-[var(--app-text)]">Todavía no tienes reservas</p>
                                    <p class="mt-1 text-sm font-semibold text-[var(--app-muted)]">
                                        Crea tu primera reserva seleccionando fecha, hora y mesas disponibles.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="meta.last_page > 1"
                    class="flex items-center justify-between border-t border-[var(--app-border)] px-6 py-4">
                    <button type="button"
                        class="rounded-xl border border-[var(--app-border)] px-4 py-2 text-sm font-black text-[var(--app-muted)] disabled:opacity-40"
                        :disabled="meta.current_page <= 1" @click="goToPage(meta.current_page - 1)">
                        Anterior
                    </button>

                    <p class="text-sm font-black text-[var(--app-muted)]">
                        Página {{ meta.current_page }} de {{ meta.last_page }}
                    </p>

                    <button type="button"
                        class="rounded-xl border border-[var(--app-border)] px-4 py-2 text-sm font-black text-[var(--app-muted)] disabled:opacity-40"
                        :disabled="meta.current_page >= meta.last_page" @click="goToPage(meta.current_page + 1)">
                        Siguiente
                    </button>
                </div>
            </section>
        </div>

        <div v-if="modalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-8 backdrop-blur-sm"
            @mousedown.self="closeModal">
            <div
                class="max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-6 shadow-2xl">
                <div class="rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-surface-soft)] p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-black uppercase tracking-[0.18em] text-[var(--app-primary)]">
                                Nueva reserva
                            </p>
                            <h2 class="mt-2 text-2xl font-black text-[var(--app-text)]">
                                Reservar mesa
                            </h2>
                            <p class="mt-1 text-sm font-semibold text-[var(--app-muted)]">
                                La fecha y hora no pueden ser menores a la actual.
                            </p>
                        </div>

                        <button type="button"
                            class="rounded-xl bg-[var(--app-card)] p-2 text-[var(--app-muted)] transition hover:text-[var(--app-primary)]"
                            @click="closeModal">
                            ✕
                        </button>
                    </div>
                </div>

                <form class="mt-6 space-y-5" @submit.prevent="submit">
                    <div>
                        <label class="text-sm font-black text-[var(--app-text)]">Motivo o descripción</label>
                        <input v-model="form.descriptions" type="text"
                            placeholder="Ej: Cena familiar, cumpleaños, reunión"
                            class="mt-2 w-full rounded-2xl border-[var(--app-border)] bg-[var(--app-surface-soft)] text-[var(--app-text)] focus:border-[var(--app-primary)] focus:ring-[var(--app-primary)]" />
                        <p v-if="form.errors.descriptions" class="mt-1 text-sm font-bold text-red-500">{{
                            form.errors.descriptions }}</p>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="text-sm font-black text-[var(--app-text)]">Fecha</label>
                            <input v-model="form.date" type="date" :min="today"
                                class="mt-2 w-full rounded-2xl border-[var(--app-border)] bg-[var(--app-surface-soft)] text-[var(--app-text)] focus:border-[var(--app-primary)] focus:ring-[var(--app-primary)]" />
                            <p v-if="form.errors.date" class="mt-1 text-sm font-bold text-red-500">{{ form.errors.date
                                }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-black text-[var(--app-text)]">Hora</label>
                            <input v-model="form.hour" type="time"
                                class="mt-2 w-full rounded-2xl border-[var(--app-border)] bg-[var(--app-surface-soft)] text-[var(--app-text)] focus:border-[var(--app-primary)] focus:ring-[var(--app-primary)]" />
                            <p v-if="form.errors.hour" class="mt-1 text-sm font-bold text-red-500">{{ form.errors.hour
                                }}</p>
                        </div>
                    </div>

                    <div>
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <label class="text-sm font-black text-[var(--app-text)]">Mesas disponibles</label>
                                <p class="text-xs font-semibold text-[var(--app-muted)]">
                                    Busca mesas según fecha y hora.
                                </p>
                            </div>

                            <button type="button"
                                class="rounded-2xl bg-[var(--app-primary)] px-4 py-3 text-sm font-black text-white disabled:opacity-60"
                                :disabled="loadingTables" @click="searchAvailableTables">
                                {{ loadingTables ? 'Buscando...' : 'Buscar mesas disponibles' }}
                            </button>
                        </div>

                        <div class="mt-4 grid gap-3 md:grid-cols-3">
                            <label v-for="table in availableTables" :key="table.id"
                                class="flex cursor-pointer items-center gap-3 rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface-soft)] p-4 transition hover:bg-[var(--app-primary-soft)]">
                                <input v-model="form.tables" type="checkbox" :value="table.id"
                                    class="rounded border-[var(--app-border)] text-[var(--app-primary)] focus:ring-[var(--app-primary)]" />

                                <span>
                                    <strong class="block text-sm font-black text-[var(--app-text)]">{{ table.name
                                        }}</strong>
                                    <small class="font-semibold text-[var(--app-muted)]">{{ table.amount }}
                                        personas</small>
                                </span>
                            </label>

                            <p v-if="availableTables.length === 0"
                                class="text-sm font-semibold text-[var(--app-muted)] md:col-span-3">
                                Selecciona fecha y hora, luego presiona buscar mesas disponibles.
                            </p>
                        </div>

                        <p v-if="form.errors.tables" class="mt-1 text-sm font-bold text-red-500">{{ form.errors.tables
                            }}</p>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button"
                            class="flex-1 rounded-2xl border border-[var(--app-border)] bg-[var(--app-card)] px-5 py-3 text-sm font-black text-[var(--app-muted)]"
                            @click="closeModal">
                            Cancelar
                        </button>

                        <button type="submit"
                            class="flex-1 rounded-2xl bg-[var(--app-primary)] px-5 py-3 text-sm font-black text-white disabled:opacity-60"
                            :disabled="form.processing">
                            {{ form.processing ? 'Enviando...' : 'Enviar reserva' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="cancelModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-8 backdrop-blur-sm"
            @mousedown.self="closeCancelModal">
            <div
                class="w-full max-w-md rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-6 shadow-2xl">
                <h2 class="text-xl font-black text-[var(--app-text)]">Cancelar reserva</h2>

                <p class="mt-2 text-sm font-semibold text-[var(--app-muted)]">
                    ¿Seguro que deseas cancelar esta reserva?
                </p>

                <p
                    class="mt-3 rounded-2xl bg-[var(--app-surface-soft)] px-4 py-3 text-sm font-black text-[var(--app-text)]">
                    {{ reservationToCancel?.descriptions }}
                </p>

                <div class="mt-6 flex gap-3">
                    <button type="button"
                        class="flex-1 rounded-2xl border border-[var(--app-border)] px-5 py-3 text-sm font-black text-[var(--app-muted)]"
                        :disabled="cancelForm.processing" @click="closeCancelModal">
                        Volver
                    </button>

                    <button type="button"
                        class="flex-1 rounded-2xl bg-red-500 px-5 py-3 text-sm font-black text-white disabled:opacity-60"
                        :disabled="cancelForm.processing" @click="cancelReservation">
                        {{ cancelForm.processing ? 'Cancelando...' : 'Cancelar reserva' }}
                    </button>
                </div>
            </div>
        </div>
    </SidebarLayout>
</template>
