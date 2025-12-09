import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useGameStore = defineStore('game', () => {
  // --- Estado do Jogo ---
  const deck = ref([])
  const playerHand = ref([])
  const botHand = ref([])
  const table = ref({ player: null, opponent: null })
  const trumpCard = ref(null)

  const turn = ref('player')
  const score = ref({ player: 0, opponent: 0 }) // Pontos da rodada (0-120)

  // NOVO: Pontuação da Partida (Riscas) - Acumula entre rodadas
  const matchScore = ref({ player: 0, opponent: 0 })

  // Status:
  // 'playing'     = A jogar
  // 'resolving'   = Pausa para ver quem ganhou a vaza
  // 'ended'       = Fim da Rodada. Botão "Próxima Rodada".
  // 'match_ended' = Fim da Partida (4 riscas). Botão "Novo Jogo".
  const gameStatus = ref('idle')

  // CONFIGURAÇÃO
  const cardsPerHand = ref(3)

  // --- Regras da Bisca ---
  const suits = ['HEARTS', 'SPADES', 'DIAMONDS', 'CLUBS']
  const ranks = ['2', '3', '4', '5', '6', 'QUEEN', 'JACK', 'KING', '7', 'ACE']

  const values = {
    '2':0, '3':0, '4':0, '5':0, '6':0,
    'QUEEN':2, 'JACK':3, 'KING':4, '7':10, 'ACE':11
  }

  const hierarchy = {
    '2':1, '3':2, '4':3, '5':4, '6':5,
    'QUEEN':6, 'JACK':7, 'KING':8, '7':9, 'ACE':10
  }

  // --- AÇÕES ---

  // Função interna: Prepara o baralho para uma nova rodada (NÃO zera as riscas)
  const _setupRound = () => {
    gameStatus.value = 'playing'

    // 1. Criar Baralho
    deck.value = []
    suits.forEach(suit => {
      ranks.forEach(rank => {
        deck.value.push({
          suit,
          rank,
          points: values[rank],
          id: `${rank}-${suit}`
        })
      })
    })

    // 2. Baralhar
    deck.value.sort(() => Math.random() - 0.5)

    // 3. Definir Trunfo
    trumpCard.value = deck.value[deck.value.length - 1]

    // 4. Distribuir Cartas
    playerHand.value = deck.value.splice(0, cardsPerHand.value)
    botHand.value = deck.value.splice(0, cardsPerHand.value)

    // 5. Resetar Variáveis da Rodada (Pontos da mesa voltam a 0)
    score.value = { player: 0, opponent: 0 }
    table.value = { player: null, opponent: null }
    turn.value = 'player'
  }

  // Inicia uma PARTIDA nova (ZERA AS RISCAS) -> Botão "Novo Jogo"
  const initGame = () => {
    matchScore.value = { player: 0, opponent: 0 }
    _setupRound()
  }

  // Inicia a próxima rodada (MANTÉM AS RISCAS) -> Botão "Próxima Rodada"
  const nextRound = () => {
    _setupRound()
  }

  const playCard = (cardIndex) => {
    if (turn.value !== 'player' || gameStatus.value !== 'playing') return

    const card = playerHand.value.splice(cardIndex, 1)[0]
    table.value.player = card

    if (table.value.opponent) {
        // Vaza completa, resolver
        gameStatus.value = 'resolving'
        setTimeout(resolveTrick, 1500)
    } else {
        // Player puxou, vez do bot
        turn.value = 'opponent'
        setTimeout(botPlay, 1000)
    }
  }

  const botPlay = () => {
    if (gameStatus.value !== 'playing') return

    const randomIndex = Math.floor(Math.random() * botHand.value.length)
    const card = botHand.value.splice(randomIndex, 1)[0]
    table.value.opponent = card

    if (table.value.player) {
      setTimeout(resolveTrick, 1500)
    } else {
      turn.value = 'player'
    }
  }

  const resolveTrick = () => {
    const pCard = table.value.player
    const bCard = table.value.opponent

    if (!pCard || !bCard) {
        if (gameStatus.value === 'resolving') gameStatus.value = 'playing'
        return;
    }

    let winner = 'opponent'

    const pSuit = pCard.suit
    const bSuit = bCard.suit
    const tSuit = trumpCard.value.suit

    if (pSuit === bSuit) {
      if (hierarchy[pCard.rank] > hierarchy[bCard.rank]) winner = 'player'
    } else {
      if (pSuit === tSuit) {
        winner = 'player'
      } else if (bSuit === tSuit) {
        winner = 'opponent'
      } else {
        if (turn.value === 'opponent') winner = 'player'
        else winner = 'opponent'
      }
    }

    const points = pCard.points + bCard.points
    score.value[winner] += points

    table.value = { player: null, opponent: null }
    turn.value = winner

    if (gameStatus.value === 'resolving') {
        gameStatus.value = 'playing'
    }

    // Lógica de compra ou fim
    if (playerHand.value.length === 0 && deck.value.length === 0) {
      calculateMarksAndEndRound()
    } else if (deck.value.length > 0) {
      drawCards(winner)
    } else {
      if (winner === 'opponent') {
        setTimeout(botPlay, 1000)
      }
    }
  }

  const drawCards = (winner) => {
    if (winner === 'player') {
      if(deck.value.length) playerHand.value.push(deck.value.shift())
      if(deck.value.length) botHand.value.push(deck.value.shift())
    } else {
      if(deck.value.length) botHand.value.push(deck.value.shift())
      if(deck.value.length) playerHand.value.push(deck.value.shift())
      setTimeout(botPlay, 1000)
    }
  }

  // --- CÁLCULO DE RISCAS E FIM DE RODADA ---
  const calculateMarksAndEndRound = () => {
    const pPoints = score.value.player
    const oPoints = score.value.opponent

    let pMarks = 0
    let oMarks = 0

    // Regras: 61-90=1 risca, 91-119=2 riscas, 120=4 riscas
    if (pPoints === 120) {
        matchScore.value.player = 4
    } else if (pPoints >= 91) {
        pMarks = 2
    } else if (pPoints >= 61) {
        pMarks = 1
    }

    if (oPoints === 120) {
        matchScore.value.opponent = 4
    } else if (oPoints >= 91) {
        oMarks = 2
    } else if (oPoints >= 61) {
        oMarks = 1
    }

    // Soma riscas (se ninguém fez bandeira ainda)
    if (matchScore.value.player < 4 && matchScore.value.opponent < 4) {
        matchScore.value.player += pMarks
        matchScore.value.opponent += oMarks
    }

    // Verifica se a PARTIDA acabou (alguém tem >= 4 riscas)
    if (matchScore.value.player >= 4 || matchScore.value.opponent >= 4) {
        gameStatus.value = 'match_ended'
    } else {
        gameStatus.value = 'ended'
    }
  }

  return {
    // State
    deck, playerHand, botHand, table, trumpCard,
    turn, score, gameStatus, cardsPerHand, matchScore,
    // Actions
    initGame, playCard, nextRound
  }
})
