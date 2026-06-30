<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const open = ref(true);

const user = computed(() => page.props.auth?.user ?? null);

const userRoles = computed(() => {
    const roles =
        user.value?.roles ??
        user.value?.role_names ??
        user.value?.roleNames ??
        null;

    if (Array.isArray(roles)) {
        return roles
            .map((role) => {
                if (typeof role === 'string') {
                    return role;
                }

                return role?.name;
            })
            .filter(Boolean);
    }

    if (roles && typeof roles === 'object') {
        return Object.values(roles)
            .map((role) => {
                if (typeof role === 'string') {
                    return role;
                }

                return role?.name;
            })
            .filter(Boolean);
    }

    const singleRole =
        user.value?.role ??
        user.value?.user_role ??
        user.value?.role_name ??
        null;

    return singleRole ? [singleRole] : [];
});

const hasAnyRole = (allowedRoles) => {
    return userRoles.value.some((role) => allowedRoles.includes(role));
};

const menuItems = computed(() => {
    const items = [];

    if (hasAnyRole(['Master', 'Administrador', 'Mesero'])) {
        items.push({
            label: 'Mesas',
            routeName: 'reservation.tables.index',
            path: '/mesas',
            icon: 'M3 10h18M5 10l1.5 10M18.5 20L20 10M8 10V6a4 4 0 018 0v4M7 20h10',
        });

        items.push({
            label: 'Reservas internas',
            routeName: 'admin.reservations.index',
            path: '/admin/reservas',
            icon: 'M8 7V3m8 4V3M5 11h14M6 5h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2zm3 10h3m3 0h1',
        });
    }

    if (hasAnyRole(['Cliente'])) {
        items.push({
            label: 'Mis reservas',
            routeName: 'client.reservations.index',
            path: '/cliente/reservas',
            icon: 'M9 12l2 2 4-4M7 4h10a2 2 0 012 2v14l-4-2-4 2-4-2-4 2V6a2 2 0 012-2z',
        });
    }

    return items;
});

const sectionActive = computed(() => {
    return page.url.startsWith('/mesas')
        || page.url.startsWith('/admin/reservas')
        || page.url.startsWith('/cliente/reservas');
});

const isActive = (path) => {
    return page.url === path || page.url.startsWith(`${path}/`);
};

onMounted(() => {
    const saved = localStorage.getItem('sidebar.reservas.open');

    if (saved !== null) {
        open.value = saved === 'true';
    }

    if (sectionActive.value) {
        open.value = true;
    }
});

watch(open, (value) => {
    localStorage.setItem('sidebar.reservas.open', value ? 'true' : 'false');
});
</script>

<template>
    <div v-if="menuItems.length" class="mt-3">
        <button
            type="button"
            class="flex w-full items-center justify-between rounded-2xl px-4 py-3 text-sm font-black transition"
            :class="sectionActive
                ? 'bg-[var(--app-primary-soft)] text-[var(--app-primary-text)]'
                : 'text-[var(--app-muted)] hover:bg-[var(--app-surface-soft)] hover:text-[var(--app-text)]'"
            @click="open = !open"
        >
            <span class="flex items-center gap-3">
                <svg
                    class="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M8 7V3m8 4V3M5 11h14M6 5h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2z"
                    />
                </svg>

                Reservas
            </span>

            <svg
                class="h-4 w-4 transition"
                :class="open ? 'rotate-180' : ''"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                />
            </svg>
        </button>

        <div v-if="open" class="mt-2 space-y-1 pl-2">
            <Link
                v-for="item in menuItems"
                :key="item.path"
                :href="route(item.routeName)"
                class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-bold transition"
                :class="isActive(item.path)
                    ? 'bg-[var(--app-primary-soft)] text-[var(--app-primary-text)]'
                    : 'text-[var(--app-muted)] hover:bg-[var(--app-surface-soft)] hover:text-[var(--app-text)]'"
            >
                <svg
                    class="h-5 w-5 shrink-0"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        :d="item.icon"
                    />
                </svg>

                <span class="truncate">
                    {{ item.label }}
                </span>
            </Link>
        </div>
    </div>
</template>
