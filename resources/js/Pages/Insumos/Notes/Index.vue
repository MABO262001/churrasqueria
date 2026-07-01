<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import SidebarLayout from '@/Layouts/SidebarLayout.vue';

const props = defineProps({
    notes: {
        type: Object,
        required: true,
    },
    insumos: {
        type: Array,
        default: () => [],
    },
    admins: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            admin_id: '',
            date_from: '',
            date_to: '',
            period: 'all',
            sort: 'recent',
            per_page: '10',
        }),
    },
    stats: {
        type: Object,
        default: () => ({
            notes: 0,
            total_used: 0,
            unique_insumos: 0,
            max_usage: 0,
        }),
    },
    allowedPerPage: {
        type: Array,
        default: () => ['10', '20', '30', '50', '100', 'all'],
    },
});

const page = usePage();

const search = ref(props.filters.search ?? '');
const adminId = ref(props.filters.admin_id ?? '');
const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');
const period = ref(props.filters.period ?? 'all');
const sort = ref(props.filters.sort ?? 'recent');
const perPage = ref(props.filters.per_page ?? '10');

const tableLoading = ref(false);
const noteToDelete = ref(null);
const expandedNoteId = ref(null);

let filterTimeout = null;

const form = useForm({
    details: [
        {
            insumos_id: '',
            amount: 1,
            search: '',
            open: false,
        },
    ],
});

const rows = computed(() => props.notes?.data ?? []);
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const currentPage = computed(() => props.notes?.current_page ?? 1);
const lastPage = computed(() => props.notes?.last_page ?? 1);
const fromRow = computed(() => props.notes?.from ?? 0);
const toRow = computed(() => props.notes?.to ?? 0);
const totalRows = computed(() => props.notes?.total ?? 0);

const selectedInsumosIds = computed(() => {
    return form.details
        .map((detail) => Number(detail.insumos_id))
        .filter(Boolean);
});

const getInsumo = (id) => {
    return props.insumos.find((insumo) => Number(insumo.id) === Number(id));
};

const filteredInsumos = (detail) => {
    const text = (detail.search ?? '').toLowerCase().trim();

    if (!text) {
        return props.insumos;
    }

    return props.insumos.filter((insumo) => {
        return insumo.name.toLowerCase().includes(text);
    });
};

const totalAmountForm = computed(() => {
    return form.details.reduce((total, detail) => {
        return total + Number(detail.amount || 0);
    }, 0);
});

const buildFilters = (pageNumber = null) => {
    const payload = {
        search: search.value,
        admin_id: adminId.value,
        date_from: dateFrom.value,
        date_to: dateTo.value,
        period: period.value,
        sort: sort.value,
        per_page: perPage.value,
    };

    if (pageNumber) {
        payload.page = pageNumber;
    }

    return payload;
};

const reloadList = (pageNumber = null, preserveState = true) => {
    tableLoading.value = true;

    router.get(route('insumos.notes.index'), buildFilters(pageNumber), {
        preserveState,
        preserveScroll: true,
        replace: true,
        only: ['notes', 'insumos', 'admins', 'filters', 'stats', 'allowedPerPage', 'flash'],
        onFinish: () => {
            tableLoading.value = false;
        },
    });
};

watch([search, adminId, dateFrom, dateTo, period, sort, perPage], () => {
    clearTimeout(filterTimeout);

    filterTimeout = setTimeout(() => {
        reloadList(1, true);
    }, 350);
});

const clearFilters = () => {
    search.value = '';
    adminId.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    period.value = 'all';
    sort.value = 'recent';
    perPage.value = '10';

    reloadList(1, true);
};

const todayUses = () => {
    search.value = '';
    adminId.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    period.value = 'today';
    sort.value = 'recent';

    reloadList(1, true);
};

const highestUsesMonth = () => {
    search.value = '';
    adminId.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    period.value = 'this_month';
    sort.value = 'highest_amount';

    reloadList(1, true);
};

const selectInsumo = (detail, insumo) => {
    detail.insumos_id = insumo.id;
    detail.search = insumo.name;
    detail.open = false;
};

const addDetail = () => {
    form.details.push({
        insumos_id: '',
        amount: 1,
        search: '',
        open: false,
    });
};

const removeDetail = (index) => {
    if (form.details.length === 1) {
        return;
    }

    form.details.splice(index, 1);
};

const resetForm = () => {
    form.reset();
    form.clearErrors();

    form.details = [
        {
            insumos_id: '',
            amount: 1,
            search: '',
            open: false,
        },
    ];
};

const blockNegative = (event) => {
    if (['-', '+', 'e', 'E'].includes(event.key)) {
        event.preventDefault();
    }
};

const sanitizeAmount = (detail) => {
    if (detail.amount === '' || detail.amount === null) {
        return;
    }

    if (Number(detail.amount) < 1 || Number.isNaN(Number(detail.amount))) {
        detail.amount = 1;
    }
};

const submit = () => {
    form.details.forEach((detail) => {
        sanitizeAmount(detail);
    });

    form
        .transform((data) => ({
            details: data.details.map((detail) => ({
                insumos_id: detail.insumos_id,
                amount: detail.amount,
            })),
        }))
        .post(route('insumos.notes.store'), {
            preserveScroll: true,
            onSuccess: () => {
                resetForm();
                reloadList(1, false);
            },
        });
};

const confirmDelete = (note) => {
    noteToDelete.value = note;
};

const closeDeleteModal = () => {
    noteToDelete.value = null;
};

const destroyNote = () => {
    if (!noteToDelete.value) {
        return;
    }

    tableLoading.value = true;

    router.delete(route('insumos.notes.destroy', noteToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            noteToDelete.value = null;
            reloadList(1, false);
        },
        onFinish: () => {
            tableLoading.value = false;
        },
    });
};

const exportNotes = (type) => {
    const params = new URLSearchParams(buildFilters()).toString();

    const routeName = {
        excel: 'insumos.notes.export.excel',
        pdf: 'insumos.notes.export.pdf',
        txt: 'insumos.notes.export.txt',
    }[type];

    window.open(`${route(routeName)}?${params}`, '_blank');
};

const goToPage = (pageNumber) => {
    reloadList(pageNumber, false);
};

const toggleDetails = (note) => {
    expandedNoteId.value = expandedNoteId.value === note.id ? null : note.id;
};

const amountTotal = (note) => {
    return (note.details_insumos ?? []).reduce((total, detail) => {
        return total + Number(detail.amount ?? 0);
    }, 0);
};

const detailsCount = (note) => {
    return note.details_insumos?.length ?? 0;
};

const perPageLabel = (value) => {
    return value === 'all' ? 'Todos' : value;
};
</script>

<template>
    <Head title="Notas de insumos" />

    <SidebarLayout
        title="Notas de insumos"
        subtitle="Registra el uso de insumos y descuenta automáticamente el stock"
    >
        <div class="space-y-6">
            <div
                v-if="flashSuccess"
                class="rounded-3xl border border-green-500/20 bg-green-500/10 px-5 py-4 text-sm font-black text-green-600"
            >
                {{ flashSuccess }}
            </div>

            <div
                v-if="flashError"
                class="rounded-3xl border border-red-500/20 bg-red-500/10 px-5 py-4 text-sm font-black text-red-600"
            >
                {{ flashError }}
            </div>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-bold text-[var(--app-muted)]">Notas</p>
                        <div class="rounded-2xl bg-[var(--app-primary-soft)] p-3 text-[var(--app-primary)]">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 3h10a2 2 0 012 2v16l-3-2-3 2-3-2-3 2V5a2 2 0 012-2zm2 5h6M9 12h6M9 16h4" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-4xl font-black text-[var(--app-text)]">{{ stats.notes }}</p>
                </article>

                <article class="rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-bold text-[var(--app-muted)]">Cantidad utilizada</p>
                        <div class="rounded-2xl bg-red-500/10 p-3 text-red-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 12H4m16 0l-6-6m6 6l-6 6" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-4xl font-black text-red-500">{{ stats.total_used }}</p>
                </article>

                <article class="rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-bold text-[var(--app-muted)]">Insumos distintos</p>
                        <div class="rounded-2xl bg-green-500/10 p-3 text-green-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-4xl font-black text-[var(--app-text)]">{{ stats.unique_insumos }}</p>
                </article>

                <article class="rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-bold text-[var(--app-muted)]">Mayor uso</p>
                        <div class="rounded-2xl bg-yellow-500/10 p-3 text-yellow-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 17l6-6 4 4 6-8M14 7h6v6" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-4xl font-black text-yellow-600">{{ stats.max_usage }}</p>
                </article>
            </section>

            <section class="rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-6 shadow-sm">
                <div class="mb-5 flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.18em] text-[var(--app-primary)]">
                            Registro de uso
                        </p>

                        <h1 class="mt-2 text-3xl font-black text-[var(--app-text)]">
                            Administrar notas de insumos
                        </h1>

                        <p class="mt-1 text-sm font-semibold text-[var(--app-muted)]">
                            Al registrar una nota se descuenta el stock de cada insumo utilizado.
                        </p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3 xl:flex xl:flex-wrap">
                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-[var(--app-border)] bg-[var(--app-card)] px-4 py-3 text-sm font-black text-[var(--app-primary)] transition hover:bg-[var(--app-primary-soft)]"
                            @click="exportNotes('excel')"
                        >
                            Excel
                        </button>

                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-[var(--app-border)] bg-[var(--app-card)] px-4 py-3 text-sm font-black text-[var(--app-primary)] transition hover:bg-[var(--app-primary-soft)]"
                            @click="exportNotes('pdf')"
                        >
                            PDF
                        </button>

                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-[var(--app-border)] bg-[var(--app-card)] px-4 py-3 text-sm font-black text-[var(--app-primary)] transition hover:bg-[var(--app-primary-soft)]"
                            @click="exportNotes('txt')"
                        >
                            TXT
                        </button>
                    </div>
                </div>

                <form class="space-y-4" @submit.prevent="submit">
                    <div
                        v-for="(detail, index) in form.details"
                        :key="index"
                        class="rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-surface-soft)] p-4"
                    >
                        <div class="grid gap-4 xl:grid-cols-[1fr_140px_170px_170px_70px] xl:items-end">
                            <div class="relative">
                                <label class="text-sm font-black text-[var(--app-text)]">
                                    Buscar insumo utilizado
                                </label>

                                <div class="relative mt-2">
                                    <svg class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-[var(--app-primary)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                                    </svg>

                                    <input
                                        v-model="detail.search"
                                        type="text"
                                        placeholder="Buscar por nombre del insumo..."
                                        class="w-full rounded-2xl border-[var(--app-border)] bg-[var(--app-card)] py-3 pl-12 text-[var(--app-text)] focus:border-[var(--app-primary)] focus:ring-[var(--app-primary)]"
                                        @focus="detail.open = true"
                                    />
                                </div>

                                <div
                                    v-if="detail.open"
                                    class="absolute z-20 mt-2 max-h-72 w-full overflow-y-auto rounded-[1.5rem] border border-[var(--app-border)] bg-[var(--app-card)] p-2 shadow-2xl"
                                >
                                    <button
                                        v-for="insumo in filteredInsumos(detail)"
                                        :key="insumo.id"
                                        type="button"
                                        class="flex w-full items-center justify-between gap-3 rounded-2xl px-4 py-3 text-left transition hover:bg-[var(--app-primary-soft)] disabled:cursor-not-allowed disabled:opacity-40"
                                        :disabled="selectedInsumosIds.includes(insumo.id) && Number(detail.insumos_id) !== Number(insumo.id)"
                                        @click="selectInsumo(detail, insumo)"
                                    >
                                        <span class="min-w-0">
                                            <span class="block truncate text-sm font-black text-[var(--app-text)]">
                                                {{ insumo.name }}
                                            </span>

                                            <span class="block truncate text-xs font-semibold text-[var(--app-muted)]">
                                                Stock disponible: {{ insumo.amount }}
                                            </span>
                                        </span>

                                        <span
                                            class="shrink-0 rounded-xl px-3 py-1 text-xs font-black"
                                            :class="insumo.amount <= 0
                                                ? 'bg-red-500/10 text-red-500'
                                                : insumo.amount <= 5
                                                    ? 'bg-yellow-500/10 text-yellow-600'
                                                    : 'bg-green-500/10 text-green-600'"
                                        >
                                            {{ insumo.amount <= 0 ? 'Sin stock' : insumo.amount <= 5 ? 'Bajo' : 'Disponible' }}
                                        </span>
                                    </button>

                                    <div
                                        v-if="filteredInsumos(detail).length === 0"
                                        class="px-4 py-6 text-center text-sm font-bold text-[var(--app-muted)]"
                                    >
                                        No se encontraron insumos.
                                    </div>
                                </div>

                                <p
                                    v-if="form.errors[`details.${index}.insumos_id`]"
                                    class="mt-1 text-sm font-bold text-red-500"
                                >
                                    {{ form.errors[`details.${index}.insumos_id`] }}
                                </p>
                            </div>

                            <div>
                                <label class="text-sm font-black text-[var(--app-text)]">
                                    Cantidad
                                </label>

                                <input
                                    v-model="detail.amount"
                                    type="number"
                                    min="1"
                                    step="1"
                                    inputmode="numeric"
                                    class="mt-2 w-full rounded-2xl border-[var(--app-border)] bg-[var(--app-card)] text-[var(--app-text)] focus:border-[var(--app-primary)] focus:ring-[var(--app-primary)]"
                                    @keydown="blockNegative"
                                    @input="sanitizeAmount(detail)"
                                    @blur="sanitizeAmount(detail)"
                                />

                                <p
                                    v-if="form.errors[`details.${index}.amount`]"
                                    class="mt-1 text-sm font-bold text-red-500"
                                >
                                    {{ form.errors[`details.${index}.amount`] }}
                                </p>
                            </div>

                            <div>
                                <label class="text-sm font-black text-[var(--app-text)]">
                                    Stock actual
                                </label>

                                <div class="mt-2 rounded-2xl border border-[var(--app-border)] bg-[var(--app-card)] px-4 py-3 text-sm font-black text-[var(--app-text)]">
                                    {{ getInsumo(detail.insumos_id)?.amount ?? '-' }}
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-black text-[var(--app-text)]">
                                    Quedaría
                                </label>

                                <div class="mt-2 rounded-2xl border border-[var(--app-border)] bg-[var(--app-card)] px-4 py-3 text-sm font-black text-[var(--app-primary)]">
                                    {{
                                        detail.insumos_id
                                            ? Number(getInsumo(detail.insumos_id)?.amount ?? 0) - Number(detail.amount || 0)
                                            : '-'
                                    }}
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button
                                    type="button"
                                    title="Quitar insumo"
                                    class="rounded-xl bg-red-500/10 p-3 text-red-500 transition hover:bg-red-500/20 disabled:opacity-40"
                                    :disabled="form.details.length === 1"
                                    @click="removeDetail(index)"
                                >
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 7h12M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m2 0v13a1 1 0 01-1 1H8a1 1 0 01-1-1V7h10zM10 11v6M14 11v6" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <p v-if="form.errors.details" class="text-sm font-bold text-red-500">
                        {{ form.errors.details }}
                    </p>

                    <div class="flex flex-col gap-4 rounded-3xl border border-[var(--app-border)] bg-[var(--app-primary-soft)] p-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-black uppercase tracking-[0.18em] text-[var(--app-primary)]">
                                Total a descontar
                            </p>

                            <p class="mt-1 text-4xl font-black text-[var(--app-text)]">
                                {{ totalAmountForm }}
                            </p>

                            <p class="mt-1 text-xs font-bold text-[var(--app-muted)]">
                                Fecha y hora se guardan automáticamente.
                            </p>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row">
                            <button
                                type="button"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-[var(--app-border)] bg-[var(--app-card)] px-5 py-3 text-sm font-black text-[var(--app-muted)]"
                                @click="addDetail"
                            >
                                Agregar otro
                            </button>

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[var(--app-primary)] px-6 py-3 text-sm font-black text-white shadow-sm transition hover:opacity-90 disabled:opacity-60"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Guardando...' : 'Registrar nota' }}
                            </button>
                        </div>
                    </div>
                </form>
            </section>

            <section class="rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-5 shadow-sm">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-7">
                    <div class="sm:col-span-2 xl:col-span-2">
                        <label class="text-sm font-black text-[var(--app-text)]">
                            Buscar en tiempo real
                        </label>

                        <div class="relative mt-2">
                            <svg class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-[var(--app-primary)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                            </svg>

                            <input
                                v-model="search"
                                type="text"
                                placeholder="Insumo, usuario, fecha..."
                                class="w-full rounded-2xl border-[var(--app-border)] bg-[var(--app-surface-soft)] py-3 pl-12 text-[var(--app-text)] focus:border-[var(--app-primary)] focus:ring-[var(--app-primary)]"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-black text-[var(--app-text)]">Administrador</label>

                        <select
                            v-model="adminId"
                            class="mt-2 w-full rounded-2xl border-[var(--app-border)] bg-[var(--app-surface-soft)] text-[var(--app-text)] focus:border-[var(--app-primary)] focus:ring-[var(--app-primary)]"
                        >
                            <option value="">Todos</option>
                            <option v-for="admin in admins" :key="admin.id" :value="admin.id">
                                {{ admin.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-black text-[var(--app-text)]">Desde</label>

                        <input
                            v-model="dateFrom"
                            type="date"
                            class="mt-2 w-full rounded-2xl border-[var(--app-border)] bg-[var(--app-surface-soft)] text-[var(--app-text)] focus:border-[var(--app-primary)] focus:ring-[var(--app-primary)]"
                        />
                    </div>

                    <div>
                        <label class="text-sm font-black text-[var(--app-text)]">Hasta</label>

                        <input
                            v-model="dateTo"
                            type="date"
                            class="mt-2 w-full rounded-2xl border-[var(--app-border)] bg-[var(--app-surface-soft)] text-[var(--app-text)] focus:border-[var(--app-primary)] focus:ring-[var(--app-primary)]"
                        />
                    </div>

                    <div>
                        <label class="text-sm font-black text-[var(--app-text)]">Periodo</label>

                        <select
                            v-model="period"
                            class="mt-2 w-full rounded-2xl border-[var(--app-border)] bg-[var(--app-surface-soft)] text-[var(--app-text)] focus:border-[var(--app-primary)] focus:ring-[var(--app-primary)]"
                        >
                            <option value="all">Todos</option>
                            <option value="today">Hoy</option>
                            <option value="this_week">Esta semana</option>
                            <option value="this_month">Este mes</option>
                            <option value="last_2_months">Últimos 2 meses</option>
                            <option value="last_6_months">Últimos 6 meses</option>
                            <option value="last_year">Último año</option>
                            <option value="last_2_years">Últimos 2 años</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-black text-[var(--app-text)]">Orden</label>

                        <select
                            v-model="sort"
                            class="mt-2 w-full rounded-2xl border-[var(--app-border)] bg-[var(--app-surface-soft)] text-[var(--app-text)] focus:border-[var(--app-primary)] focus:ring-[var(--app-primary)]"
                        >
                            <option value="recent">Más recientes</option>
                            <option value="oldest">Más antiguos</option>
                            <option value="highest_amount">Mayor cantidad usada</option>
                            <option value="lowest_amount">Menor cantidad usada</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-black text-[var(--app-text)]">Mostrar</label>

                        <select
                            v-model="perPage"
                            class="mt-2 w-full rounded-2xl border-[var(--app-border)] bg-[var(--app-surface-soft)] text-[var(--app-text)] focus:border-[var(--app-primary)] focus:ring-[var(--app-primary)]"
                        >
                            <option v-for="option in allowedPerPage" :key="option" :value="option">
                                {{ perPageLabel(option) }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-[var(--app-border)] bg-[var(--app-card)] px-5 py-3 text-sm font-black text-[var(--app-muted)] transition hover:bg-[var(--app-surface-soft)]"
                        @click="clearFilters"
                    >
                        Limpiar filtros
                    </button>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[var(--app-primary-soft)] px-5 py-3 text-sm font-black text-[var(--app-primary-text)] transition hover:opacity-90"
                            @click="todayUses"
                        >
                            Usos de hoy
                        </button>

                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-red-500/10 px-5 py-3 text-sm font-black text-red-500 transition hover:bg-red-500/20"
                            @click="highestUsesMonth"
                        >
                            Mayor uso del mes
                        </button>
                    </div>
                </div>
            </section>

            <section class="relative overflow-hidden rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] shadow-sm">
                <div
                    v-if="tableLoading"
                    class="absolute inset-0 z-30 flex items-center justify-center bg-[var(--app-card)]/80 backdrop-blur-sm"
                >
                    <div class="flex flex-col items-center gap-3 rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] px-7 py-6 shadow-xl">
                        <div class="h-12 w-12 animate-spin rounded-full border-4 border-[var(--app-primary-soft)] border-t-[var(--app-primary)]"></div>
                        <p class="text-sm font-black text-[var(--app-text)]">Actualizando notas...</p>
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-b border-[var(--app-border)] px-6 py-5 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-xl font-black text-[var(--app-text)]">Historial de notas</h2>

                        <p class="mt-1 text-sm font-bold text-[var(--app-muted)]">
                            Mostrando {{ fromRow ?? 0 }} - {{ toRow ?? 0 }} de {{ totalRows ?? 0 }}
                        </p>
                    </div>

                    <p class="text-sm font-black text-[var(--app-primary)]">
                        Registros visibles: {{ rows.length }}
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1050px] text-left">
                        <thead>
                            <tr class="border-b border-[var(--app-border)] bg-[var(--app-surface-soft)] text-xs font-black uppercase tracking-[0.16em] text-[var(--app-muted)]">
                                <th class="px-6 py-4">Fecha / hora</th>
                                <th class="px-6 py-4">Administrador</th>
                                <th class="px-6 py-4">Detalle</th>
                                <th class="px-6 py-4">Cantidad usada</th>
                                <th class="px-6 py-4 text-right">Acciones</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-[var(--app-border)]">
                            <template v-for="note in rows" :key="note.id">
                                <tr class="transition hover:bg-[var(--app-surface-soft)]">
                                    <td class="px-6 py-5">
                                        <p class="font-black text-[var(--app-text)]">{{ note.date }}</p>
                                        <p class="text-xs font-semibold text-[var(--app-muted)]">{{ note.hour }}</p>
                                    </td>

                                    <td class="px-6 py-5">
                                        <p class="font-black text-[var(--app-text)]">
                                            {{ note.users?.name ?? 'Sin usuario' }}
                                        </p>

                                        <p class="text-xs font-semibold text-[var(--app-muted)]">
                                            {{ note.users?.email ?? 'Sin correo' }}
                                        </p>
                                    </td>

                                    <td class="px-6 py-5">
                                        <p class="font-black text-[var(--app-text)]">
                                            {{ detailsCount(note) }} insumos usados
                                        </p>

                                        <p class="text-xs font-semibold text-[var(--app-muted)]">
                                            Se descontó del stock automáticamente
                                        </p>
                                    </td>

                                    <td class="px-6 py-5">
                                        <span class="rounded-xl bg-red-500/10 px-3 py-1 text-sm font-black text-red-500">
                                            {{ amountTotal(note) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-5">
                                        <div class="flex justify-end gap-2">
                                            <button
                                                type="button"
                                                title="Ver detalle"
                                                class="rounded-xl bg-[var(--app-surface-soft)] p-2 text-[var(--app-primary)] transition hover:bg-[var(--app-primary-soft)] hover:text-[var(--app-primary-text)]"
                                                @click="toggleDetails(note)"
                                            >
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                                                </svg>
                                            </button>

                                            <button
                                                type="button"
                                                title="Eliminar nota"
                                                class="rounded-xl bg-red-500/10 p-2 text-red-500 transition hover:bg-red-500/20"
                                                @click="confirmDelete(note)"
                                            >
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 7h12M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m2 0v13a1 1 0 01-1 1H8a1 1 0 01-1-1V7h10zM10 11v6M14 11v6" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="expandedNoteId === note.id">
                                    <td colspan="5" class="bg-[var(--app-surface-soft)] px-6 py-5">
                                        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                                            <div
                                                v-for="detail in note.details_insumos"
                                                :key="detail.id"
                                                class="rounded-2xl border border-[var(--app-border)] bg-[var(--app-card)] p-4"
                                            >
                                                <p class="font-black text-[var(--app-text)]">
                                                    {{ detail.insumos?.name ?? 'Insumo eliminado' }}
                                                </p>

                                                <p class="mt-1 text-sm font-semibold text-[var(--app-muted)]">
                                                    Cantidad utilizada:
                                                    <span class="font-black text-red-500">{{ detail.amount }}</span>
                                                </p>

                                                <p class="mt-1 text-xs font-semibold text-[var(--app-muted)]">
                                                    Stock actual: {{ detail.insumos?.amount ?? '-' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                            <tr v-if="rows.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <p class="text-lg font-black text-[var(--app-text)]">No hay notas registradas</p>
                                    <p class="mt-1 text-sm font-semibold text-[var(--app-muted)]">
                                        Registra el uso de insumos para descontar stock.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="lastPage > 1"
                    class="flex items-center justify-between border-t border-[var(--app-border)] px-6 py-4"
                >
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-[var(--app-border)] px-4 py-2 text-sm font-black text-[var(--app-muted)] disabled:opacity-40"
                        :disabled="currentPage <= 1"
                        @click="goToPage(currentPage - 1)"
                    >
                        Anterior
                    </button>

                    <p class="text-sm font-black text-[var(--app-muted)]">
                        Página {{ currentPage }} de {{ lastPage }}
                    </p>

                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-[var(--app-border)] px-4 py-2 text-sm font-black text-[var(--app-muted)] disabled:opacity-40"
                        :disabled="currentPage >= lastPage"
                        @click="goToPage(currentPage + 1)"
                    >
                        Siguiente
                    </button>
                </div>
            </section>
        </div>

        <div
            v-if="noteToDelete"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-8 backdrop-blur-sm"
            @mousedown.self="closeDeleteModal"
        >
            <div class="w-full max-w-md rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-6 shadow-2xl">
                <h3 class="text-xl font-black text-[var(--app-text)]">
                    Eliminar nota
                </h3>

                <p class="mt-2 text-sm font-semibold text-[var(--app-muted)]">
                    Al eliminar esta nota se restaurará el stock de los insumos utilizados.
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-2xl border border-[var(--app-border)] px-5 py-3 text-sm font-black text-[var(--app-muted)]"
                        @click="closeDeleteModal"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        class="rounded-2xl bg-red-600 px-5 py-3 text-sm font-black text-white"
                        @click="destroyNote"
                    >
                        Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    </SidebarLayout>
</template>
