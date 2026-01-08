<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Admin Login" />

    <div class="min-h-screen bg-gray-950 flex items-center justify-center p-4 relative overflow-hidden font-sans">
        <!-- Background Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-[20%] -left-[10%] w-[50rem] h-[50rem] bg-purple-900/20 rounded-full mix-blend-screen filter blur-[100px] animate-blob"></div>
            <div class="absolute top-[20%] -right-[10%] w-[40rem] h-[40rem] bg-blue-900/20 rounded-full mix-blend-screen filter blur-[100px] animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-[20%] left-[20%] w-[45rem] h-[45rem] bg-indigo-900/20 rounded-full mix-blend-screen filter blur-[100px] animate-blob animation-delay-4000"></div>
        </div>

        <!-- Login Card -->
        <div class="card w-full max-w-md bg-gray-900/40 backdrop-blur-xl border border-white/10 shadow-2xl z-10 relative overflow-hidden group">
            <!-- Decorative top border -->
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-purple-500 to-transparent opacity-50"></div>
            
            <div class="card-body p-8 sm:p-10">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-purple-500/20 to-blue-500/20 border border-white/5 mb-4 shadow-inner">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-white">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold text-white tracking-tight">Welcome Back</h2>
                    <p class="text-sm text-gray-400 mt-2">Sign in to access the VVIP Dashboard</p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div class="form-control">
                        <label class="label pl-1">
                            <span class="label-text text-gray-300 text-xs uppercase tracking-wider font-semibold">Email Address</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 group-focus-within:text-purple-400 transition-colors">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                            </div>
                            <input 
                                type="email" 
                                v-model="form.email"
                                required
                                class="input input-bordered w-full bg-black/20 border-gray-700 text-white pl-10 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-all duration-300 placeholder-gray-600" 
                                placeholder="admin@example.com" 
                            />
                        </div>
                        <div v-if="form.errors.email" class="text-error text-xs mt-1 pl-1">{{ form.errors.email }}</div>
                    </div>

                    <div class="form-control">
                        <label class="label pl-1">
                            <span class="label-text text-gray-300 text-xs uppercase tracking-wider font-semibold">Password</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 group-focus-within:text-purple-400 transition-colors">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                </svg>
                            </div>
                            <input 
                                type="password" 
                                v-model="form.password"
                                required
                                class="input input-bordered w-full bg-black/20 border-gray-700 text-white pl-10 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-all duration-300 placeholder-gray-600" 
                                placeholder="••••••••" 
                            />
                        </div>
                        <div v-if="form.errors.password" class="text-error text-xs mt-1 pl-1">{{ form.errors.password }}</div>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center cursor-pointer group">
                            <input type="checkbox" v-model="form.remember" class="checkbox checkbox-xs checkbox-primary rounded-sm border-gray-600 group-hover:border-purple-500 transition-colors" />
                            <span class="ml-2 text-gray-400 group-hover:text-gray-300 transition-colors">Remember me</span>
                        </label>
                        <a href="#" class="text-purple-400 hover:text-purple-300 transition-colors font-medium">Forgot Password?</a>
                    </div>

                    <button 
                        type="submit" 
                        class="btn btn-primary w-full bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-500 hover:to-blue-500 border-none text-white font-bold tracking-wide shadow-lg shadow-purple-900/20 hover:shadow-purple-900/40 transition-all duration-300 hover:scale-[1.02]" 
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="loading loading-spinner loading-sm"></span>
                        <span v-else>Sign In</span>
                    </button>
                </form>
            </div>
            
            <!-- Decorative bottom glow -->
            <div class="absolute bottom-0 left-0 w-full h-24 bg-gradient-to-t from-purple-900/10 to-transparent pointer-events-none"></div>
        </div>
        
        <div class="absolute bottom-6 text-center">
            <p class="text-gray-600 text-xs">© 2026 VVIP Wedding System. Secure Access Only.</p>
        </div>
    </div>
</template>

<style scoped>
.animate-blob {
    animation: blob 10s infinite;
}
.animation-delay-2000 {
    animation-delay: 2s;
}
.animation-delay-4000 {
    animation-delay: 4s;
}
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}
</style>