<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    checkedInCount: {
        type: Number,
        default: 0,
    },
    guestCount: {
        type: Number,
        default: 0,
    },
    resetResult: {
        type: Object,
        default: null,
    },
    qrResetResult: {
        type: Object,
        default: null,
    },
    scanLogs: {
        type: Array,
        default: () => [],
    },
});

const resetForm = useForm({});
const qrForm = useForm({});

const resetAttendance = () => {
    const confirmed = window.confirm('This will clear all attendance check-ins. Continue?');
    if (!confirmed) return;

    resetForm.post('/settings/attendance/reset', {
        preserveScroll: true,
    });
};

const regenerateQRCodes = () => {
    const confirmed = window.confirm('This will regenerate all QR codes. Old codes will stop working. Continue?');
    if (!confirmed) return;

    qrForm.post('/settings/qr/regenerate', {
        preserveScroll: true,
    });
};

const formatDateTime = (value) => {
    if (!value) return '--';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return String(value);
    }
    return date.toLocaleString();
};

const statusLabel = (value) => {
    if (!value) return 'unknown';
    return String(value).replace(/_/g, ' ');
};

const statusBadgeClass = (value) => {
    switch (value) {
        case 'checked_in':
            return 'bg-emerald-500/15 text-emerald-200 border border-emerald-500/30';
        case 'already_checked_in':
            return 'bg-amber-500/15 text-amber-200 border border-amber-500/30';
        case 'expired':
        case 'invalid':
            return 'bg-rose-500/15 text-rose-200 border border-rose-500/30';
        case 'not_found':
            return 'bg-orange-500/15 text-orange-200 border border-orange-500/30';
        default:
            return 'bg-slate-500/15 text-slate-200 border border-slate-500/30';
    }
};
</script>

<template>
    <Head title="Settings" />

    <div class="min-h-screen bg-gray-950 text-white font-sans relative overflow-hidden">
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-[-15%] left-[-10%] w-[40rem] h-[40rem] bg-rose-900/20 rounded-full mix-blend-screen filter blur-[100px] animate-blob"></div>
            <div class="absolute bottom-[-15%] right-[-10%] w-[40rem] h-[40rem] bg-blue-900/20 rounded-full mix-blend-screen filter blur-[100px] animate-blob animation-delay-2000"></div>
        </div>

        <nav class="relative z-20 border-b border-white/10 bg-gray-900/50 backdrop-blur-md">
            <div class="container mx-auto px-6 py-4 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-500/20 to-blue-500/20 border border-white/10 flex items-center justify-center shadow-inner">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-white">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h3m-3 12h3m-6.364-9.364l2.122-2.122m5.486 5.486l2.122-2.122m-9.9 9.9l2.122-2.122m5.486 5.486l2.122-2.122M12 9a3 3 0 100 6 3 3 0 000-6z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-wide text-white">Settings</h1>
                        <p class="text-[0.65rem] uppercase tracking-[0.2em] text-gray-400">Admin Only</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <Link href="/dashboard" class="btn btn-sm btn-ghost text-gray-400 hover:text-white hover:bg-white/5 font-light tracking-wider">
                        DASHBOARD
                    </Link>
                    <Link href="/logout" method="post" as="button" class="btn btn-sm btn-ghost text-gray-400 hover:text-white hover:bg-white/5 font-light tracking-wider">
                        LOGOUT
                    </Link>
                </div>
            </div>
        </nav>

        <main class="relative z-10 container mx-auto px-6 py-12">
            <div class="max-w-3xl">
                <h2 class="text-3xl font-light text-white mb-2">System Settings</h2>
                <p class="text-gray-400">Use these tools for testing and maintenance.</p>

                <div class="mt-6 rounded-2xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm text-rose-200">
                    Resetting attendance will clear all guest check-ins. Use for testing only.
                </div>

                <div v-if="props.resetResult" class="mt-4 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-200">
                    Attendance reset complete. Cleared {{ props.resetResult.cleared }} records.
                </div>
                <div v-if="props.qrResetResult" class="mt-4 rounded-2xl border border-blue-500/30 bg-blue-500/10 p-4 text-sm text-blue-200">
                    QR codes regenerated for {{ props.qrResetResult.updated }} guests.
                </div>

                <div class="mt-8 rounded-3xl bg-gray-900/40 border border-white/10 p-8">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                        <div>
                            <h3 class="text-xl font-semibold text-white">Reset Attendance Status</h3>
                            <p class="text-sm text-gray-400 mt-2">All guests will return to pending status.</p>
                            <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-gray-800/60 px-3 py-1 text-xs text-gray-300">
                                Checked in: <span class="text-white font-semibold">{{ props.checkedInCount }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="resetAttendance"
                                :disabled="resetForm.processing || props.checkedInCount === 0"
                                class="btn bg-rose-600 hover:bg-rose-500 text-white border-none shadow-lg shadow-rose-900/20 disabled:opacity-50"
                            >
                                <span v-if="resetForm.processing" class="loading loading-spinner loading-sm"></span>
                                <span v-else>Reset Attendance</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-6 rounded-3xl bg-gray-900/40 border border-white/10 p-8">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                        <div>
                            <h3 class="text-xl font-semibold text-white">Regenerate QR Codes</h3>
                            <p class="text-sm text-gray-400 mt-2">Invalidate existing codes and create new ones for all guests.</p>
                            <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-gray-800/60 px-3 py-1 text-xs text-gray-300">
                                Guests: <span class="text-white font-semibold">{{ props.guestCount }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="regenerateQRCodes"
                                :disabled="qrForm.processing || props.guestCount === 0"
                                class="btn bg-blue-600 hover:bg-blue-500 text-white border-none shadow-lg shadow-blue-900/20 disabled:opacity-50"
                            >
                                <span v-if="qrForm.processing" class="loading loading-spinner loading-sm"></span>
                                <span v-else>Regenerate QR Codes</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-8 rounded-3xl bg-gray-900/40 border border-white/10 p-8">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                        <div>
                            <h3 class="text-xl font-semibold text-white">Scan Logs</h3>
                            <p class="text-sm text-gray-400 mt-2">Latest QR scan attempts with status and error details.</p>
                        </div>
                    </div>

                    <div class="mt-6 overflow-x-auto">
                        <table class="table w-full text-sm">
                            <thead>
                                <tr class="text-gray-400">
                                    <th class="font-medium">Time</th>
                                    <th class="font-medium">Status</th>
                                    <th class="font-medium">Guest</th>
                                    <th class="font-medium">HTTP</th>
                                    <th class="font-medium">Error</th>
                                    <th class="font-medium">IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="log in props.scanLogs" :key="log.id" class="text-gray-200">
                                    <td class="whitespace-nowrap">{{ formatDateTime(log.created_at) }}</td>
                                    <td>
                                        <span class="px-2 py-1 rounded-full text-xs uppercase tracking-wider"
                                            :class="statusBadgeClass(log.status)"
                                        >
                                            {{ statusLabel(log.status) }}
                                        </span>
                                    </td>
                                    <td class="max-w-[12rem] truncate">
                                        <span v-if="log.guest">{{ log.guest.name }}</span>
                                        <span v-else-if="log.guest_id">Guest #{{ log.guest_id }}</span>
                                        <span v-else>--</span>
                                    </td>
                                    <td class="text-gray-300">{{ log.http_status || '--' }}</td>
                                    <td class="max-w-[24rem] truncate text-gray-300" :title="log.error_message || ''">
                                        {{ log.error_message || '--' }}
                                    </td>
                                    <td class="text-gray-400">{{ log.ip_address || '--' }}</td>
                                </tr>
                                <tr v-if="!props.scanLogs.length">
                                    <td colspan="6" class="text-center text-gray-500 py-6">No scan logs yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
.animate-blob {
    animation: blob 10s infinite;
}
.animation-delay-2000 {
    animation-delay: 2s;
}
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}
</style>
