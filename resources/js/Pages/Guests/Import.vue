<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    importResult: {
        type: Object,
        default: null,
    },
});

const form = useForm({
    file: null,
});

const onFileChange = (event) => {
    form.file = event.target.files[0];
};

const submit = () => {
    form.post('/guests/import', {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Import Guests" />

    <div class="min-h-screen bg-gray-950 text-white font-sans relative overflow-hidden">
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-[-10%] left-[-10%] w-[40rem] h-[40rem] bg-emerald-900/20 rounded-full mix-blend-screen filter blur-[100px] animate-blob"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[40rem] h-[40rem] bg-blue-900/20 rounded-full mix-blend-screen filter blur-[100px] animate-blob animation-delay-2000"></div>
        </div>

        <nav class="relative z-20 border-b border-white/10 bg-gray-900/50 backdrop-blur-md">
            <div class="container mx-auto px-6 py-4 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500/20 to-blue-500/20 border border-white/10 flex items-center justify-center shadow-inner">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-white">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-6L12 6m0 0l4.5 4.5M12 6v12" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-wide text-white">VVIP WEDDING</h1>
                        <p class="text-[0.65rem] uppercase tracking-[0.2em] text-gray-400">Import Console</p>
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
                <h2 class="text-3xl font-light text-white mb-2">Import Guests</h2>
                <p class="text-gray-400">Upload a CSV list and we will add the guests to the database.</p>

                <div v-if="props.importResult" class="mt-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-200">
                    Imported {{ props.importResult.imported }} rows. Skipped {{ props.importResult.skipped }} rows with empty names.
                </div>

                <div class="mt-8 rounded-3xl bg-gray-900/40 border border-white/10 p-8">
                    <form class="space-y-6" @submit.prevent="submit">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">CSV File</label>
                            <input
                                type="file"
                                accept=".csv,text/csv"
                                class="file-input file-input-bordered w-full bg-gray-950/60 border-white/10"
                                @change="onFileChange"
                            />
                            <p v-if="form.errors.file" class="text-sm text-red-400 mt-2">
                                {{ form.errors.file }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-gray-950/50 p-4 text-sm text-gray-400">
                            <p class="font-semibold text-gray-300 mb-2">Required columns</p>
                            <div class="grid grid-cols-2 gap-2">
                                <span class="px-2 py-1 rounded-lg bg-white/5">name</span>
                                <span class="px-2 py-1 rounded-lg bg-white/5">phone</span>
                                <span class="px-2 py-1 rounded-lg bg-white/5">table</span>
                                <span class="px-2 py-1 rounded-lg bg-white/5">hall</span>
                            </div>
                            <p class="mt-3">Tip: In Excel, use File &gt; Save As &gt; CSV (UTF-8).</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <button
                                type="submit"
                                class="btn btn-primary"
                                :disabled="form.processing"
                            >
                                Import CSV
                            </button>
                            <span v-if="form.processing" class="text-sm text-gray-400">Importing...</span>
                        </div>
                    </form>
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
