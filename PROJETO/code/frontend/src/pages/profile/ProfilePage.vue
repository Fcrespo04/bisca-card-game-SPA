<template>
    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-3xl font-bold mb-8">My Profile</h1>

        <div v-if="authStore.currentUser" class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Profile Photo</CardTitle>
                    <CardDescription>Update your avatar</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-col sm:flex-row items-start gap-6">
                        <div class="flex-shrink-0">
                            <Avatar class="w-32 h-32">
                                <AvatarImage v-if="authStore.currentUser.photo_avatar_filename"
                                    :src="`${serverBaseURL}/storage/photos_avatars/${authStore.currentUser.photo_avatar_filename}`"
                                    :alt="authStore.currentUser.name" />
                                <AvatarFallback class="text-4xl">
                                    {{ authStore.currentUser.name?.charAt(0).toUpperCase() }}
                                </AvatarFallback>
                            </Avatar>
                        </div>

                        <div class="flex-1 space-y-3">
                            <div class="flex flex-wrap gap-2">
                                <Button @click="open" variant="outline">
                                    Choose Photo
                                </Button>
                                <Button v-if="files" @click="uploadPhoto">Save Photo</Button>
                                <Button v-if="files" @click="reset" variant="ghost">
                                    Cancel
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Account Information</CardTitle>
                    <CardDescription>Update your personal details</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-md flex items-center gap-2 text-yellow-800">
                        <span class="text-xl">💰</span>
                        <span class="font-semibold">Balance:</span>
                        <span>{{ authStore.currentUser.coins_balance }} Brain Coins</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="formData.name" placeholder="Enter your name" />
                        </div>
                        
                        <div class="space-y-2">
                            <Label for="nickname">Nickname</Label>
                            <Input id="nickname" v-model="formData.nickname" placeholder="Your game nickname" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input id="email" v-model="formData.email" type="email" placeholder="Enter your email" />
                    </div>
                </CardContent>
                <CardFooter class="flex justify-between">
                    <Button @click="saveProfile">Save Changes</Button>
                </CardFooter>
            </Card>

            <Card class="border-red-200" v-if="authStore.currentUser.type !== 'A'">
                <CardHeader>
                    <CardTitle class="text-red-600">Delete Account</CardTitle>
                    <CardDescription>
                        Permanently remove your account and forfeit all coins.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-500">This action cannot be undone.</p>
                        <Button variant="destructive" @click="showDeleteConfirmation = true">
                            Delete Account
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div v-if="showDeleteConfirmation" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
            <Card class="w-full max-w-md bg-white">
                <CardHeader>
                    <CardTitle>Confirm Deletion</CardTitle>
                    <CardDescription>Please enter your password to confirm.</CardDescription>
                </CardHeader>
                <CardContent>
                    <Input type="password" v-model="deletePassword" placeholder="Your Password" />
                </CardContent>
                <CardFooter class="flex justify-end gap-2">
                    <Button variant="outline" @click="showDeleteConfirmation = false">Cancel</Button>
                    <Button variant="destructive" @click="confirmDelete">Confirm Delete</Button>
                </CardFooter>
            </Card>
        </div>
    </div>
</template>

<script setup>
import { ref, inject, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useFileDialog } from '@vueuse/core'
import { toast } from 'vue-sonner'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'

const authStore = useAuthStore()
const router = useRouter()
const serverBaseURL = inject("serverBaseURL")

// --- State ---
const formData = ref({
    name: '',
    nickname: '', // Added nickname
    email: ''
})

const showDeleteConfirmation = ref(false)
const deletePassword = ref('')

// --- Watcher ---
// Sync form data when user loads or updates
watch(() => authStore.currentUser, (user) => {
    if (user) {
        formData.value = {
            name: user.name || '',
            nickname: user.nickname || '', // Map nickname
            email: user.email || ''
        }
    }
}, { immediate: true })

// --- File Upload ---
const { files, open, reset } = useFileDialog({
    accept: 'image/*',
    multiple: false
})

const uploadPhoto = async () => {
    if (!files.value || files.value.length === 0) return
    
    try {
        await authStore.updateUserPhoto(files.value[0])
        
        toast.success("Profile photo updated successfully")
        reset()
    } catch (error) {
        console.error('Failed to upload photo:', error)
        toast.error("Failed to upload photo.")
    }
}

// --- Save Profile ---
const saveProfile = async () => {
    try {
        // Use the authStore action we created earlier
        const userToUpdate = {
            ...formData.value,
            id: authStore.currentUser.id 
        }

        await authStore.updateUser(userToUpdate)
        toast.success("Profile updated successfully")
    } catch (error) {
        console.error('Failed to update profile:', error)
        // Show validation errors from API if available
        const msg = error.response?.data?.message || "Failed to update profile."
        toast.error(msg)
    }
}

// --- Delete Account ---
const confirmDelete = async () => {
    try {
        await authStore.deleteAccount(deletePassword.value)
        toast.success('Account deleted successfully.')
        router.push({ name: 'login' })
    } catch (error) {
        const msg = error.response?.data?.message || 'Deletion failed'
        toast.error(msg)
    } finally {
        showDeleteConfirmation.value = false
        deletePassword.value = ''
    }
}
</script>