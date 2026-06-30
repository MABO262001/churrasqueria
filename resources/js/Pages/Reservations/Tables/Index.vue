<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import SidebarLayout from '@/Layouts/SidebarLayout.vue';

const props = defineProps({
    tables: {
        type: Object,
        required: true,
    },
    states: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            state: '',
            per_page: '10',
        }),
    },
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            available: 0,
            inactive: 0,
            maintenance: 0,
        }),
    },
});

const page = usePage();

const modalOpen = ref(false);
const modalMode = ref('create');
const editingTable = ref(null);
const tableLoading = ref(false);

const confirmOpen = ref(false);
const tableToDelete = ref(null);

const search = ref(props.filters.search ?? '');
const state = ref(props.filters.state ?? '');
const perPage = ref(props.filters.per_page ?? '10');

let searchTimeout = null;

const rows = computed(() => props.tables?.data ?? []);
const meta = computed(() => props.tables?.meta ?? {});
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const form = useForm({
    name: '',
    amount: '',
    state: '',
});

const buildFilters = (pageNumber = null) => {
    const payload = {
        search: search.value,
        state: state.value,
        per_page: perPage.value,
    };

    if (pageNumber) {
        payload.page = pageNumber;
    }

    return payload;
};

const reloadList = (pageNumber = null) => {
    tableLoading.value = true;

    router.get(route('reservation.tables.index'), buildFilters(pageNumber), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['tables', 'filters', 'stats'],
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
watch(perPage, () => reloadList());

const clearFilters = () => {
    search.value = '';
    state.value = '';
    perPage.value = '10';
    reloadList();
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
    editingTable.value = null;
};

const openCreate = () => {
    modalMode.value = 'create';
    resetForm();
    modalOpen.value = true;
};

const openEdit = (table) => {
    modalMode.value = 'edit';
    editingTable.value = table;

    form.name = table.name ?? '';
    form.amount = table.amount ?? '';
    form.state = table.state ?? '';

    form.clearErrors();
    modalOpen.value = true;
};

const closeModal = () => {
    if (form.processing) {
        return;
    }

    modalOpen.value = false;
    resetForm();
};

const blockNegative = (event) => {
    if (['-', '+', 'e', 'E'].includes(event.key)) {
        event.preventDefault();
    }
};

const sanitizeAmount = () => {
    if (form.amount === '' || form.amount === null) {
        return;
    }

    if (Number(form.amount) < 1 || Number.isNaN(Number(form.amount))) {
        form.amount = 1;
    }
};

const submit = () => {
    sanitizeAmount();

    if (modalMode.value === 'create') {
        form.post(route('reservation.tables.store'), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                reloadList();
            },
        });

        return;
    }

    form.put(route('reservation.tables.update', editingTable.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            reloadList();
        },
    });
};

const askDelete = (table) => {
    tableToDelete.value = table;
    confirmOpen.value = true;
};

const closeConfirm = () => {
    if (tableLoading.value) {
        return;
    }

    tableToDelete.value = null;
    confirmOpen.value = false;
};

const deleteTable = () => {
    if (!tableToDelete.value) {
        return;
    }

    tableLoading.value = true;

    router.delete(route('reservation.tables.destroy', tableToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeConfirm();
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
    const finalValue = value ?? 'Disponible';

    if (finalValue === 'Disponible') {
        return 'bg-green-500/10 text-green-600';
    }

    if (finalValue === 'Inactiva') {
        return 'bg-red-500/10 text-red-500';
    }

    return 'bg-yellow-500/10 text-yellow-600';
};

onMounted(() => {
    const params = new URLSearchParams(window.location.search);

    if (params.get('action') === 'create') {
        openCreate();
    }
});
</script>

<template>

    <Head title="Mesas" />

    <SidebarLayout title="Mesas" subtitle="Gestiona mesas, capacidad y estado físico">
        <div class="space-y-6">
            <div v-if="flashSuccess"
                class="rounded-3xl border border-green-500/20 bg-green-500/10 px-5 py-4 text-sm font-black text-green-600">
                {{ flashSuccess }}
            </div>

            <div v-if="flashError"
                class="rounded-3xl border border-red-500/20 bg-red-500/10 px-5 py-4 text-sm font-black text-red-600">
                {{ flashError }}
            </div>

            <section class="grid gap-4 md:grid-cols-4">
                <article class="rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-6 shadow-sm">
                    <p class="text-sm font-bold text-[var(--app-muted)]">Mesas</p>
                    <p class="mt-2 text-4xl font-black text-[var(--app-text)]">{{ stats.total }}</p>
                </article>

                <article class="rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-6 shadow-sm">
                    <p class="text-sm font-bold text-[var(--app-muted)]">Disponibles</p>
                    <p class="mt-2 text-4xl font-black text-green-600">{{ stats.available }}</p>
                </article>

                <article class="rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-6 shadow-sm">
                    <p class="text-sm font-bold text-[var(--app-muted)]">Inactivas</p>
                    <p class="mt-2 text-4xl font-black text-red-500">{{ stats.inactive }}</p>
                </article>

                <article class="rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-6 shadow-sm">
                    <p class="text-sm font-bold text-[var(--app-muted)]">Mantenimiento</p>
                    <p class="mt-2 text-4xl font-black text-yellow-600">{{ stats.maintenance }}</p>
                </article>
            </section>

            <section class="rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-6 shadow-sm">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.18em] text-[var(--app-primary)]">
                            Gestión de reservas
                        </p>
                        <h1 class="mt-2 text-3xl font-black text-[var(--app-text)]">
                            Mesas del restaurante
                        </h1>
                        <p class="mt-1 text-sm font-semibold text-[var(--app-muted)]">
                            El estado físico no reemplaza la disponibilidad por reserva; eso se calcula por fecha y
                            hora.
                        </p>
                    </div>

                    <button type="button"
                        class="rounded-2xl bg-[var(--app-primary)] px-5 py-3 text-sm font-black text-white shadow-sm transition hover:opacity-90"
                        @click="openCreate">
                        Nueva mesa
                    </button>
                </div>
            </section>

            <section class="rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-6 shadow-sm">
                <div class="grid gap-4 xl:grid-cols-[1fr_220px_160px_160px] xl:items-end">
                    <div>
                        <label class="text-sm font-black text-[var(--app-text)]">Buscar en tiempo real</label>
                        <input v-model="search" type="text" placeholder="Nombre de mesa"
                            class="mt-2 w-full rounded-2xl border-[var(--app-border)] bg-[var(--app-surface-soft)] text-[var(--app-text)] focus:border-[var(--app-primary)] focus:ring-[var(--app-primary)]" />
                    </div>

                    <div>
                        <label class="text-sm font-black text-[var(--app-text)]">Estado</label>
                        <select v-model="state"
                            class="mt-2 w-full rounded-2xl border-[var(--app-border)] bg-[var(--app-surface-soft)] text-[var(--app-text)] focus:border-[var(--app-primary)] focus:ring-[var(--app-primary)]">
                            <option value="">Todos</option>
                            <option value="Sin estado">Sin estado</option>
                            <option v-for="item in states" :key="item" :value="item">
                                {{ item }}
                            </option>
                        </select>
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
                        <p class="text-sm font-black text-[var(--app-text)]">Actualizando mesas...</p>
                    </div>
                </div>

                <div
                    class="flex flex-col gap-3 border-b border-[var(--app-border)] px-6 py-5 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-xl font-black text-[var(--app-text)]">Lista de mesas</h2>
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
                                <th class="px-6 py-4">Mesa</th>
                                <th class="px-6 py-4">Capacidad</th>
                                <th class="px-6 py-4">Estado físico</th>
                                <th class="px-6 py-4">Reservas</th>
                                <th class="px-6 py-4">Ventas</th>
                                <th class="px-6 py-4 text-right">Acciones</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-[var(--app-border)]">
                            <tr v-for="table in rows" :key="table.id"
                                class="transition hover:bg-[var(--app-surface-soft)]">
                                <td class="px-6 py-5">
                                    <p class="font-black text-[var(--app-text)]">{{ table.name }}</p>
                                </td>

                                <td class="px-6 py-5 text-sm font-black text-[var(--app-text)]">
                                    {{ table.amount }} personas
                                </td>

                                <td class="px-6 py-5">
                                    <span class="rounded-xl px-3 py-1 text-xs font-black"
                                        :class="stateClass(table.computed_state)">
                                        {{ table.computed_state }}
                                    </span>
                                </td>

                                <td class="px-6 py-5 text-sm font-black text-[var(--app-text)]">
                                    {{ table.details_reservations_count }}
                                </td>

                                <td class="px-6 py-5 text-sm font-black text-[var(--app-text)]">
                                    {{ table.sales_notes_count }}
                                </td>

                                <td class="px-6 py-5">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" title="Editar mesa"
                                            class="rounded-xl bg-[var(--app-surface-soft)] p-2 text-[var(--app-text)] transition hover:bg-[var(--app-primary-soft)] hover:text-[var(--app-primary-text)]"
                                            @click="openEdit(table)">
                                            Editar
                                        </button>

                                        <button type="button" title="Eliminar mesa"
                                            class="rounded-xl bg-red-500/10 p-2 text-red-500 transition hover:bg-red-500/20"
                                            @click="askDelete(table)">
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="rows.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <p class="text-lg font-black text-[var(--app-text)]">No hay mesas registradas</p>
                                    <p class="mt-1 text-sm font-semibold text-[var(--app-muted)]">
                                        Crea una nueva mesa para empezar a gestionar reservas.
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
                class="w-full max-w-xl rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-6 shadow-2xl">
                <div class="rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-surface-soft)] p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-black uppercase tracking-[0.18em] text-[var(--app-primary)]">
                                {{ modalMode === 'create' ? 'Nueva mesa' : 'Editar mesa' }}
                            </p>
                            <h2 class="mt-2 text-2xl font-black text-[var(--app-text)]">
                                {{ modalMode === 'create' ? 'Registrar mesa' : 'Actualizar mesa' }}
                            </h2>
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
                        <label class="text-sm font-black text-[var(--app-text)]">Nombre</label>
                        <input v-model="form.name" type="text"
                            class="mt-2 w-full rounded-2xl border-[var(--app-border)] bg-[var(--app-surface-soft)] text-[var(--app-text)] focus:border-[var(--app-primary)] focus:ring-[var(--app-primary)]" />
                        <p v-if="form.errors.name" class="mt-1 text-sm font-bold text-red-500">{{ form.errors.name }}
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-black text-[var(--app-text)]">Capacidad</label>
                        <input v-model="form.amount" type="number" min="1" step="1"
                            class="mt-2 w-full rounded-2xl border-[var(--app-border)] bg-[var(--app-surface-soft)] text-[var(--app-text)] focus:border-[var(--app-primary)] focus:ring-[var(--app-primary)]"
                            @keydown="blockNegative" @input="sanitizeAmount" @blur="sanitizeAmount" />
                        <p v-if="form.errors.amount" class="mt-1 text-sm font-bold text-red-500">{{ form.errors.amount
                            }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-black text-[var(--app-text)]">Estado físico</label>
                        <select v-model="form.state"
                            class="mt-2 w-full rounded-2xl border-[var(--app-border)] bg-[var(--app-surface-soft)] text-[var(--app-text)] focus:border-[var(--app-primary)] focus:ring-[var(--app-primary)]">
                            <option value="">Sin estado / Disponible por defecto</option>
                            <option v-for="item in states" :key="item" :value="item">
                                {{ item }}
                            </option>
                        </select>
                        <p v-if="form.errors.state" class="mt-1 text-sm font-bold text-red-500">{{ form.errors.state }}
                        </p>
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

        <div v-if="confirmOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-8 backdrop-blur-sm"
            @mousedown.self="closeConfirm">
            <div
                class="w-full max-w-md rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-6 shadow-2xl">
                <h2 class="text-xl font-black text-[var(--app-text)]">Eliminar mesa</h2>
                <p class="mt-2 text-sm font-semibold text-[var(--app-muted)]">
                    ¿Seguro que deseas eliminar la mesa
                    <strong>{{ tableToDelete?.name }}</strong>?
                </p>

                <div class="mt-6 flex gap-3">
                    <button type="button"
                        class="flex-1 rounded-2xl border border-[var(--app-border)] px-5 py-3 text-sm font-black text-[var(--app-muted)]"
                        @click="closeConfirm">
                        Cancelar
                    </button>

                    <button type="button"
                        class="flex-1 rounded-2xl bg-red-500 px-5 py-3 text-sm font-black text-white disabled:opacity-60"
                        :disabled="tableLoading" @click="deleteTable">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </SidebarLayout>
</template>
