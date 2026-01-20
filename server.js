// =====================================
// IMPORTAÇÕES
// =====================================
require("dotenv").config();

const qrcode = require("qrcode-terminal");
const { Client, LocalAuth } = require("whatsapp-web.js");
const OpenAI = require("openai");

// =====================================
// OPENAI
// =====================================
let openai = null;
if (process.env.OPENAI_API_KEY && process.env.OPENAI_API_KEY.trim()) {
  openai = new OpenAI({ apiKey: process.env.OPENAI_API_KEY });
} else {
  console.log("⚠️ OPENAI_API_KEY não configurada. IA ficará desativada (menu continua funcionando).");
}
const OPENAI_MODEL = process.env.OPENAI_MODEL || "gpt-4.1-mini";

// =====================================
// URL RESERVA (PHP)
// =====================================
const PUBLIC_BASE_URL = process.env.PUBLIC_BASE_URL || "http://localhost:4000";
const RESERVA_URL = `${PUBLIC_BASE_URL.replace(/\/$/, "")}/reserva/index.php`;

// =====================================
// CONFIGURAÇÕES
// =====================================
const COOLDOWN_MS = 1200;

// =====================================
// CONFIGURAÇÃO DO CLIENTE
// =====================================
const client = new Client({
  authStrategy: new LocalAuth(),
  puppeteer: {
    headless: false,
    args: ["--no-sandbox", "--disable-setuid-sandbox", "--disable-dev-shm-usage"],
    ...(process.env.CHROME_PATH ? { executablePath: process.env.CHROME_PATH } : {}),
  },
});

// =====================================
// QR CODE
// =====================================
client.on("qr", (qr) => {
  console.log("📲 Escaneie o QR Code abaixo:");
  qrcode.generate(qr, { small: true });
});

// =====================================
// WHATSAPP CONECTADO
// =====================================
client.on("ready", () => console.log("✅ Tudo certo! WhatsApp conectado."));
client.on("disconnected", (reason) => console.log("⚠️ Desconectado:", reason));
client.initialize();

// =====================================
// UTIL
// =====================================
const delay = (ms) => new Promise((res) => setTimeout(res, ms));

function normalizeText(text = "") {
  return text.trim();
}

function normalizeKey(text = "") {
  return text.trim().toLowerCase();
}

function getGreeting() {
  const hora = new Date().getHours();
  if (hora >= 5 && hora < 12) return "Bom dia";
  if (hora >= 12 && hora < 18) return "Boa tarde";
  return "Boa noite";
}

async function simulateTyping(chat, ms = 900) {
  try {
    await chat.sendStateTyping();
    await delay(ms);
    await chat.clearState();
  } catch (_) {}
}

async function safeSend(chatId, message) {
  return client.sendMessage(chatId, message, { sendSeen: false });
}

// =====================================
// ESTADO (somente atendimento)
// =====================================
const userState = new Map();

function getUser(chatId) {
  if (!userState.has(chatId)) {
    userState.set(chatId, {
      step: "NEW", // NEW | MENU | HUMAN | FAQ | CHAT
      lastMsgAt: 0,
      aiHistory: [],
    });
  }
  return userState.get(chatId);
}

function tooFast(user) {
  const now = Date.now();
  if (now - user.lastMsgAt < COOLDOWN_MS) return true;
  user.lastMsgAt = now;
  return false;
}

function pushHistory(user, role, content) {
  user.aiHistory.push({ role, content });
  if (user.aiHistory.length > 10) user.aiHistory = user.aiHistory.slice(-10);
}

// =====================================
// MENSAGENS
// =====================================
function menuMessage() {
  return (
    `Escolha uma opção:\n\n` +
    `1) Informações sobre Day Use\n` +
    `2) Hospedagem (opções)\n` +
    `3) Regras e perguntas frequentes\n` +
    `4) Falar com atendimento humano\n` +
    `5) Fazer reserva e pagamento (link)\n` +
    `0) Ver menu novamente`
  );
}

function welcomeMessage() {
  return (
    `${getGreeting()}! 👋\n\n` +
    `Sou o atendimento do *Monã – Terra Sem Males* 🌿\n\n` +
    `Eu tiro suas dúvidas por aqui — e quando você quiser *reservar e pagar*, você faz pelo nosso site de reserva:\n` +
    `🔗 ${RESERVA_URL}\n\n` +
    `${menuMessage()}`
  );
}

function dayUseInfo() {
  return (
    `🌿 *Day Use privativo (grupo fechado)*\n\n` +
    `• Horário: *9h às 18h30*\n` +
    `• Valor mínimo por grupo: *R$ 1.000*\n` +
    `• O espaço fica reservado só para o seu grupo\n\n` +
    `Quer reservar agora? Aqui está o link:\n` +
    `🔗 ${RESERVA_URL}\n\n` +
    `Se quiser, me diga sua dúvida que eu te ajudo.`
  );
}

function lodgingInfo() {
  return (
    `🏡 *Hospedagem (para quem contrata o Day Use)*\n\n` +
    `Temos apenas *2 suítes*:\n` +
    `• Suíte com *1 cama de casal + redes* – *R$ 500/noite*\n` +
    `• Suíte com *2 camas de casal + redes* – *R$ 800/noite*\n\n` +
    `Para reservar e pagar, use o link:\n` +
    `🔗 ${RESERVA_URL}`
  );
}

function rulesFaq() {
  return (
    `📌 *Regras do Monã (não negociáveis)*\n\n` +
    `• Check-in: 9h\n` +
    `• Check-out: até 8h\n` +
    `• Para ficar após check-out: somente com novo Day Use (sujeito à disponibilidade)\n` +
    `• Sem visitantes externos\n` +
    `• Sem piscina artificial e sem som alto\n\n` +
    `Se quiser seguir com a reserva, é por aqui:\n` +
    `🔗 ${RESERVA_URL}`
  );
}

function humanMessage() {
  return (
    `Perfeito 🙂\n\n` +
    `Me diga por favor:\n` +
    `• Seu *nome*\n` +
    `• Qual a *dúvida* ou o que você precisa\n\n` +
    `Se preferir já reservar e pagar, use o link:\n` +
    `🔗 ${RESERVA_URL}`
  );
}

function reserveLinkMessage() {
  return (
    `Fechado ✅\n\n` +
    `Para *reservar e pagar online*, continue por aqui:\n` +
    `🔗 ${RESERVA_URL}\n\n` +
    `Se quiser, pode me mandar sua dúvida antes de finalizar.`
  );
}

// =====================================
// IA — PERSONALIDADE (somente atendimento)
// =====================================
function buildSystemPrompt() {
  return (
    `Você é o atendimento do Monã – Terra Sem Males pelo WhatsApp.\n` +
    `Seu papel é responder dúvidas com simpatia, clareza e objetividade.\n\n` +
    `REGRAS:\n` +
    `- NÃO colete CPF, data de nascimento, dados pessoais sensíveis, nem dados de pagamento.\n` +
    `- NÃO confirme reserva, não prometa disponibilidade.\n` +
    `- Sempre que o cliente quiser reservar/pagar, direcione para o link: ${RESERVA_URL}\n` +
    `- Se o cliente pedir valores, horários, regras, explique.\n` +
    `- Mantenha mensagens curtas e humanas.\n`
  );
}

async function aiReply(user, userText) {
  if (!openai) {
    return (
      `Entendi 🙂\n\n` +
      `Para reservar e pagar online, use:\n🔗 ${RESERVA_URL}\n\n` +
      `${menuMessage()}`
    );
  }

  const messages = [
    { role: "system", content: buildSystemPrompt() },
    ...user.aiHistory,
    { role: "user", content: userText },
  ];

  const resp = await openai.chat.completions.create({
    model: OPENAI_MODEL,
    messages,
    temperature: 0.5,
    max_tokens: 220,
  });

  const out = resp.choices?.[0]?.message?.content?.trim();
  return out || `Entendi. 🙂`;
}

// =====================================
// DETECÇÃO DE INTENÇÃO (reserva/pagamento)
// =====================================
function wantsReservation(textLower) {
  return /\b(reservar|reserva|agendar|agenda|pagamento|pagar|pix|boleto|cart[aã]o|checkout|comprar|fechar|confirmar)\b/.test(
    textLower
  );
}

// =====================================
// FLUXO PRINCIPAL
// =====================================
async function handleFlow(chatId, chat, rawText) {
  const user = getUser(chatId);

  const text = normalizeText(rawText);
  const key = normalizeKey(text);

  // atalhos
  if (/^(menu|oi|olá|ola|bom dia|boa tarde|boa noite|in[ií]cio|inicio)$/i.test(key)) {
    user.step = "MENU";
    await simulateTyping(chat, 700);
    const msg = welcomeMessage();
    pushHistory(user, "assistant", msg);
    return safeSend(chatId, msg);
  }

  // primeira interação
  if (user.step === "NEW") {
    user.step = "MENU";
    await simulateTyping(chat, 700);
    const msg = welcomeMessage();
    pushHistory(user, "assistant", msg);
    return safeSend(chatId, msg);
  }

  // se detectar intenção de reserva/pagamento a qualquer momento: manda link
  if (wantsReservation(key)) {
    await simulateTyping(chat, 700);
    const msg = reserveLinkMessage();
    pushHistory(user, "assistant", msg);
    return safeSend(chatId, msg);
  }

  // MENU
  if (user.step === "MENU") {
    if (key === "1") {
      await simulateTyping(chat, 700);
      const msg = dayUseInfo();
      pushHistory(user, "assistant", msg);
      return safeSend(chatId, msg);
    }
    if (key === "2") {
      await simulateTyping(chat, 700);
      const msg = lodgingInfo();
      pushHistory(user, "assistant", msg);
      return safeSend(chatId, msg);
    }
    if (key === "3") {
      await simulateTyping(chat, 700);
      const msg = rulesFaq();
      pushHistory(user, "assistant", msg);
      return safeSend(chatId, msg);
    }
    if (key === "4") {
      user.step = "HUMAN";
      await simulateTyping(chat, 700);
      const msg = humanMessage();
      pushHistory(user, "assistant", msg);
      return safeSend(chatId, msg);
    }
    if (key === "5") {
      await simulateTyping(chat, 700);
      const msg = reserveLinkMessage();
      pushHistory(user, "assistant", msg);
      return safeSend(chatId, msg);
    }
    if (key === "0") {
      await simulateTyping(chat, 600);
      const msg = menuMessage();
      pushHistory(user, "assistant", msg);
      return safeSend(chatId, msg);
    }

    // texto livre: IA responde e ao final recomenda link de reserva
    pushHistory(user, "user", text);
    await simulateTyping(chat, 800);

    let ai = await aiReply(user, text);

    // garante CTA de reserva se a resposta não tiver
    if (!ai.includes("/reserva/index.php")) {
      ai += `\n\nPara reservar e pagar online:\n🔗 ${RESERVA_URL}`;
    }

    pushHistory(user, "assistant", ai);
    return safeSend(chatId, ai);
  }

  // HUMANO (por enquanto só confirma recebimento e manda link)
  if (user.step === "HUMAN") {
    await simulateTyping(chat, 800);
    user.step = "MENU";
    const msg =
      `Recebi ✅ Vou encaminhar para o atendimento humano.\n\n` +
      `Se você preferir já reservar e pagar, é por aqui:\n🔗 ${RESERVA_URL}\n\n` +
      `Digite *0* para ver o menu novamente.`;
    pushHistory(user, "assistant", msg);
    return safeSend(chatId, msg);
  }

  // fallback
  user.step = "MENU";
  await simulateTyping(chat, 700);
  const msg = welcomeMessage();
  pushHistory(user, "assistant", msg);
  return safeSend(chatId, msg);
}

// =====================================
// RECEBE MENSAGENS (SOMENTE PRIVADO)
// =====================================
client.on("message", async (msg) => {
  try {
    if (!msg.from || msg.from.endsWith("@g.us")) return;
    if (msg.from === "status@broadcast") return;
    if (msg.fromMe) return;

    const chat = await msg.getChat();
    if (chat.isGroup) return;

    const chatId = msg.from;
    const text = normalizeText(msg.body || "");
    const user = getUser(chatId);

    if (tooFast(user)) return;

    if (!text) {
      await simulateTyping(chat, 600);
      return safeSend(chatId, `Recebi sua mensagem 🙂\n\nPara reservar e pagar:\n🔗 ${RESERVA_URL}`);
    }

    await handleFlow(chatId, chat, text);
  } catch (error) {
    console.error("❌ Erro no processamento:", error);
  }
});

// =====================================
// PROCESS SAFETY
// =====================================
process.on("unhandledRejection", (reason) => console.error("❌ unhandledRejection:", reason));
process.on("uncaughtException", (err) => console.error("❌ uncaughtException:", err));
