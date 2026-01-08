<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Admin Login" />

    <div class="min-h-screen bg-black flex items-center justify-center p-4 relative">
        <!-- Ambient Background -->
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
        <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-purple-900/40 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-1/4 right-1/4 w-64 h-64 bg-blue-900/40 rounded-full blur-[100px]"></div>

        <div class="card w-full max-w-sm shadow-2xl bg-gray-900/60 backdrop-blur-xl border border-white/10 z-10">
            <div class="card-body">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-white tracking-widest uppercase">Command Center</h2>
                    <p class="text-xs text-gray-500 mt-1">VVIP Wedding System</p>
                </div>

                <form @submit.prevent="submit">
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text text-gray-400">Email Access</span>
                        </label>
                        <input 
                            type="email" 
                            v-model="form.email"
                            required
                            class="input input-bordered bg-black/50 border-gray-700 text-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-all" 
                            placeholder="admin@vvip.com" 
                        />
                        <div v-if="form.errors.email" class="text-error text-xs mt-1">{{ form.errors.email }}</div>
                    </div>

                    <div class="form-control w-full mt-4">
                        <label class="label">
                            <span class="label-text text-gray-400">Passcode</span>
                        </label>
                        <input 
                            type="password" 
                            v-model="form.password"
                            required
                            class="input input-bordered bg-black/50 border-gray-700 text-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-all" 
                            placeholder="••••••••" 
                        />
                        <div v-if="form.errors.password" class="text-error text-xs mt-1">{{ form.errors.password }}</div>
                    </div>

                    <div class="form-control mt-8">
                        <button class="btn btn-primary bg-gradient-to-r from-purple-600 to-blue-600 border-none text-white hover:scale-105 transition-transform" :disabled="form.processing">
                            <span v-if="form.processing" class="loading loading-spinner"></span>
                            Authenticate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>