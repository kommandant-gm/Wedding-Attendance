<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Html5Qrcode } from 'html5-qrcode';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';

// State
const scanning = ref(true);
const loading = ref(false);
const scanner = ref(null);
const guest = ref(null);
const errorMsg = ref('');

// Audio for success beep
const successAudio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');

const onScanSuccess = async (decodedText, decodedResult) => {
    if (loading.value || !scanning.value) return;
    
    // Pause scanning
    scanning.value = false;
    loading.value = true;
    
    try {
        // Stop the camera temporarily
        if (scanner.value) {
            await scanner.value.pause();
        }

        // Play beep
        successAudio.play().catch(e => console.log('Audio play failed', e));

        // Call Backend to check-in
        // Assuming the QR code contains a unique Guest ID or Token
        const response = await axios.post('/api/attendance/check-in', {
            token: decodedText
        });

        guest.value = response.data.guest; // Expecting { name: '...', table: '...' }
        
        // Open the modal
        document.getElementById('guest_modal').showModal();

    } catch (error) {
        console.error(error);
        errorMsg.value = "Invalid QR Code or Guest not found.";
        scanning.value = true;
        if(scanner.value) scanner.value.resume();
    } finally {
        loading.value = false;
    }
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
            <div class="modal-box bg-gray-900 border border-white/10 shadow-2xl text-center relative overflow-hidden">
                <!-- Glow effect inside modal -->
                <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-purple-500 via-yellow-500 to-purple-500"></div>
                
                <div v-if="guest" class="py-6">
                    <div class="w-20 h-20 mx-auto bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center mb-4 shadow-lg shadow-yellow-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-black">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    
                    <h3 class="font-bold text-2xl text-white mb-1">Welcome!</h3>
                    <p class="text-yellow-500 text-xl font-light tracking-wide mb-6">{{ guest.name }}</p>
                    
                    <div class="stats shadow bg-white/5 border border-white/5">
                        <div class="stat place-items-center">
                            <div class="stat-title text-gray-400">Seated At</div>
                            <div class="stat-value text-white text-3xl font-serif">Table {{ guest.table }}</div>
                            <div class="stat-desc text-yellow-500/80">VIP Section</div>
                        </div>
                    </div>
                </div>

                <div class="modal-action justify-center mt-8">
                    <form method="dialog">
                        <button @click="resetScanner" class="btn btn-wide bg-yellow-600 hover:bg-yellow-500 text-black border-none font-bold tracking-wider">
                            SCAN NEXT
                        </button>
                    </form>
                </div>
            </div>
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