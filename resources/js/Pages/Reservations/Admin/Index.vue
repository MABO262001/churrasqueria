<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import SidebarLayout from '@/Layouts/SidebarLayout.vue';

const props = defineProps({
    reservations: {
        type: Object,
        required: true,
    },
    states: {
        type: Array,
        default: () => [],
    },
    availableNowTables: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            state: '',
            date: '',
            per_page: '10',
        }),
    },
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            pending: 0,
            confirmed: 0,
            in_process: 0,
            available_now: 0,
        }),
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
const modalMode = ref('create');
const editingReservation = ref(null);
const tableLoading = ref(false);
const availableTables = ref([]);
const loadingTables = ref(false);

const clientSearch = ref('');
const clientResults = ref([]);
const selectedClient = ref(null);
const searchingClients = ref(false);

const search = ref(props.filters.search ?? '');
const state = ref(props.filters.state ?? '');
const date = ref(props.filters.date ?? '');
const perPage = ref(props.filters.per_page ?? '10');

let searchTimeout = null;
let clientTimeout = null;
let tablesTimeout = null;

const rows = computed(() => props.reservations?.data ?? []);
const meta = computed(() => props.reservations?.meta ?? {});
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const form = useForm({
    users_cliente_id: '',
    descriptions: '',
    date: '',
    hour: '',
    state: 'Pendiente',
    tables: [],
});

const buildFilters = (pageNumber = null) => {
    const payload = {
        search: search.value,
        state: state.value,
        date: date.value,
        per_page: perPage.value,
    };

    if (pageNumber) {
        payload.page = pageNumber;
    }

    return payload;
};

const reloadList = (pageNumber = null) => {
    tableLoading.value = true;

    router.get(route('admin.reservations.index'), buildFilters(pageNumber), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['reservations', 'filters', 'stats', 'availableNowTables'],
        onFinish: () => {
            tableLoading.value = false;
        },
    });
};

watch(search, () => {
    clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {
        reloadList();
    }, 400);
});

watch(state, () => reloadList());
watch(date, () => reloadList());
watch(perPage, () => reloadList());

const clearFilters = () => {
    search.value = '';
    state.value = '';
    date.value = '';
    perPage.value = '10';
    reloadList();
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.state = 'Pendiente';
    editingReservation.value = null;
    availableTables.value = [];
    clientSearch.value = '';
    clientResults.value = [];
    selectedClient.value = null;
};

const openCreate = () => {
    modalMode.value = 'create';
    resetForm();
    modalOpen.value = true;
};

const openEdit = (reservation) => {
    modalMode.value = 'edit';
    editingReservation.value = reservation;

    form.users_cliente_id = reservation.users_cliente_id ?? '';
    form.descriptions = reservation.descriptions ?? '';
    form.date = reservation.date ?? '';
    form.hour = reservation.hour ?? '';
    form.state = reservation.state ?? 'Pendiente';
    form.tables = reservation.tables?.map((table) => table.id) ?? [];

    selectedClient.value = reservation.client ?? null;
    clientSearch.value = reservation.client?.label ?? reservation.client?.name ?? '';
    clientResults.value = [];

    availableTables.value = reservation.tables ?? [];
    form.clearErrors();
    modalOpen.value = true;

    setTimeout(() => {
        searchAvailableTables();
    }, 250);
};

const closeModal = () => {
    if (form.processing) {
        return;
    }

    modalOpen.value = false;
    resetForm();
};

const searchClients = async () => {
    const value = clientSearch.value.trim();

    if (value.length < 2) {
        clientResults.value = [];
        return;
    }

    searchingClients.value = true;

    try {
        const url = new URL(route('admin.reservations.search-clients'));
        url.searchParams.append('search', value);

        const response = await fetch(url);
        const data = await response.json();

        if (!response.ok) {
            clientResults.value = [];
            return;
        }

        clientResults.value = data;
    } catch (error) {
        clientResults.value = [];
    } finally {
        searchingClients.value = false;
    }
};

watch(clientSearch, () => {
    clearTimeout(clientTimeout);

    if (selectedClient.value && clientSearch.value === selectedClient.value.label) {
        return;
    }

    selectedClient.value = null;
    form.users_cliente_id = '';

    clientTimeout = setTimeout(() => {
        searchClients();
    }, 350);
});

const selectClient = (client) => {
    selectedClient.value = client;
    form.users_cliente_id = client.id;
    clientSearch.value = client.label || client.name;
    clientResults.value = [];
    form.clearErrors('users_cliente_id');
};

const clearSelectedClient = () => {
    selectedClient.value = null;
    form.users_cliente_id = '';
    clientSearch.value = '';
    clientResults.value = [];
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
        const url = new URL(route('admin.reservations.available-tables'));

        url.searchParams.append('date', form.date);
        url.searchParams.append('hour', form.hour);

        if (modalMode.value === 'edit' && editingReservation.value?.id) {
            url.searchParams.append('ignore_reservation_id', editingReservation.value.id);
        }

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
    if (!form.users_cliente_id) {
        form.setError('users_cliente_id', 'Debe buscar y seleccionar un cliente.');
        return;
    }

    if (modalMode.value === 'create') {
        form.post(route('admin.reservations.store'), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                reloadList();
            },
        });

        return;
    }

    form.put(route('admin.reservations.update', editingReservation.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            reloadList();
        },
    });
};

const changeState = (reservation, newState) => {
    tableLoading.value = true;

    router.patch(route('admin.reservations.change-state', reservation.id), {
        state: newState,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            reloadList();
        },
        onFinish: () => {
            tableLoading.value = false;
        },
    });
};

const goToPage = (pageNumber) => {
    reloadList(pageNumber);
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

    <Head title="Reservas" />

    <SidebarLayout title="Reservas" subtitle="Gestión interna de reservas y disponibilidad de mesas">
        <div class="space-y-6">
            <div v-if="flashSuccess"
                class="rounded-3xl border border-green-500/20 bg-green-500/10 px-5 py-4 text-sm font-black text-green-600">
                {{ flashSuccess }}
            </div>

            <div v-if="flashError"
                class="rounded-3xl border border-red-500/20 bg-red-500/10 px-5 py-4 text-sm font-black text-red-600">
                {{ flashError }}
            </div>

            <section class="grid gap-4 md:grid-cols-5">
                <article class="rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-6 shadow-sm">
                    <p class="text-sm font-bold text-[var(--app-muted)]">Reservas</p>
                    <p class="mt-2 text-4xl font-black text-[var(--app-text)]">{{ stats.total }}</p>
                </article>

                <article class="rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-6 shadow-sm">
                    <p class="text-sm font-bold text-[var(--app-muted)]">Pendientes</p>
                    <p class="mt-2 text-4xl font-black text-yellow-600">{{ stats.pending }}</p>
                </article>

                <article class="rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-6 shadow-sm">
                    <p class="text-sm font-bold text-[var(--app-muted)]">Confirmadas</p>
                    <p class="mt-2 text-4xl font-black text-blue-600">{{ stats.confirmed }}</p>
                </article>

                <article class="rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-6 shadow-sm">
                    <p class="text-sm font-bold text-[var(--app-muted)]">En proceso</p>
                    <p class="mt-2 text-4xl font-black text-purple-600">{{ stats.in_process }}</p>
                </article>

                <article class="rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-6 shadow-sm">
                    <p class="text-sm font-bold text-[var(--app-muted)]">Mesas libres ahora</p>
                    <p class="mt-2 text-4xl font-black text-green-600">{{ stats.available_now }}</p>
                </article>
            </section>

            <section class="rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-6 shadow-sm">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.18em] text-[var(--app-primary)]">
                            Administración
                        </p>
                        <h1 class="mt-2 text-3xl font-black text-[var(--app-text)]">
                            Reservas del restaurante
                        </h1>
                        <p class="mt-1 text-sm font-semibold text-[var(--app-muted)]">
                            Las mesas se bloquean por fecha y hora con 15 minutos de tolerancia.
                        </p>
                    </div>

                    <button type="button"
                        class="rounded-2xl bg-[var(--app-primary)] px-5 py-3 text-sm font-black text-white shadow-sm transition hover:opacity-90"
                        @click="openCreate">
                        Nueva reserva
                    </button>
                </div>
            </section>

            <section class="rounded-[2rem] border border-green-500/20 bg-green-500/10 p-6 shadow-sm">
                <h2 class="text-xl font-black text-green-700">Mesas disponibles para el mesero ahora</h2>

                <div v-if="availableNowTables.length" class="mt-4 flex flex-wrap gap-2">
                    <span v-for="table in availableNowTables" :key="table.id"
                        class="rounded-2xl bg-green-500/10 px-4 py-2 text-sm font-black text-green-700">
                        {{ table.name }} · {{ table.amount }} personas
                    </span>
                </div>

                <p v-else class="mt-3 text-sm font-bold text-green-700">
                    No hay mesas disponibles en este momento.
                </p>
            </section>

            <section class="rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-6 shadow-sm">
                <div class="grid gap-4 xl:grid-cols-[1fr_220px_180px_160px_160px] xl:items-end">
                    <div>
                        <label class="text-sm font-black text-[var(--app-text)]">Buscar</label>
                        <input v-model="search" type="text" placeholder="Cliente o descripción"
                            class="mt-2 w-full rounded-2xl border-[var(--app-border)] bg-[var(--app-surface-soft)] text-[var(--app-text)] focus:border-[var(--app-primary)] focus:ring-[var(--app-primary)]" />
                    </div>

                    <div>
                        <label class="text-sm font-black text-[var(--app-text)]">Estado</label>
                        <select v-model="state"
                            class="mt-2 w-full rounded-2xl border-[var(--app-border)] bg-[var(--app-surface-soft)] text-[var(--app-text)] focus:border-[var(--app-primary)] focus:ring-[var(--app-primary)]">
                            <option value="">Todos</option>
                            <option v-for="item in states" :key="item" :value="item">
                                {{ item }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-black text-[var(--app-text)]">Fecha</label>
                        <input v-model="date" type="date"
                            class="mt-2 w-full rounded-2xl border-[var(--app-border)] bg-[var(--app-surface-soft)] text-[var(--app-text)] focus:border-[var(--app-primary)] focus:ring-[var(--app-primary)]" />
                    </div>

                    <div>
                        <label class="text-sm font-black text-[var(--app-text)]">Mostrar</label>
                        <select v-model="perPage"
                            class="mt-2 w-full rounded-2xl border-[var(--app-border)] bg-[var(--app-surface-soft)] text-[var(--app-text)] focus:border-[var(--app-primary)] focus:ring-[var(--app-primary)]">
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="all">Todos</option>
                        </select>
                    </div>

                    <button type="button"
                        class="rounded-2xl border border-[var(--app-border)] bg-[var(--app-card)] px-5 py-3 text-sm font-black text-[var(--app-muted)] transition hover:bg-[var(--app-surface-soft)]"
                        @click="clearFilters">
                        Limpiar
                    </button>
                </div>
            </section>

            <section
                class="relative overflow-hidden rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] shadow-sm">
                <div v-if="tableLoading"
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
                        <h2 class="text-xl font-black text-[var(--app-text)]">Lista de reservas</h2>
                        <p class="mt-1 text-sm font-bold text-[var(--app-muted)]">
                            Mostrando {{ meta.from ?? 0 }} - {{ meta.to ?? 0 }} de {{ meta.total ?? 0 }}
                        </p>
                    </div>

                    <p class="text-sm font-black text-[var(--app-primary)]">
                        Cantidad del listado: {{ rows.length }}
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1100px] text-left">
                        <thead>
                            <tr
                                class="border-b border-[var(--app-border)] bg-[var(--app-surface-soft)] text-xs font-black uppercase tracking-[0.16em] text-[var(--app-muted)]">
                                <th class="px-6 py-4">Cliente</th>
                                <th class="px-6 py-4">Reserva</th>
                                <th class="px-6 py-4">Fecha / hora</th>
                                <th class="px-6 py-4">Mesas</th>
                                <th class="px-6 py-4">Estado</th>
                                <th class="px-6 py-4">Registrado por</th>
                                <th class="px-6 py-4 text-right">Acciones</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-[var(--app-border)]">
                            <tr v-for="reservation in rows" :key="reservation.id"
                                class="transition hover:bg-[var(--app-surface-soft)]">
                                <td class="px-6 py-5">
                                    <p class="font-black text-[var(--app-text)]">
                                        {{ reservation.client?.label ?? reservation.client?.name ?? 'Sin cliente' }}
                                    </p>

                                    <p class="text-xs font-semibold text-[var(--app-muted)]">
                                        {{ reservation.client?.ci ? `CI: ${reservation.client.ci}` : 'Sin CI registrado'
                                        }}
                                    </p>

                                    <p class="text-xs font-semibold text-[var(--app-muted)]">
                                        {{ reservation.client?.email ?? 'Sin correo' }}
                                    </p>
                                </td>
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
                                    <select :value="reservation.state"
                                        class="rounded-2xl border-[var(--app-border)] bg-[var(--app-surface-soft)] text-sm font-black text-[var(--app-text)] focus:border-[var(--app-primary)] focus:ring-[var(--app-primary)]"
                                        @change="changeState(reservation, $event.target.value)">
                                        <option v-for="item in states" :key="item" :value="item">
                                            {{ item }}
                                        </option>
                                    </select>
                                </td>

                                <td class="px-6 py-5">
                                    <p class="text-sm font-black text-[var(--app-text)]">{{ reservation.admin?.name ??
                                        'Sin usuario' }}</p>
                                </td>

                                <td class="px-6 py-5">
                                    <div class="flex justify-end">
                                        <button type="button"
                                            class="rounded-xl bg-[var(--app-surface-soft)] px-4 py-2 text-sm font-black text-[var(--app-text)] transition hover:bg-[var(--app-primary-soft)]"
                                            @click="openEdit(reservation)">
                                            Editar
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="rows.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <p class="text-lg font-black text-[var(--app-text)]">No hay reservas registradas</p>
                                    <p class="mt-1 text-sm font-semibold text-[var(--app-muted)]">
                                        Crea una reserva o cambia los filtros.
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
                                {{ modalMode === 'create' ? 'Nueva reserva' : 'Editar reserva' }}
                            </p>
                            <h2 class="mt-2 text-2xl font-black text-[var(--app-text)]">
                                {{ modalMode === 'create' ? 'Registrar reserva' : 'Actualizar reserva' }}
                            </h2>
                            <p class="mt-1 text-sm font-semibold text-[var(--app-muted)]">
                                Se bloquean mesas en el mismo horario con 15 minutos de tolerancia.
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
                        <label class="text-sm font-black text-[var(--app-text)]">
                            Buscar cliente
                        </label>

                        <div class="relative mt-2">
                            <input v-model="clientSearch" type="text"
                                placeholder="Buscar por CI, nombre, apellido o correo"
                                class="w-full rounded-2xl border-[var(--app-border)] bg-[var(--app-surface-soft)] text-[var(--app-text)] focus:border-[var(--app-primary)] focus:ring-[var(--app-primary)]" />

                            <div v-if="clientResults.length > 0"
                                class="absolute z-50 mt-2 max-h-72 w-full overflow-y-auto rounded-2xl border border-[var(--app-border)] bg-[var(--app-card)] shadow-xl">
                                <button v-for="client in clientResults" :key="client.id" type="button"
                                    class="block w-full border-b border-[var(--app-border)] px-4 py-3 text-left transition hover:bg-[var(--app-surface-soft)]"
                                    @click="selectClient(client)">
                                    <p class="text-sm font-black text-[var(--app-text)]">
                                        {{ client.label }}
                                    </p>

                                    <p class="text-xs font-semibold text-[var(--app-muted)]">
                                        CI: {{ client.ci ?? 'Sin CI' }} · {{ client.email }}
                                    </p>

                                    <p v-if="client.telephone" class="text-xs font-semibold text-[var(--app-muted)]">
                                        Teléfono: {{ client.telephone }}
                                    </p>
                                </button>
                            </div>
                        </div>

                        <p v-if="searchingClients" class="mt-1 text-xs font-bold text-[var(--app-primary)]">
                            Buscando clientes...
                        </p>

                        <div v-if="selectedClient"
                            class="mt-3 flex items-center justify-between gap-3 rounded-2xl border border-green-500/20 bg-green-500/10 px-4 py-3">
                            <div>
                                <p class="text-sm font-black text-green-700">
                                    Cliente seleccionado: {{ selectedClient.label }}
                                </p>

                                <p class="text-xs font-semibold text-green-700">
                                    CI: {{ selectedClient.ci ?? 'Sin CI' }} · {{ selectedClient.email }}
                                </p>
                            </div>

                            <button type="button"
                                class="rounded-xl bg-white/70 px-3 py-2 text-xs font-black text-red-500"
                                @click="clearSelectedClient">
                                Quitar
                            </button>
                        </div>

                        <p v-if="form.errors.users_cliente_id" class="mt-1 text-sm font-bold text-red-500">
                            {{ form.errors.users_cliente_id }}
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-black text-[var(--app-text)]">Descripción</label>
                        <input v-model="form.descriptions" type="text"
                            class="mt-2 w-full rounded-2xl border-[var(--app-border)] bg-[var(--app-surface-soft)] text-[var(--app-text)] focus:border-[var(--app-primary)] focus:ring-[var(--app-primary)]" />
                        <p v-if="form.errors.descriptions" class="mt-1 text-sm font-bold text-red-500">{{
                            form.errors.descriptions }}</p>
                    </div>

                    <div class="grid gap-5 md:grid-cols-3">
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

                        <div>
                            <label class="text-sm font-black text-[var(--app-text)]">Estado</label>
                            <select v-model="form.state"
                                class="mt-2 w-full rounded-2xl border-[var(--app-border)] bg-[var(--app-surface-soft)] text-[var(--app-text)] focus:border-[var(--app-primary)] focus:ring-[var(--app-primary)]">
                                <option v-for="item in states" :key="item" :value="item">
                                    {{ item }}
                                </option>
                            </select>
                            <p v-if="form.errors.state" class="mt-1 text-sm font-bold text-red-500">{{ form.errors.state
                            }}</p>
                        </div>
                    </div>

                    <div>
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <label class="text-sm font-black text-[var(--app-text)]">Mesas disponibles</label>
                                <p class="text-xs font-semibold text-[var(--app-muted)]">
                                    Primero selecciona fecha y hora, luego busca mesas.
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
                                No hay mesas cargadas. Presiona buscar mesas disponibles.
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
                            {{ form.processing ? 'Guardando...' : modalMode === 'create' ? 'Guardar' : 'Actualizar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </SidebarLayout>
</template>
