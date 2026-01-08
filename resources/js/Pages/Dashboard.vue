<script setup>
import { Link, Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <div class="min-h-screen bg-gray-950 text-white font-sans relative overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-[-10%] left-[-10%] w-[40rem] h-[40rem] bg-purple-900/20 rounded-full mix-blend-screen filter blur-[100px] animate-blob"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[40rem] h-[40rem] bg-blue-900/20 rounded-full mix-blend-screen filter blur-[100px] animate-blob animation-delay-2000"></div>
        </div>

        <!-- Navbar -->
        <nav class="relative z-20 border-b border-white/10 bg-gray-900/50 backdrop-blur-md">
            <div class="container mx-auto px-6 py-4 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500/20 to-blue-500/20 border border-white/10 flex items-center justify-center shadow-inner">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-white">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-wide text-white">VVIP WEDDING</h1>
                        <p class="text-[0.65rem] uppercase tracking-[0.2em] text-gray-400">Admin Console</p>
                    </div>
                </div>
                
                <Link href="/logout" method="post" as="button" class="btn btn-sm btn-ghost text-gray-400 hover:text-white hover:bg-white/5 font-light tracking-wider">
                    LOGOUT
                </Link>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="relative z-10 container mx-auto px-6 py-12">
            <div class="mb-10">
                <h2 class="text-3xl font-light text-white mb-2">Overview</h2>
                <p class="text-gray-400">Welcome back. Here is what's happening today.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Attendance Card -->
                <Link href="/attendance" class="group relative overflow-hidden rounded-3xl bg-gray-900/40 border border-white/10 p-8 hover:bg-gray-800/40 transition-all duration-500 hover:border-purple-500/30 hover:shadow-[0_0_30px_rgba(168,85,247,0.15)]">
                    <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity duration-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-24 h-24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    
                    <div class="relative z-10">
                        <div class="w-12 h-12 rounded-2xl bg-purple-500/20 flex items-center justify-center mb-6 text-purple-400 group-hover:scale-110 transition-transform duration-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Attendance List</h3>
                        <p class="text-sm text-gray-400 mb-6">Manage guest list, view check-in status, and seating arrangements.</p>
                        <span class="text-xs font-bold text-purple-400 uppercase tracking-widest flex items-center gap-2 group-hover:gap-3 transition-all">
                            Access Records <span class="text-lg leading-none">&rarr;</span>
                        </span>
                    </div>
                </Link>

                <!-- Reports Card -->
                <Link href="/reports" class="group relative overflow-hidden rounded-3xl bg-gray-900/40 border border-white/10 p-8 hover:bg-gray-800/40 transition-all duration-500 hover:border-blue-500/30 hover:shadow-[0_0_30px_rgba(59,130,246,0.15)]">
                    <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity duration-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-24 h-24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                    </div>
                    
                    <div class="relative z-10">
                        <div class="w-12 h-12 rounded-2xl bg-blue-500/20 flex items-center justify-center mb-6 text-blue-400 group-hover:scale-110 transition-transform duration-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Analytics & Reports</h3>
                        <p class="text-sm text-gray-400 mb-6">Real-time statistics, arrival timelines, and VIP summary reports.</p>
                        <span class="text-xs font-bold text-blue-400 uppercase tracking-widest flex items-center gap-2 group-hover:gap-3 transition-all">
                            View Analytics <span class="text-lg leading-none">&rarr;</span>
                        </span>
                    </div>
                </Link>

                <!-- Scan Kiosk Link (Optional but useful) -->
                <Link href="/scan" class="group relative overflow-hidden rounded-3xl bg-gray-900/40 border border-white/10 p-8 hover:bg-gray-800/40 transition-all duration-500 hover:border-yellow-500/30 hover:shadow-[0_0_30px_rgba(234,179,8,0.15)]">
                    <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity duration-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-24 h-24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75z" />
                        </svg>
                    </div>
                    
                    <div class="relative z-10">
                        <div class="w-12 h-12 rounded-2xl bg-yellow-500/20 flex items-center justify-center mb-6 text-yellow-400 group-hover:scale-110 transition-transform duration-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Scan Kiosk</h3>
                        <p class="text-sm text-gray-400 mb-6">Open the guest scanning interface for the reception team.</p>
                        <span class="text-xs font-bold text-yellow-400 uppercase tracking-widest flex items-center gap-2 group-hover:gap-3 transition-all">
                            Launch Scanner <span class="text-lg leading-none">&rarr;</span>
                        </span>
                    </div>
                </Link>
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