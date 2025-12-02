<template>
  <div class="max-w-4xl mx-auto p-6 space-y-8">
    <div class="flex justify-between items-center">
      <h1 class="text-3xl font-bold">My Wallet</h1>
      <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg flex items-center gap-3">
        <span class="text-2xl">💰</span>
        <div>
          <p class="text-sm text-yellow-800 uppercase font-semibold">Current Balance</p>
          <p class="text-2xl font-bold text-yellow-900">{{ authStore.currentUser?.coins_balance ?? 0 }} Coins</p>
        </div>
      </div>
    </div>

    <Card class="border-blue-100 bg-blue-50/50">
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <span>🛒</span> Buy Coins
        </CardTitle>
        <CardDescription>1 Euro = 10 Coins. Instant delivery.</CardDescription>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="handlePurchase" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <div class="space-y-2 relative">
              <div class="flex items-center gap-2">
                <Label>Payment Method</Label>
                
                <div class="relative group cursor-pointer">
                  <span class="text-blue-600 text-lg hover:text-blue-800">🛈</span>
                  
                  <div class="absolute z-10 hidden group-hover:block w-64 p-4 bg-white border border-gray-200 rounded-lg shadow-xl -left-10 top-6 text-sm text-gray-600">
                    <h4 class="font-semibold text-gray-900 mb-2">Payment Limits (Simulation)</h4>
                    <ul class="space-y-1">
                      <li class="flex justify-between"><span>MBWAY:</span> <span class="font-mono">Max €5</span></li>
                      <li class="flex justify-between"><span>PAYPAL:</span> <span class="font-mono">Max €10</span></li>
                      <li class="flex justify-between"><span>MB:</span> <span class="font-mono">Max €20</span></li>
                      <li class="flex justify-between"><span>VISA:</span> <span class="font-mono">Max €30</span></li>
                      <li class="flex justify-between"><span>IBAN:</span> <span class="font-mono">Max €50</span></li>
                    </ul>
                  </div>
                </div>
              </div>

              <Select v-model="paymentType">
                <SelectTrigger>
                  <SelectValue placeholder="Select Method" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="MBWAY">MBWay</SelectItem>
                  <SelectItem value="PAYPAL">PayPal</SelectItem>               
                  <SelectItem value="MB">Multibanco</SelectItem>
                  <SelectItem value="VISA">Visa</SelectItem>
                  <SelectItem value="IBAN">IBAN Transfer</SelectItem>
                </SelectContent>
              </Select>
            </div>
            
            <div class="space-y-2">
              <Label>Reference / Number</Label>
              <Input v-model="reference" :placeholder="referencePlaceholder" required />
            </div>
          </div>

          <div class="space-y-2">
            <Label>Amount (€)</Label>
            <div class="flex items-center gap-4">
              <Input type="number" min="1" max="99" v-model="euros" class="w-32" />
              <span class="text-lg font-medium text-yellow-600">
                = {{ euros * 10 }} Coins
              </span>
            </div>
          </div>

          <Button type="submit" :disabled="isBuying" class="w-full md:w-auto">
            {{ isBuying ? 'Processing...' : 'Confirm Purchase' }}
          </Button>
        </form>
      </CardContent>
    </Card>

    <Card>
      <CardHeader>
        <CardTitle>Transaction History</CardTitle>
        <CardDescription>Your recent activity</CardDescription>
      </CardHeader>
      <CardContent>
        <div v-if="isLoadingHistory" class="text-center py-4 text-gray-500">Loading history...</div>
        
        <div v-else-if="transactions.length === 0" class="text-center py-8 text-gray-500">
          No transactions found.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm text-left">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
              <tr>
                <th class="px-4 py-3">Date</th>
                <th class="px-4 py-3">Type</th>
                <th class="px-4 py-3">Amount</th>
                <th class="px-4 py-3 text-right">Details</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in transactions" :key="t.id" class="border-b hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-600">{{ t.datetime }}</td>
                <td class="px-4 py-3 font-medium">{{ t.type }}</td>
                <td class="px-4 py-3">
                  <span :class="t.coins > 0 ? 'text-yellow-600 font-bold' : 'text-red-600 font-bold'">
                    {{ t.coins > 0 ? '+' : '' }}{{ t.coins }}
                  </span>
                </td>
                <td class="px-4 py-3 text-right text-gray-500">
                  <span v-if="t.purchase">
                    {{ t.purchase.payment_type }} ({{ t.purchase.euros }}€)
                  </span>
                  <span v-else-if="t.custom && t.custom.deck_name">
                     Bought <strong>{{ t.custom.deck_name }}</strong>
                  </span>
                  <span v-else>-</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { toast } from 'vue-sonner'
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const authStore = useAuthStore()

const paymentType = ref('MBWAY')
const reference = ref('')
const euros = ref(1)
const isBuying = ref(false)

// transaction history variables
const transactions = ref([])
const isLoadingHistory = ref(false)

const loadHistory = async () => {
  isLoadingHistory.value = true
  try {
    transactions.value = await authStore.fetchTransactions()
  } catch (error) {
    console.error('Failed to load history', error)
    toast.error('Could not load transaction history.')
  } finally {
    isLoadingHistory.value = false
  }
}

onMounted(() => {
  loadHistory()
})

// Reset reference when payment type changes
watch(paymentType, () => {
  reference.value = ''
})

const referencePlaceholder = computed(() => {
  switch (paymentType.value) {
    case 'MBWAY': return '9xxxxxxxxx (9 digits)'
    case 'PAYPAL': return 'email@example.com'
    case 'VISA': return '16 digits starting with 4'
    case 'IBAN': return 'PT50...'
    case 'MB': return '12345-123456789'
    default: return 'Payment Reference'
  }
})

const handlePurchase = async () => {
  isBuying.value = true
  try {
    await authStore.buyCoins({
      payment_type: paymentType.value,
      payment_reference: reference.value,
      euros: euros.value
    })
    toast.success(`Successfully bought ${euros.value * 10} coins!`)
    
    // reset form
    euros.value = 1
    reference.value = ''

    // history reload so new purchase appears
    await loadHistory()

  } catch (error) {
    const data = error.response?.data
    
    if (data) {
      if (data.errors) {
        // Laravel Validation Error (e.g., "The payment reference format is invalid.")
        const firstError = Object.values(data.errors).flat()[0]
        toast.error(firstError)
      } else if (data.message) {
        // Controller Error (e.g., "Payment failed")
        toast.error(data.message + (data.error ? `: ${data.error}` : ''))
      } else {
        toast.error('Purchase failed. Please check your inputs.')
      }
    } else {
      toast.error('Server error. Please try again later.')
    }
  } finally {
    isBuying.value = false
  }
}
</script>