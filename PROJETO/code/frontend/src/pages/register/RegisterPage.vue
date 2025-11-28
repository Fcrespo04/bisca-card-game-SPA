<template>
    <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900">
                    Create your account
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Join the Bisca platform and get 10 bonus coins
                </p>
            </div>

            <form class="mt-8 space-y-6" @submit.prevent="handleRegister">
                <div class="space-y-4 rounded-md shadow-sm">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Name
                        </label>
                        <Input id="name" v-model="formData.name" type="text" autocomplete="name" required
                            placeholder="John Doe" />
                    </div>

                    <div>
                        <label for="nickname" class="block text-sm font-medium text-gray-700 mb-1">
                            Nickname
                        </label>
                        <Input id="nickname" v-model="formData.nickname" type="text" required placeholder="BiscaMaster" />
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email address
                        </label>
                        <Input id="email" v-model="formData.email" type="email" autocomplete="email" required
                            placeholder="you@example.com" />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Password
                        </label>
                        <Input id="password" v-model="formData.password" type="password" autocomplete="new-password"
                            required placeholder="••••••••" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                            Confirm Password
                        </label>
                        <Input id="password_confirmation" v-model="formData.password_confirmation" type="password"
                            autocomplete="new-password" required placeholder="••••••••" />
                    </div>
                </div>

                <div>
                    <Button type="submit" class="w-full" :disabled="isLoading">
                        {{ isLoading ? 'Creating account...' : 'Sign up' }}
                    </Button>
                </div>

                <div class="text-center text-sm">
                    <span class="text-gray-600">Already have an account? </span>
                    <RouterLink to="/login" class="font-medium text-blue-600 hover:text-blue-500">
                        Sign in
                    </RouterLink>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { useAuthStore } from '@/stores/auth'
import { useRouter, RouterLink } from 'vue-router'
import { toast } from 'vue-sonner'

const authStore = useAuthStore()
const router = useRouter()
const isLoading = ref(false)

const formData = ref({
    name: '',
    nickname: '',
    email: '',
    password: '',
    password_confirmation: ''
})

const handleRegister = async () => {
    isLoading.value = true
    console.log("Sending Form Data:", formData.value)
    try {
        await authStore.register(formData.value)
        toast.success('Account created! Welcome to Bisca.')
        router.push('/')
    } catch (error) {
        const msg = error.response?.data?.message || 'Registration failed'
        toast.error(`Error: ${msg}`)
    } finally {
        isLoading.value = false
    }
}
</script>