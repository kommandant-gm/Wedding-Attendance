<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Html5Qrcode } from 'html5-qrcode';
import axios from 'axios';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeftIcon, CheckCircleIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

// State
const scanning = ref(true);
const loading = ref(false);
const scanner = ref(null);
const guest = ref(null);
const errorMsg = ref('');
const alreadyCheckedIn = ref(false);

// Audio for success beep
const successAudio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');

const onScanSuccess = async (decodedText, decodedResult) => {
    if (loading.value || !scanning.value) return;

    // Pause scanning
    scanning.value = false;
    loading.value = true;
    errorMsg.value = '';

    try {
        // Stop the camera temporarily
        if (scanner.value) {
            await scanner.value.pause();
        }

        // Play beep
        successAudio.play().catch(e => console.log('Audio play failed', e));

        // Call Backend to check-in
        const response = await axios.post('/api/attendance/check-in', {
            token: decodedText
        });

        guest.value = response.data.guest;
        alreadyCheckedIn.value = false;

        // Open the modal
        document.getElementById('guest_modal').showModal();

    } catch (error) {
        console.error(error);

        if (error.response?.status === 409) {
            // Already checked in
            guest.value = error.response.data.guest;
            alreadyCheckedIn.value = true;
            document.getElementById('guest_modal').showModal();
        } else {
            errorMsg.value = error.response?.data?.error || "Invalid QR Code or Guest not found.";
            scanning.value = true;
            if(scanner.value) scanner.value.resume();
        }
    } finally {
        loading.value = false;
    }
};

const formatDateTime = (value) => {
    if (!value) return '--';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return String(value);
    }
    return date.toLocaleString();
};

const resetScanner = () => {
    guest.value = null;
    errorMsg.value = '';
    scanning.value = true;
    if (scanner.value) {
        scanner.value.resume();
    }
};

onMounted(() => {
    const config = { fps: 10, qrbox: { width: 250, height: 250 } };
    
    scanner.value = new Html5Qrcode("reader");
    
    scanner.value.start(
        { facingMode: "environment" }, 
        config, 
        onScanSuccess
    ).catch(err => {
        console.error("Error starting scanner", err);
        errorMsg.value = "Camera access denied or not available.";
    });
});

onUnmounted(() => {
    if (scanner.value) {
        scanner.value.stop().catch(err => console.error("Failed to stop scanner", err));
    }
});
</script>

<template>
    <Head title="VVIP Access" />

    <div class="min-h-screen bg-gray-900 text-white flex flex-col items-center justify-center relative overflow-hidden font-sans">
        <!-- Background Elements for Futuristic Feel -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
            <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-purple-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
            <div class="absolute top-[-10%] right-[-10%] w-96 h-96 bg-yellow-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-[-20%] left-[20%] w-96 h-96 bg-pink-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
        </div>

        <div class="z-10 w-full max-w-md px-6 flex flex-col items-center">
            <!-- Back Button -->
            <div class="w-full mb-4">
                <Link
                    href="/dashboard"
                    class="btn btn-circle btn-ghost text-gray-400 hover:text-white hover:bg-white/10"
                >
                    <ArrowLeftIcon class="w-6 h-6" />
                </Link>
            </div>

            <h1 class="text-3xl font-extralight tracking-[0.2em] text-center mb-2 text-transparent bg-clip-text bg-gradient-to-r from-yellow-200 to-yellow-500">
                VVIP WEDDING
            </h1>
            <p class="text-xs uppercase tracking-widest text-gray-400 mb-8">VVIP Guest Attendance</p>

            <!-- Scanner Container -->
            <div class="relative w-full aspect-square bg-black/50 backdrop-blur-sm rounded-3xl border border-white/10 shadow-2xl overflow-hidden group">
                <!-- Decorative Corners -->
                <div class="absolute top-4 left-4 w-8 h-8 border-t-2 border-l-2 border-yellow-500 rounded-tl-lg z-20"></div>
                <div class="absolute top-4 right-4 w-8 h-8 border-t-2 border-r-2 border-yellow-500 rounded-tr-lg z-20"></div>
                <div class="absolute bottom-4 left-4 w-8 h-8 border-b-2 border-l-2 border-yellow-500 rounded-bl-lg z-20"></div>
                <div class="absolute bottom-4 right-4 w-8 h-8 border-b-2 border-r-2 border-yellow-500 rounded-br-lg z-20"></div>

                <!-- Scanner Viewport -->
                <div id="reader" class="w-full h-full object-cover"></div>

                <!-- Scanning Line Animation -->
                <div v-if="scanning" class="absolute top-0 left-0 w-full h-1 bg-yellow-400 shadow-[0_0_15px_rgba(250,204,21,0.8)] animate-scan z-10"></div>
                
                <!-- Loading Overlay -->
                <div v-if="loading" class="absolute inset-0 bg-black/80 flex items-center justify-center z-30">
                    <span class="loading loading-infinity loading-lg text-yellow-500"></span>
                </div>
            </div>

            <p class="mt-6 text-sm text-gray-400 text-center">
                Align the QR code within the frame to check in.
            </p>
            
            <p v-if="errorMsg" class="mt-4 text-red-400 text-sm bg-red-900/20 px-4 py-2 rounded-lg border border-red-500/30">
                {{ errorMsg }}
            </p>
        </div>

        <!-- Result Modal -->
        <dialog id="guest_modal" class="modal modal-bottom sm:modal-middle backdrop-blur-sm">
            <div class="modal-box p-0 bg-gray-900/95 border border-white/10 shadow-2xl overflow-hidden max-w-sm w-full relative">
                <!-- Background Effects -->
                <div class="absolute inset-0 pointer-events-none overflow-hidden">
                    <div class="absolute -top-20 -right-20 w-64 h-64 bg-purple-600/20 rounded-full blur-3xl"></div>
                    <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-yellow-600/20 rounded-full blur-3xl"></div>
                </div>

                <div v-if="guest" class="relative z-10 flex flex-col items-center pt-10 pb-8 px-6">
                    
                    <!-- Status Icon with Pulse -->
                    <div class="relative mb-6">
                        <div class="absolute inset-0 rounded-full blur-xl opacity-50 animate-pulse" 
                             :class="alreadyCheckedIn ? 'bg-red-500' : 'bg-yellow-400'"></div>
                        <div class="relative w-24 h-24 rounded-full flex items-center justify-center border-4 shadow-2xl transition-all duration-500"
                             :class="alreadyCheckedIn ? 'bg-gray-900 border-red-500/50 text-red-500' : 'bg-gray-900 border-yellow-500/50 text-yellow-400'">
                            <CheckCircleIcon v-if="!alreadyCheckedIn" class="w-12 h-12" />
                            <ExclamationTriangleIcon v-else class="w-12 h-12" />
                        </div>
                    </div>

                    <!-- Text Content -->
                    <h3 class="text-3xl font-bold text-white mb-2 tracking-tight text-center">
                        {{ alreadyCheckedIn ? 'Already Here' : 'Welcome' }}
                    </h3>
                    
                    <div class="text-center mb-8 w-full">
                        <p class="text-xl text-gray-300 font-light truncate px-4">{{ guest.name }}</p>
                        <p v-if="alreadyCheckedIn" class="text-xs text-red-400 mt-2 bg-red-900/30 px-3 py-1 rounded-full inline-block border border-red-500/20">
                            Checked in: {{ formatDateTime(guest.checked_in_at) }}
                        </p>
                    </div>

                    <!-- Seat Ticket Card -->
                    <div class="w-full bg-white/5 border border-white/10 rounded-2xl p-6 relative overflow-hidden group hover:bg-white/10 transition-colors">
                        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
                        
                        <div class="flex justify-between items-end">
                            <div class="text-left">
                                <p class="text-[10px] uppercase tracking-[0.2em] text-gray-500 mb-1">Seating Table</p>
                                <p class="text-6xl font-serif text-white font-bold tracking-tighter leading-none">
                                    {{ guest.table || '--' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] uppercase tracking-[0.2em] text-gray-500 mb-1">Location</p>
                                <p class="text-lg text-yellow-500 font-medium">
                                    {{ guest.hall || 'Main Hall' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <form method="dialog" class="w-full mt-8">
                        <button
                            @click="resetScanner"
                            class="btn btn-lg w-full border-none font-bold tracking-widest text-black relative overflow-hidden group shadow-lg shadow-yellow-900/20"
                            :class="alreadyCheckedIn ? 'bg-gray-200 hover:bg-white' : 'bg-yellow-400 hover:bg-yellow-300'"
                        >
                            <span class="relative z-10">SCAN NEXT GUEST</span>
                        </button>
                    </form>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button @click="resetScanner">close</button>
            </form>
        </dialog>
    </div>
</template>

<style>
.animate-scan {
    animation: scan 2s linear infinite;
}
@keyframes scan {
    0% { top: 0%; opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { top: 100%; opacity: 0; }
}
.animate-blob {
    animation: blob 7s infinite;
}
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}
</style>