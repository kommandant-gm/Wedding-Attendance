<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    EyeIcon,
    XMarkIcon,
    CalendarIcon,
    DevicePhoneMobileIcon,
    FunnelIcon,
    ArrowDownTrayIcon,
    PencilIcon,
    ArrowLeftIcon,
    QrCodeIcon,
    CheckCircleIcon
} from '@heroicons/vue/24/outline';
import JSZip from 'jszip';
import { saveAs } from 'file-saver';
import QRCode from 'qrcode';
import axios from 'axios';

const props = defineProps({
    guests: {
        type: Array,
        default: () => [],
    },
});

const selectedGuest = ref(null);
const isModalOpen = ref(false);
const editingGuest = ref(null);
const isEditModalOpen = ref(false);
const isQRModalOpen = ref(false);
const qrDataUrl = ref('');
const downloadingBulk = ref(false);
const qrError = ref('');
const qrTokenCache = new Map();
const editForm = ref({
    name: '',
    phone: '',
    table_name: '',
    hall: '',
});

const fetchQrToken = async (guest) => {
    if (!guest?.id) {
        throw new Error('Missing guest ID');
    }

    if (qrTokenCache.has(guest.id)) {
        return qrTokenCache.get(guest.id);
    }

    try {
        const response = await axios.get(`/api/guests/${guest.id}/qr-token`);
        const token = response.data?.token;
        if (!token) {
            throw new Error('Missing QR token');
        }
        qrTokenCache.set(guest.id, token);
        return token;
    } catch (error) {
        console.error('Failed to fetch QR token', error);
        qrError.value = 'Unable to load QR code. Please try again.';
        throw error;
    }
};

const openModal = (guest) => {
    selectedGuest.value = guest;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => selectedGuest.value = null, 300);
};

const openEditModal = (guest) => {
    editingGuest.value = guest;
    editForm.value = {
        name: guest.name,
        phone: guest.phone || '',
        table_name: guest.table_name || '',
        hall: guest.hall || '',
    };
    isEditModalOpen.value = true;
};

const closeEditModal = () => {
    isEditModalOpen.value = false;
    setTimeout(() => {
        editingGuest.value = null;
        editForm.value = { name: '', phone: '', table_name: '', hall: '' };
    }, 300);
};

const saveGuest = async () => {
    if (!editingGuest.value) return;

    router.put(`/api/guests/${editingGuest.value.id}`, editForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal();
        },
        onError: (errors) => {
            console.error('Failed to update guest:', errors);
        },
    });
};

const openQRModal = async (guest) => {
    qrError.value = '';
    selectedGuest.value = guest;

    try {
        const token = await fetchQrToken(guest);
        qrDataUrl.value = await QRCode.toDataURL(token, {
            width: 512,
            margin: 2,
            color: { dark: '#8b5cf6', light: '#ffffff' }
        });
        isQRModalOpen.value = true;
    } catch (error) {
        selectedGuest.value = null;
        qrDataUrl.value = '';
    }
};

const closeQRModal = () => {
    isQRModalOpen.value = false;
    setTimeout(() => {
        selectedGuest.value = null;
        qrDataUrl.value = '';
    }, 300);
};

const downloadQRCode = async (guest) => {
    qrError.value = '';

    try {
        const token = await fetchQrToken(guest);
        const canvas = document.createElement('canvas');
        await QRCode.toCanvas(canvas, token, {
            width: 512,
            margin: 2,
            color: { dark: '#8b5cf6', light: '#ffffff' }
        });
        canvas.toBlob((blob) => {
            if (blob) {
                saveAs(blob, `${guest.name.replace(/\s+/g, '-')}-${guest.id}.png`);
            }
        });
    } catch (error) {
        // fetchQrToken handles messaging
    }
};

const downloadAllQRCodes = async () => {
    downloadingBulk.value = true;
    qrError.value = '';
    const zip = new JSZip();
    let generatedCount = 0;
    let failedCount = 0;

    for (const guest of props.guests) {
        try {
            const token = await fetchQrToken(guest);
            const canvas = document.createElement('canvas');
            await QRCode.toCanvas(canvas, token, {
                width: 512,
                margin: 2,
                color: { dark: '#8b5cf6', light: '#ffffff' }
            });
            const blob = await new Promise(resolve => canvas.toBlob(resolve));
            if (!blob) {
                failedCount++;
                continue;
            }
            zip.file(`${guest.name.replace(/\s+/g, '-')}-${guest.id}.png`, blob);
            generatedCount++;
        } catch (error) {
            failedCount++;
        }
    }

    if (generatedCount > 0) {
        const content = await zip.generateAsync({ type: 'blob' });
        saveAs(content, `vvip-wedding-qrcodes-${Date.now()}.zip`);
    }

    if (failedCount > 0) {
        qrError.value = 'Some QR codes could not be generated.';
    }

    downloadingBulk.value = false;
};

const getInitials = (name) => {
    if (!name) return '?';
    const parts = String(name).trim().split(/\s+/).filter(Boolean);
    return parts.slice(0, 2).map((part) => part[0].toUpperCase()).join('');
};

const displayValue = (value) => {
    if (value === null || value === undefined) return '--';
    const text = String(value).trim();
    return text === '' ? '--' : text;
};

const formatDateTime = (value) => {
    if (!value) return '--';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return String(value);
    }
    return date.toLocaleString();
};
</script>

<template>
    <Head title="Attendance" />

    <div class="min-h-screen bg-gray-950 text-gray-100 font-sans p-6 relative overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-[20%] -right-[10%] w-[50rem] h-[50rem] bg-purple-900/10 rounded-full mix-blend-screen filter blur-[100px]"></div>
            <div class="absolute bottom-[10%] -left-[10%] w-[40rem] h-[40rem] bg-blue-900/10 rounded-full mix-blend-screen filter blur-[100px]"></div>
        </div>

        <div class="max-w-7xl mx-auto relative z-10">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div class="flex items-center gap-4">
                    <Link
                        href="/dashboard"
                        class="btn btn-circle btn-ghost text-gray-400 hover:text-white hover:bg-white/5"
                    >
                        <ArrowLeftIcon class="w-5 h-5" />
                    </Link>
                    <div>
                        <h1 class="text-3xl font-bold text-white tracking-tight">Guest Attendance</h1>
                        <p class="text-gray-400 mt-1 text-sm">Review imported guests and seating details.</p>
                        <p v-if="qrError" class="text-sm text-red-400 mt-2">{{ qrError }}</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button
                        @click="downloadAllQRCodes"
                        :disabled="downloadingBulk || props.guests.length === 0"
                        class="btn btn-sm bg-purple-600 hover:bg-purple-500 text-white border-none shadow-lg shadow-purple-900/20 disabled:opacity-50"
                    >
                        <ArrowDownTrayIcon class="w-4 h-4 mr-2" />
                        {{ downloadingBulk ? 'Generating...' : 'Download All QR Codes' }}
                    </button>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card bg-gray-900/40 backdrop-blur-xl border border-white/5 shadow-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead class="bg-gray-800/50 text-gray-300 uppercase text-xs font-semibold tracking-wider">
                            <tr>
                                <th class="py-4 pl-6">Guest</th>
                                <th>Phone</th>
                                <th>Table</th>
                                <th>Hall</th>
                                <th>Status</th>
                                <th>QR Code</th>
                                <th class="text-right pr-6">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            <template v-if="props.guests.length === 0">
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-gray-500">No guests imported yet.</td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr v-for="guest in props.guests" :key="guest.id" class="hover:bg-white/5 transition-colors group">
                                    <td class="pl-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-purple-500/20 text-purple-200 text-sm font-semibold flex items-center justify-center">
                                                {{ getInitials(guest.name) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-white">{{ guest.name }}</div>
                                                <div class="text-xs text-gray-500">Table {{ displayValue(guest.table_name) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-gray-300 font-mono text-sm">{{ displayValue(guest.phone) }}</td>
                                    <td class="text-gray-300 text-sm">{{ displayValue(guest.table_name) }}</td>
                                    <td class="text-gray-300 text-sm">{{ displayValue(guest.hall) }}</td>
                                    <td class="py-4">
                                        <span
                                            v-if="guest.attendance"
                                            class="badge badge-success badge-sm gap-1"
                                        >
                                            <CheckCircleIcon class="w-3 h-3" />
                                            Checked In
                                        </span>
                                        <span
                                            v-else
                                            class="badge badge-ghost badge-sm"
                                        >
                                            Pending
                                        </span>
                                    </td>
                                    <td class="py-4">
                                        <div class="flex items-center gap-2">
                                            <button
                                                @click="downloadQRCode(guest)"
                                                class="btn btn-square btn-sm btn-ghost text-purple-400 hover:bg-purple-500/10"
                                                title="Download QR Code"
                                            >
                                                <ArrowDownTrayIcon class="w-4 h-4" />
                                            </button>
                                            <button
                                                @click="openQRModal(guest)"
                                                class="btn btn-square btn-sm btn-ghost text-gray-400 hover:bg-purple-500/10"
                                                title="View QR Code"
                                            >
                                                <QrCodeIcon class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                    <td class="text-right pr-6">
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                @click="openEditModal(guest)"
                                                class="btn btn-square btn-sm btn-ghost text-blue-400 hover:bg-blue-500/10"
                                                title="Edit Guest"
                                            >
                                                <PencilIcon class="w-5 h-5" />
                                            </button>
                                            <button
                                                @click="openModal(guest)"
                                                class="btn btn-square btn-sm btn-ghost text-gray-400 hover:text-purple-400 hover:bg-purple-500/10"
                                            >
                                                <EyeIcon class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Details Modal -->
        <dialog class="modal modal-bottom sm:modal-middle backdrop-blur-sm" :class="{ 'modal-open': isModalOpen }">
            <div class="modal-box bg-gray-900 border border-white/10 shadow-2xl p-0 overflow-hidden max-w-lg" v-if="selectedGuest">
                <!-- Modal Header -->
                <div class="p-6 border-b border-white/5 flex justify-between items-center bg-gradient-to-r from-gray-800/50 to-transparent">
                    <h3 class="font-bold text-lg text-white flex items-center gap-2">
                        Guest Details
                        <span class="badge badge-outline text-xs font-normal text-gray-400">{{ selectedGuest.id }}</span>
                    </h3>
                    <button @click="closeModal" class="btn btn-sm btn-circle btn-ghost text-gray-400 hover:text-white">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-6">
                    <!-- Guest Profile -->
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full ring ring-purple-500/30 ring-offset-base-100 ring-offset-2 bg-purple-500/20 text-white text-xl font-semibold flex items-center justify-center">
                            {{ getInitials(selectedGuest.name) }}
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-white">{{ selectedGuest.name }}</h4>
                            <p class="text-sm text-gray-400">
                                Table {{ displayValue(selectedGuest.table_name) }} | {{ displayValue(selectedGuest.hall) }}
                            </p>
                        </div>
                    </div>

                    <!-- Stats Grid -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-800/50 p-4 rounded-xl border border-white/5">
                            <div class="text-xs text-gray-400 mb-1">Table</div>
                            <div class="text-white font-medium">{{ displayValue(selectedGuest.table_name) }}</div>
                        </div>
                        <div class="bg-gray-800/50 p-4 rounded-xl border border-white/5">
                            <div class="text-xs text-gray-400 mb-1">Hall</div>
                            <div class="text-white font-medium">{{ displayValue(selectedGuest.hall) }}</div>
                        </div>
                    </div>

                    <!-- Meta Info -->
                    <div class="space-y-3 text-sm">
                        <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-white/5 transition-colors">
                            <DevicePhoneMobileIcon class="w-5 h-5 text-blue-400 mt-0.5" />
                            <div>
                                <div class="text-gray-300 font-medium">Phone</div>
                                <div class="text-gray-500">{{ displayValue(selectedGuest.phone) }}</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-white/5 transition-colors">
                            <CalendarIcon class="w-5 h-5 text-purple-400 mt-0.5" />
                            <div>
                                <div class="text-gray-300 font-medium">Imported At</div>
                                <div class="text-gray-500">{{ formatDateTime(selectedGuest.created_at) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 bg-gray-800/30 border-t border-white/5 flex justify-end">
                    <button @click="closeModal" class="btn btn-ghost btn-sm text-gray-400 hover:text-white">Close</button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button @click="closeModal">close</button>
            </form>
        </dialog>

        <!-- Edit Modal -->
        <dialog class="modal modal-bottom sm:modal-middle backdrop-blur-sm" :class="{ 'modal-open': isEditModalOpen }">
            <div class="modal-box bg-gray-900 border border-white/10 shadow-2xl" v-if="editingGuest">
                <h3 class="font-bold text-lg text-white mb-6">Edit Guest Details</h3>

                <form @submit.prevent="saveGuest" class="space-y-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text text-gray-300">Name</span>
                        </label>
                        <input
                            v-model="editForm.name"
                            type="text"
                            class="input input-bordered bg-gray-800 text-white"
                            required
                        />
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text text-gray-300">Phone</span>
                        </label>
                        <input
                            v-model="editForm.phone"
                            type="text"
                            class="input input-bordered bg-gray-800 text-white"
                        />
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text text-gray-300">Table</span>
                        </label>
                        <input
                            v-model="editForm.table_name"
                            type="text"
                            class="input input-bordered bg-gray-800 text-white"
                        />
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text text-gray-300">Hall</span>
                        </label>
                        <input
                            v-model="editForm.hall"
                            type="text"
                            class="input input-bordered bg-gray-800 text-white"
                        />
                    </div>

                    <div class="modal-action">
                        <button type="button" @click="closeEditModal" class="btn btn-ghost">Cancel</button>
                        <button type="submit" class="btn bg-purple-600 hover:bg-purple-500 text-white">Save Changes</button>
                    </div>
                </form>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button @click="closeEditModal">close</button>
            </form>
        </dialog>

        <!-- QR Code Modal -->
        <dialog class="modal modal-bottom sm:modal-middle backdrop-blur-sm" :class="{ 'modal-open': isQRModalOpen }">
            <div class="modal-box bg-gray-900 border border-white/10 shadow-2xl text-center" v-if="selectedGuest">
                <h3 class="font-bold text-lg text-white mb-4">{{ selectedGuest.name }}</h3>
                <p class="text-sm text-gray-400 mb-6">Table {{ displayValue(selectedGuest.table_name) }} | {{ displayValue(selectedGuest.hall) }}</p>

                <div class="flex justify-center mb-6">
                    <div class="p-4 bg-white rounded-xl">
                        <img :src="qrDataUrl" alt="QR Code" class="w-64 h-64" />
                    </div>
                </div>

                <div class="modal-action justify-center">
                    <button @click="downloadQRCode(selectedGuest)" class="btn bg-purple-600 hover:bg-purple-500 text-white">
                        <ArrowDownTrayIcon class="w-4 h-4 mr-2" />
                        Download
                    </button>
                    <button @click="closeQRModal" class="btn btn-ghost">Close</button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button @click="closeQRModal">close</button>
            </form>
        </dialog>
    </div>
</template>
