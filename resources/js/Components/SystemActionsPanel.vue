<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    search: {
        type: String,
        default: '',
    },
    selectedUser: {
        type: Object,
        default: null,
    },
});

const page = usePage();

const currentRoles = computed(() => {
    const user = page.props.auth?.user ?? null;

    const roles =
        user?.roles ??
        user?.role_names ??
        user?.roleNames ??
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
        user?.role ??
        user?.user_role ??
        user?.role_name ??
        null;

    return singleRole ? [singleRole] : [];
});

const can = (roles) => {
    return roles.some((role) => currentRoles.value.includes(role));
};

const routeOrNull = (routeName) => {
    try {
        if (typeof route !== 'function') {
            return null;
        }

        try {
            const routerInstance = route();

            if (routerInstance?.has && !routerInstance.has(routeName)) {
                return null;
            }
        } catch {
            //
        }

        return route(routeName);
    } catch {
        return null;
    }
};

const routeWithQuery = (routeName, query = {}) => {
    const baseUrl = routeOrNull(routeName);

    if (!baseUrl) {
        return null;
    }

    const params = new URLSearchParams();

    Object.entries(query).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
            params.append(key, value);
        }
    });

    const queryString = params.toString();

    return queryString ? `${baseUrl}?${queryString}` : baseUrl;
};

const makeAction = ({
    key,
    title,
    description,
    category,
    roles,
    routeName,
    query = {},
    icon,
    keywords = [],
}) => {
    const href = routeWithQuery(routeName, query);

    if (!href) {
        return null;
    }

    return {
        key,
        title,
        description,
        category,
        roles,
        href,
        icon,
        keywords,
    };
};

const selectedRole = computed(() => {
    return props.selectedUser?.roles?.[0] ?? null;
});

const selectedEmail = computed(() => {
    return props.selectedUser?.email ?? '';
});

const baseActions = computed(() => {
    const actions = [
        makeAction({
            key: 'dashboard',
            title: 'Ir al dashboard',
            description: 'Volver al panel principal del sistema.',
            category: 'General',
            roles: ['Master', 'Administrador', 'Mesero', 'Cliente'],
            routeName: 'dashboard',
            icon: 'dashboard',
            keywords: ['inicio', 'panel', 'principal', 'home'],
        }),

        makeAction({
            key: 'profile',
            title: 'Mi perfil',
            description: 'Ver y editar mis datos de cuenta.',
            category: 'Cuenta',
            roles: ['Master', 'Administrador', 'Mesero', 'Cliente'],
            routeName: 'profile.edit',
            icon: 'user',
            keywords: ['perfil', 'cuenta', 'usuario', 'datos'],
        }),

        makeAction({
            key: 'reservation-tables-index',
            title: 'Visualizar mesas',
            description: 'Gestionar mesas, capacidad y estado físico.',
            category: 'Reservas',
            roles: ['Master', 'Administrador', 'Mesero'],
            routeName: 'reservation.tables.index',
            icon: 'tables',
            keywords: ['mesas', 'mesa', 'capacidad', 'salón', 'disponible', 'inactiva', 'mantenimiento'],
        }),

        makeAction({
            key: 'reservation-tables-create',
            title: 'Añadir mesa',
            description: 'Registrar una nueva mesa para reservas y ventas.',
            category: 'Reservas',
            roles: ['Master', 'Administrador', 'Mesero'],
            routeName: 'reservation.tables.index',
            query: {
                action: 'create',
            },
            icon: 'plus',
            keywords: ['crear mesa', 'nueva mesa', 'agregar mesa', 'registrar mesa'],
        }),

        makeAction({
            key: 'admin-reservations-index',
            title: 'Visualizar reservas internas',
            description: 'Gestionar reservas, clientes, mesas y estados.',
            category: 'Reservas',
            roles: ['Master', 'Administrador', 'Mesero'],
            routeName: 'admin.reservations.index',
            icon: 'calendar',
            keywords: ['reservas', 'reserva', 'reservaciones', 'mesa reservada', 'cliente reserva'],
        }),

        makeAction({
            key: 'admin-reservations-create',
            title: 'Añadir reserva interna',
            description: 'Registrar una reserva seleccionando cliente, fecha, hora y mesas.',
            category: 'Reservas',
            roles: ['Master', 'Administrador', 'Mesero'],
            routeName: 'admin.reservations.index',
            query: {
                action: 'create',
            },
            icon: 'reservation',
            keywords: ['crear reserva', 'nueva reserva', 'agregar reserva', 'registrar reserva', 'reservar mesa'],
        }),

        makeAction({
            key: 'admin-reservations-pending',
            title: 'Reservas pendientes',
            description: 'Ver reservas que todavía están pendientes de confirmación.',
            category: 'Reservas',
            roles: ['Master', 'Administrador', 'Mesero'],
            routeName: 'admin.reservations.index',
            query: {
                state: 'Pendiente',
            },
            icon: 'clock',
            keywords: ['reservas pendientes', 'pendiente', 'confirmar reserva'],
        }),

        makeAction({
            key: 'admin-reservations-confirmed',
            title: 'Reservas confirmadas',
            description: 'Ver reservas confirmadas para atención.',
            category: 'Reservas',
            roles: ['Master', 'Administrador', 'Mesero'],
            routeName: 'admin.reservations.index',
            query: {
                state: 'Confirmada',
            },
            icon: 'calendar',
            keywords: ['reservas confirmadas', 'confirmada', 'confirmadas'],
        }),

        makeAction({
            key: 'admin-reservations-in-process',
            title: 'Reservas en proceso',
            description: 'Ver reservas que están siendo atendidas.',
            category: 'Reservas',
            roles: ['Master', 'Administrador', 'Mesero'],
            routeName: 'admin.reservations.index',
            query: {
                state: 'En Proceso',
            },
            icon: 'reservation',
            keywords: ['reservas en proceso', 'en proceso', 'atendiendo reserva'],
        }),

        makeAction({
            key: 'client-reservations-index',
            title: 'Mis reservas',
            description: 'Ver historial, estado y cancelación de mis reservas.',
            category: 'Reservas cliente',
            roles: ['Cliente'],
            routeName: 'client.reservations.index',
            icon: 'calendar',
            keywords: ['mis reservas', 'mi reserva', 'historial de reservas', 'estado reserva', 'cancelar reserva'],
        }),

        makeAction({
            key: 'client-reservations-create',
            title: 'Reservar mesa',
            description: 'Crear una nueva reserva seleccionando fecha, hora y mesas disponibles.',
            category: 'Reservas cliente',
            roles: ['Cliente'],
            routeName: 'client.reservations.index',
            query: {
                action: 'create',
            },
            icon: 'reservation',
            keywords: ['reservar', 'reservar mesa', 'nueva reserva', 'crear reserva', 'hacer reserva'],
        }),

        makeAction({
            key: 'view-employees',
            title: 'Visualizar empleados',
            description: 'Listar, buscar, editar y exportar empleados.',
            category: 'Administración',
            roles: ['Master', 'Administrador'],
            routeName: 'administracion.empleados.index',
            icon: 'users',
            keywords: ['empleados', 'meseros', 'personal'],
        }),

        makeAction({
            key: 'add-employee',
            title: 'Añadir empleado',
            description: 'Registrar un nuevo empleado con rol Mesero.',
            category: 'Administración',
            roles: ['Master', 'Administrador'],
            routeName: 'administracion.empleados.index',
            query: {
                action: 'create',
            },
            icon: 'plus',
            keywords: ['crear empleado', 'nuevo empleado', 'agregar empleado'],
        }),

        makeAction({
            key: 'view-clients',
            title: 'Visualizar clientes',
            description: 'Listar clientes, reservas y compras registradas.',
            category: 'Administración',
            roles: ['Master', 'Administrador'],
            routeName: 'administracion.clientes.index',
            icon: 'clients',
            keywords: ['clientes', 'cliente'],
        }),

        makeAction({
            key: 'add-client',
            title: 'Añadir cliente',
            description: 'Registrar un nuevo cliente en el sistema.',
            category: 'Administración',
            roles: ['Master', 'Administrador'],
            routeName: 'administracion.clientes.index',
            query: {
                action: 'create',
            },
            icon: 'plus',
            keywords: ['crear cliente', 'nuevo cliente', 'agregar cliente'],
        }),

        makeAction({
            key: 'view-admins',
            title: 'Visualizar administradores',
            description: 'Revisar usuarios con rol Administrador.',
            category: 'Seguridad',
            roles: ['Master', 'Administrador'],
            routeName: 'administracion.administradores.index',
            icon: 'shield',
            keywords: ['administradores', 'seguridad'],
        }),

        makeAction({
            key: 'add-admin',
            title: 'Añadir administrador',
            description: 'Registrar un usuario administrador.',
            category: 'Seguridad',
            roles: ['Master'],
            routeName: 'administracion.administradores.index',
            query: {
                action: 'create',
            },
            icon: 'shield',
            keywords: ['crear administrador', 'nuevo administrador'],
        }),

        makeAction({
            key: 'search-users',
            title: 'Buscar usuario',
            description: 'Consultar datos y estadísticas por usuario.',
            category: 'Consultas',
            roles: ['Master', 'Administrador'],
            routeName: 'administracion.usuarios.buscar',
            icon: 'search',
            keywords: ['buscar usuario', 'consultar usuario'],
        }),

        makeAction({
            key: 'audit-log',
            title: 'Ver bitácora',
            description: 'Revisar acciones, accesos y movimientos del sistema.',
            category: 'Seguridad',
            roles: ['Master', 'Administrador'],
            routeName: 'administracion.bitacora.index',
            icon: 'clock',
            keywords: ['bitacora', 'auditoria', 'logs', 'acciones'],
        }),
    ].filter(Boolean);

    if (props.selectedUser && can(['Master', 'Administrador'])) {
        if (selectedRole.value === 'Mesero') {
            const action = makeAction({
                key: 'selected-employee',
                title: 'Ver empleado seleccionado',
                description: `Abrir el listado filtrado por ${selectedEmail.value}.`,
                category: 'Usuario seleccionado',
                roles: ['Master', 'Administrador'],
                routeName: 'administracion.empleados.index',
                query: {
                    search: selectedEmail.value,
                },
                icon: 'users',
                keywords: ['empleado seleccionado', 'mesero seleccionado'],
            });

            if (action) {
                actions.unshift(action);
            }
        }

        if (selectedRole.value === 'Cliente') {
            const action = makeAction({
                key: 'selected-client',
                title: 'Ver cliente seleccionado',
                description: `Abrir el listado filtrado por ${selectedEmail.value}.`,
                category: 'Usuario seleccionado',
                roles: ['Master', 'Administrador'],
                routeName: 'administracion.clientes.index',
                query: {
                    search: selectedEmail.value,
                },
                icon: 'clients',
                keywords: ['cliente seleccionado'],
            });

            if (action) {
                actions.unshift(action);
            }

            const reservationAction = makeAction({
                key: 'selected-client-reservations',
                title: 'Buscar reservas del cliente',
                description: `Abrir reservas internas filtrando por ${selectedEmail.value}.`,
                category: 'Usuario seleccionado',
                roles: ['Master', 'Administrador'],
                routeName: 'admin.reservations.index',
                query: {
                    search: selectedEmail.value,
                },
                icon: 'calendar',
                keywords: ['reservas cliente seleccionado', 'historial reserva cliente'],
            });

            if (reservationAction) {
                actions.unshift(reservationAction);
            }
        }

        if (selectedRole.value === 'Administrador' || selectedRole.value === 'Master') {
            const action = makeAction({
                key: 'selected-admin',
                title: 'Ver administrador seleccionado',
                description: `Abrir el listado filtrado por ${selectedEmail.value}.`,
                category: 'Usuario seleccionado',
                roles: ['Master', 'Administrador'],
                routeName: 'administracion.administradores.index',
                query: {
                    search: selectedEmail.value,
                },
                icon: 'shield',
                keywords: ['administrador seleccionado'],
            });

            if (action) {
                actions.unshift(action);
            }
        }
    }

    return actions;
});

const visibleActions = computed(() => {
    const text = props.search.toLowerCase().trim();

    return baseActions.value
        .filter((action) => can(action.roles))
        .filter((action) => {
            if (!text) {
                return true;
            }

            return [
                action.title,
                action.description,
                action.category,
                ...(action.keywords ?? []),
            ]
                .join(' ')
                .toLowerCase()
                .includes(text);
        });
});

const groupedActions = computed(() => {
    return visibleActions.value.reduce((groups, action) => {
        if (!groups[action.category]) {
            groups[action.category] = [];
        }

        groups[action.category].push(action);

        return groups;
    }, {});
});

const iconPath = (icon) => {
    const icons = {
        dashboard: 'M4 5h7v7H4V5zm9 0h7v5h-7V5zM4 14h7v5H4v-5zm9-2h7v7h-7v-7z',
        user: 'M12 12a4 4 0 100-8 4 4 0 000 8zM4 21a8 8 0 0116 0',
        users: 'M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m0-4a4 4 0 118 0 4 4 0 01-8 0z',
        clients: 'M16 11a4 4 0 100-8 4 4 0 000 8zM8 13a4 4 0 100-8 4 4 0 000 8zM2 21a6 6 0 0112 0M12 21a6 6 0 0110 0',
        shield: 'M12 3l7 4v5c0 5-3 8-7 9-4-1-7-4-7-9V7l7-4z',
        plus: 'M12 5v14M5 12h14',
        search: 'M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z',
        clock: 'M12 8v5l3 2m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        tables: 'M3 10h18M5 10l1.5 10M18.5 20L20 10M8 10V6a4 4 0 018 0v4M7 20h10',
        calendar: 'M8 7V3m8 4V3M5 11h14M6 5h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2z',
        reservation: 'M9 12l2 2 4-4M7 4h10a2 2 0 012 2v14l-4-2-4 2-4-2-4 2V6a2 2 0 012-2z',
    };

    return icons[icon] ?? icons.search;
};
</script>

<template>
    <section class="rounded-[2rem] border border-[var(--app-border)] bg-[var(--app-card)] p-6 shadow-sm">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-black uppercase tracking-[0.18em] text-[var(--app-primary)]">
                    Acciones disponibles
                </p>

                <h3 class="mt-1 text-2xl font-black text-[var(--app-text)]">
                    ¿Qué puedes hacer?
                </h3>

                <p class="mt-1 text-sm font-semibold text-[var(--app-muted)]">
                    Las opciones se muestran según tu rol y la búsqueda actual.
                </p>
            </div>

            <span class="rounded-2xl bg-[var(--app-primary-soft)] px-4 py-2 text-xs font-black text-[var(--app-primary-text)]">
                {{ currentRoles.join(', ') || 'Sin rol' }}
            </span>
        </div>

        <div v-if="visibleActions.length > 0" class="mt-6 space-y-6">
            <div
                v-for="(actions, category) in groupedActions"
                :key="category"
            >
                <p class="mb-3 text-xs font-black uppercase tracking-[0.18em] text-[var(--app-muted)]">
                    {{ category }}
                </p>

                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    <Link
                        v-for="action in actions"
                        :key="action.key"
                        :href="action.href"
                        class="group rounded-[1.5rem] border border-[var(--app-border)] bg-[var(--app-surface-soft)] p-4 transition hover:-translate-y-0.5 hover:border-[var(--app-primary)] hover:bg-[var(--app-primary-soft)] hover:shadow-lg"
                    >
                        <div class="flex items-start gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[var(--app-card)] text-[var(--app-primary)] shadow-sm transition group-hover:scale-105">
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
                                        :d="iconPath(action.icon)"
                                    />
                                </svg>
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="font-black text-[var(--app-text)]">
                                    {{ action.title }}
                                </p>

                                <p class="mt-1 line-clamp-2 text-sm font-semibold text-[var(--app-muted)]">
                                    {{ action.description }}
                                </p>
                            </div>

                            <svg
                                class="h-5 w-5 shrink-0 text-[var(--app-muted)] transition group-hover:translate-x-1 group-hover:text-[var(--app-primary)]"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                        </div>
                    </Link>
                </div>
            </div>
        </div>

        <div
            v-else
            class="mt-6 rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface-soft)] p-6 text-center"
        >
            <p class="font-black text-[var(--app-text)]">
                No hay acciones disponibles
            </p>

            <p class="mt-1 text-sm font-semibold text-[var(--app-muted)]">
                Tu rol no tiene permisos para estas opciones o la búsqueda no coincide.
            </p>
        </div>
    </section>
</template>
