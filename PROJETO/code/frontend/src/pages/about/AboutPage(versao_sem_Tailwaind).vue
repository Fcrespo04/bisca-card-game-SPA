<script setup>
    import {ref, reactive, onMounted, inject } from "vue"

    const socket = inject('socket')

    const MsgToSend = ref("Hello from Vue via WebSocket!")
    const receivedMessage = ref("")

    const hello = ref("Hello World") // inicializa a variável hello com "Hello World"
    
    //const counter = reactive({value:0})
    const counter = ref(0) // inicializa o counter a 0

    // ambas as linhas acima funcionam, mas a segunda é mais simples

    // variáveis só precisam de ser reativas se forem usadas dentro do template
    // se forem usadas só dentro do script, podem ser variáveis normais JavaScript
    // ex: const counter = 0

    // no código a variável counter é um objeto reativo, não é um número
    // para acessar o valor do counter, deve-se usar counter.value
    const increase = () => {
        counter.value++
    }
    const decrease = ()=>{
        counter.value--
    }

    // CONECT TO LARAVEL API
    const apiVersion = ref("Loading...") 
    const apiName = ref("Loading...")

    // fetch metadata from Laravel API
    const fetchMetadata = async () => {
    try {
        const response = await fetch('http://127.0.0.1:8000/api/metadata', {
            method: 'GET',
            headers: {
            'Accept': 'application/json'
            }
        })
        const data = await response.json()
        
        apiVersion.value = data.version || "Version not found"
        apiName.value = data.name || "Name not found"
        
    } catch (error) {
        console.error("Error fetching metadata:", error)
        apiVersion.value = "Error loading version"
        apiName.value = "Error loading name"
    }
    }

    const SendMessage = () => {
        socket.emit('echo', MsgToSend.value)
    }

    socket.on('echo', (msg) => {
        receivedMessage.value = msg
    })
    
    // call the API when the component loads
    onMounted(fetchMetadata)

</script>

<template>
  <div>
    <!-- variáveis reativas são declaras assim: {{ nome_var }} -->
    <h1>{{ hello }}</h1>
    <p><strong>API Version: {{ apiVersion }}</strong></p>
    <p><strong>API Name: {{ apiName }}</strong></p>
    <p>Counter: {{ counter }}</p>
    


    <!-- Button using v-on:click -->
    <!-- v-on: tudo o que está entre " " pode se usar código JavaScript diretamente -->
    <button v-on:click="increase">Increase</button> <!-- só se coloca o nome da função -->

    <!-- Button using shorthand @click, tudo o que está entre " " pode se usar código JavaScript diretamente -->
    <button @click="decrease">Decrease</button>

    <!-- Não pode haver variáveis e funções com o mesmo nome -->

    <!-- @click e v-on:click são equivalentes -->
     <h3>WebSockets Test</h3>
     <p>Connected: {{ socket.connected ? 'Yes' : 'No' }}</p>
     <p>Mensage: <input type="text" v-model="MsgToSend"><button @click="SendMessage">Send</button></p>
     <p>Receive: {{receivedMessage}}</p>
  </div>
</template>

