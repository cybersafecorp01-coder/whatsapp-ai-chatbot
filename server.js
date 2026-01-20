// =====================================
// IMPORTAÇÕES
// =====================================
require("dotenv").config();

const qrcode = require("qrcode-terminal");
const { Client, LocalAuth } = require("whatsapp-web.js");
const OpenAI = require("openai");
const mysql = require("mysql2/promise");

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
// MYSQL (consulta de reserva)
// =====================================
const MYSQL_HOST = process.env.MYSQL_HOST || "localhost";
const MYSQL_DB = process.env.MYSQL_DB || "mona_reservas";
const MYSQL_USER = process.env.MYSQL_USER || "root";
const MYSQL_PASS = process.env.MYSQL_PASS || "";
const MYSQL_PORT = Number(process.env.MYSQL_PORT || 3306);

const pool = mysql.createPool({
  host: MYSQL_HOST,
  user: MYSQL_USER,
  password: MYSQL_PASS,
  database: MYSQL_DB,
  port: MYSQL_PORT,
  waitForConnections: true,
  connectionLimit: 5,
  queueLimit: 0,
});

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

function onlyDigits(s) {
  return (s || "").toString().replace(/\D+/g, "");
}

function maskCPF(cpf) {
  const c = onlyDigits(cpf);
  if (c.length !== 11) return cpf;
  return `${c.slice(0, 3)}.***.***-${c.slice(9)}`;
}

function pickFirstCPF(text) {
  const digits = onlyDigits(text);
  // tenta achar 11 dígitos em sequência dentro do texto
  const m = digits.match(/(\d{11})/);
  return m ? m[1] : null;
}

// =====================================
// ESTADO
// =====================================
const userState = new Map();

function getUser(chatId) {
  if (!userState.has(chatId)) {
    userState.set(chatId, {
      step: "NEW", // NEW | MENU | HUMAN | LOOKUP_ASK
      lastMsgAt: 0,
      aiHistory: [],
      lookup: {
        expectingCpf: false,
        expectingName: false,
      },
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
// MENSAGENS (humanizadas)
// =====================================
function menuMessage() {
  return (
    `✨ O que você gostaria de fazer?\n\n` +
    `1️⃣ *Day Use* (como funciona + valores)\n` +
    `2️⃣ *Hospedagem* (suítes + valores)\n` +
    `3️⃣ *Regras e dúvidas* (FAQ)\n` +
    `4️⃣ *Falar com humano*\n` +
    `5️⃣ *Reservar e pagar online* (link)\n` +
    `6️⃣ *Consultar minha reserva* (Nome + CPF)\n` +
    `0️⃣ Ver menu de novo`
  );
}

function welcomeMessage() {
  return (
    `${getGreeting()}! 👋🌿\n\n` +
    `Eu sou o atendimento do *Monã – Terra Sem Males*.\n` +
    `Tô por aqui pra tirar suas dúvidas rapidinho e te orientar. 🙂\n\n` +
    `👉 Quando você quiser *reservar e pagar*, é só continuar pelo nosso site:\n` +
    `🔗 ${RESERVA_URL}\n\n` +
    `${menuMessage()}`
  );
}

function dayUseInfo() {
  return (
    `🌿 *Day Use privativo (grupo fechado)*\n\n` +
    `⏰ *9h às 18h30*\n` +
    `💰 *Valor mínimo: R$ 1.000 por grupo*\n` +
    `🔒 O espaço fica reservado só pro seu grupo\n\n` +
    `Se você já quiser garantir, é por aqui:\n` +
    `🔗 ${RESERVA_URL}\n\n` +
    `Quer que eu te ajude com alguma dúvida específica?`
  );
}

function lodgingInfo() {
  return (
    `🏡 *Hospedagem (para quem contrata o Day Use)*\n\n` +
    `Temos só *2 suítes* (bem exclusivas):\n` +
    `🛏️ 1 cama de casal + redes — *R$ 500/noite*\n` +
    `🛏️🛏️ 2 camas de casal + redes — *R$ 800/noite*\n\n` +
    `Pra reservar e pagar, segue o link:\n` +
    `🔗 ${RESERVA_URL}`
  );
}

function rulesFaq() {
  return (
    `📌 *Regras do Monã (pra manter a experiência tranquila 🌿)*\n\n` +
    `✅ Check-in: *9h*\n` +
    `✅ Check-out: até *8h*\n` +
    `🚫 Sem visitantes externos\n` +
    `🚫 Sem piscina artificial\n` +
    `🔇 Sem som alto\n\n` +
    `Se você quiser reservar agora:\n` +
    `🔗 ${RESERVA_URL}`
  );
}

function humanMessage() {
  return (
    `Claro! 🙂\n\n` +
    `Me diz rapidinho:\n` +
    `• Seu *nome*\n` +
    `• O que você precisa (dúvida / objetivo)\n\n` +
    `Se preferir já adiantar a reserva e pagamento:\n` +
    `🔗 ${RESERVA_URL}`
  );
}

function reserveLinkMessage() {
  return (
    `Fechado! ✅🌿\n\n` +
    `Pra *reservar e pagar online*, é só continuar aqui:\n` +
    `🔗 ${RESERVA_URL}\n\n` +
    `Se travar em alguma etapa, me manda mensagem que eu te ajudo. 🙂`
  );
}

function askLookupMessage() {
  return (
    `Perfeito 🙂 Vou puxar aqui sua reserva.\n\n` +
    `Me envie *Nome + CPF* (pode ser tudo na mesma mensagem).\n` +
    `Ex.: João Silva, 123.456.789-09\n\n` +
    `🔒 Uso apenas pra localizar sua reserva.`
  );
}

// =====================================
// IA — PERSONALIDADE (SEM DADOS SENSÍVEIS)
// =====================================
function buildSystemPrompt() {
  return (
    `Você é o atendimento do Monã – Terra Sem Males no WhatsApp.\n` +
    `Responda com simpatia, linguagem humana, e use emojis com moderação.\n\n` +
    `REGRAS IMPORTANTES:\n` +
    `- Não peça CPF/dados pessoais. (O sistema fora da IA cuida disso quando necessário.)\n` +
    `- Não confirme disponibilidade nem “reserva confirmada”.\n` +
    `- Quando o cliente quiser reservar/pagar, direcione para: ${RESERVA_URL}\n` +
    `- Respostas curtas, claras, acolhedoras.\n`
  );
}

async function aiReply(user, userText) {
  if (!openai) {
    return `Entendi 🙂\n\nSe você quiser reservar e pagar online:\n🔗 ${RESERVA_URL}`;
  }

  const messages = [
    { role: "system", content: buildSystemPrompt() },
    ...user.aiHistory,
    { role: "user", content: userText },
  ];

  const resp = await openai.chat.completions.create({
    model: OPENAI_MODEL,
    messages,
    temperature: 0.6,
    max_tokens: 240,
  });

  const out = resp.choices?.[0]?.message?.content?.trim();
  return out || `Entendi 🙂`;
}

// =====================================
// INTENÇÕES
// =====================================
function wantsReservation(textLower) {
  return /\b(reservar|reserva|agendar|agenda|pagamento|pagar|pix|boleto|cart[aã]o|checkout|comprar|fechar|confirmar)\b/.test(
    textLower
  );
}

function wantsLookup(textLower) {
  return /\b(consultar|minha reserva|meu pedido|meu pagamento|status|comprovante|já paguei|paguei|confirmação)\b/.test(
    textLower
  );
}

// =====================================
// MYSQL: buscar reserva por CPF/Nome
// Ajuste nomes de tabela/colunas conforme seu MySQL.
// A query abaixo assume:
// - tabela `reservas` com campos: id, token, nome, cpf, data_iso, data_br, total, status, payment_status, payment_url, created_at
// Se sua tabela tiver outro nome, me diga que eu adapto.
// =====================================
async function findLatestReservationByCpfName({ cpfDigits, name }) {
  const cpf = onlyDigits(cpfDigits);
  if (cpf.length !== 11) return null;

  // tenta casar por CPF e (opcional) nome parcial
  const nameLike = name ? `%${name.trim()}%` : null;

  // 1) tenta com CPF + nome
  if (nameLike) {
    const [rows] = await pool.query(
      `
      SELECT *
      FROM reservas
      WHERE cpf = ?
        AND nome LIKE ?
      ORDER BY id DESC
      LIMIT 1
      `,
      [cpf, nameLike]
    );
    if (rows?.length) return rows[0];
  }

  // 2) fallback só CPF
  const [rows2] = await pool.query(
    `
    SELECT *
    FROM reservas
    WHERE cpf = ?
    ORDER BY id DESC
    LIMIT 1
    `,
    [cpf]
  );
  if (rows2?.length) return rows2[0];

  return null;
}

function formatReservationSummary(r) {
  // adapte campos conforme seu banco
  const nome = r.nome || "Cliente";
  const cpfMasked = maskCPF(r.cpf || "");
  const data = r.data_br || r.data_iso || "-";
  const total = r.total != null ? `R$ ${Number(r.total).toFixed(2).replace(".", ",")}` : "-";
  const status = (r.status || "").toString().toUpperCase();
  const payStatus = (r.payment_status || "").toString().toUpperCase();
  const payUrl = r.payment_url || null;

  let statusHuman = "em andamento";
  if (payStatus === "CONFIRMED" || status === "CONFIRMED") statusHuman = "✅ pago e confirmado";
  else if (payStatus === "PENDING" || status === "PENDING") statusHuman = "⏳ aguardando pagamento";
  else if (payStatus === "FAILED" || status === "CANCELLED") statusHuman = "⚠️ com pendência";

  return (
    `Encontrei sua reserva, *${nome}* 🙂\n\n` +
    `🧾 CPF: ${cpfMasked}\n` +
    `📅 Data: *${data}*\n` +
    `💰 Valor: *${total}*\n` +
    `📌 Status: *${statusHuman}*\n` +
    (payUrl ? `\n🔗 Link de pagamento:\n${payUrl}\n` : "") +
    `\nSe quiser fazer uma nova reserva:\n🔗 ${RESERVA_URL}`
  );
}

// =====================================
// FLUXO
// =====================================
async function handleFlow(chatId, chat, rawText) {
  const user = getUser(chatId);

  const text = normalizeText(rawText);
  const key = normalizeKey(text);

  // atalhos
  if (/^(menu|oi|olá|ola|bom dia|boa tarde|boa noite|in[ií]cio|inicio)$/i.test(key)) {
    user.step = "MENU";
    await simulateTyping(chat, 650);
    const msg = welcomeMessage();
    pushHistory(user, "assistant", msg);
    return safeSend(chatId, msg);
  }

  // primeira interação
  if (user.step === "NEW") {
    user.step = "MENU";
    await simulateTyping(chat, 650);
    const msg = welcomeMessage();
    pushHistory(user, "assistant", msg);
    return safeSend(chatId, msg);
  }

  // modo consulta (esperando Nome + CPF)
  if (user.step === "LOOKUP_ASK") {
    await simulateTyping(chat, 800);

    const cpf = pickFirstCPF(text);
    const name = text
      .replace(cpf ? cpf : "", "")
      .replace(/[,\-]/g, " ")
      .trim();

    if (!cpf) {
      return safeSend(chatId, `Consigo consultar sim 🙂\nSó me envie um CPF válido (11 dígitos), por favor.`);
    }

    try {
      const r = await findLatestReservationByCpfName({ cpfDigits: cpf, name: name || null });
      user.step = "MENU";

      if (!r) {
        return safeSend(
          chatId,
          `Não encontrei nenhuma reserva com esse CPF 😕\n\n` +
            `Se você ainda não finalizou, pode reservar por aqui:\n🔗 ${RESERVA_URL}\n\n` +
            `Ou digite *0* pra ver o menu.`
        );
      }

      return safeSend(chatId, formatReservationSummary(r));
    } catch (e) {
      user.step = "MENU";
      console.error("❌ erro lookup mysql:", e);
      return safeSend(
        chatId,
        `Tive um probleminha pra consultar agora 😕\n` +
          `Pode tentar novamente em instantes.\n\n` +
          `Se preferir, você também pode seguir pela reserva online:\n🔗 ${RESERVA_URL}`
      );
    }
  }

  // intenção: consultar reserva
  if (wantsLookup(key)) {
    user.step = "LOOKUP_ASK";
    await simulateTyping(chat, 700);
    const msg = askLookupMessage();
    pushHistory(user, "assistant", msg);
    return safeSend(chatId, msg);
  }

  // intenção: reserva/pagamento
  if (wantsReservation(key)) {
    await simulateTyping(chat, 650);
    const msg = reserveLinkMessage();
    pushHistory(user, "assistant", msg);
    return safeSend(chatId, msg);
  }

  // MENU
  if (user.step === "MENU") {
    // aceita tanto "1" quanto "1️⃣"
    if (key === "1" || key.includes("1️⃣")) {
      await simulateTyping(chat, 650);
      const msg = dayUseInfo();
      pushHistory(user, "assistant", msg);
      return safeSend(chatId, msg);
    }

    if (key === "2" || key.includes("2️⃣")) {
      await simulateTyping(chat, 650);
      const msg = lodgingInfo();
      pushHistory(user, "assistant", msg);
      return safeSend(chatId, msg);
    }

    if (key === "3" || key.includes("3️⃣")) {
      await simulateTyping(chat, 650);
      const msg = rulesFaq();
      pushHistory(user, "assistant", msg);
      return safeSend(chatId, msg);
    }

    if (key === "4" || key.includes("4️⃣")) {
      user.step = "HUMAN";
      await simulateTyping(chat, 650);
      const msg = humanMessage();
      pushHistory(user, "assistant", msg);
      return safeSend(chatId, msg);
    }

    if (key === "5" || key.includes("5️⃣")) {
      await simulateTyping(chat, 650);
      const msg = reserveLinkMessage();
      pushHistory(user, "assistant", msg);
      return safeSend(chatId, msg);
    }

    if (key === "6" || key.includes("6️⃣")) {
      user.step = "LOOKUP_ASK";
      await simulateTyping(chat, 700);
      const msg = askLookupMessage();
      pushHistory(user, "assistant", msg);
      return safeSend(chatId, msg);
    }

    if (key === "0" || key.includes("0️⃣")) {
      await simulateTyping(chat, 600);
      const msg = welcomeMessage();
      pushHistory(user, "assistant", msg);
      return safeSend(chatId, msg);
    }

    // texto livre -> IA responde (SEM mandar CPF/PII pra IA)
    // Se o usuário digitou CPF aqui, a gente intercepta e oferece consulta.
    const cpfInText = pickFirstCPF(text);
    if (cpfInText) {
      user.step = "LOOKUP_ASK";
      await simulateTyping(chat, 650);
      return safeSend(chatId, `Vi que você mandou um CPF 🙂\nMe manda também seu *nome* junto pra eu localizar certinho?`);
    }

    pushHistory(user, "user", text);
    await simulateTyping(chat, 750);

    let ai = await aiReply(user, text);

    // CTA no fim
    if (!ai.includes("/reserva/index.php")) {
      ai += `\n\nSe você quiser reservar/pagar online:\n🔗 ${RESERVA_URL}`;
    }

    pushHistory(user, "assistant", ai);
    return safeSend(chatId, ai);
  }

  // HUMANO
  if (user.step === "HUMAN") {
    await simulateTyping(chat, 750);
    user.step = "MENU";
    const msg =
      `Perfeito, recebi ✅\n` +
      `Vou encaminhar pro humano aqui.\n\n` +
      `Enquanto isso, se você quiser adiantar a reserva/pagamento:\n` +
      `🔗 ${RESERVA_URL}\n\n` +
      `Digite *0* pra voltar ao menu.`;
    pushHistory(user, "assistant", msg);
    return safeSend(chatId, msg);
  }

  // fallback
  user.step = "MENU";
  await simulateTyping(chat, 650);
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
      return safeSend(chatId, `Te ouvi 🙂\n\nPra reservar/pagar online:\n🔗 ${RESERVA_URL}`);
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
