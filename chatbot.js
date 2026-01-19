// =====================================
// IMPORTAÇÕES
// =====================================
const qrcode = require("qrcode-terminal");
const { Client, LocalAuth } = require("whatsapp-web.js");

// =====================================
// CONFIGURAÇÃO DO CLIENTE
// =====================================
const client = new Client({
    authStrategy: new LocalAuth(),
    puppeteer: {
        headless: false,
        args: [
            "--no-sandbox",
            "--disable-setuid-sandbox",
            "--disable-dev-shm-usage",
        ],
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
client.on("ready", () => {
    console.log("✅ Tudo certo! WhatsApp conectado.");
});

client.on("disconnected", (reason) => {
    console.log("⚠️ Desconectado:", reason);
});

// =====================================
// INICIALIZA
// =====================================
client.initialize();

// =====================================
// UTIL
// =====================================
const delay = (ms) => new Promise((res) => setTimeout(res, ms));

function normalizeText(text = "") {
    return text.trim().toLowerCase();
}

function getGreeting() {
    const hora = new Date().getHours();
    if (hora >= 5 && hora < 12) return "Bom dia";
    if (hora >= 12 && hora < 18) return "Boa tarde";
    return "Boa noite";
}

async function simulateTyping(chat, ms = 1500) {
    try {
        await chat.sendStateTyping();
        await delay(ms);
        await chat.clearState();
    } catch (_) {}
}

/**
 * Workaround do bug "markedUnread/sendSeen".
 * Sempre envia com sendSeen: false para não quebrar.
 */
async function safeSend(chatId, message) {
    return client.sendMessage(chatId, message, { sendSeen: false });
}

// =====================================
// ESTADO + RATE LIMIT (memória)
// =====================================
const userState = new Map(); // key: chatId -> { step, lastMsgAt }
const COOLDOWN_MS = 1500;

function getUser(chatId) {
    if (!userState.has(chatId)) {
        userState.set(chatId, { step: "NEW", lastMsgAt: 0 });
    }
    return userState.get(chatId);
}

function tooFast(user) {
    const now = Date.now();
    if (now - user.lastMsgAt < COOLDOWN_MS) return true;
    user.lastMsgAt = now;
    return false;
}

// =====================================
// MENSAGENS PRONTAS
// =====================================
function menuMessage() {
    return (
        `Escolha uma opção:\n\n` +
        `1) Informações sobre a versão PRO\n` +
        `2) Falar com atendimento humano\n` +
        `3) Como deixar o bot 24h no ar\n` +
        `0) Ver menu novamente`
    );
}

function proMessage() {
    return (
        `🚀 Na versão PRO você vai além: desbloqueie tudo!\n\n` +
        `✍️ Envio de textos\n` +
        `🎙️ Áudios\n` +
        `🖼️ Imagens\n` +
        `🎥 Vídeos\n` +
        `📂 Arquivos\n\n` +
        `💡 Simulação de "digitando..." e "gravando áudio"\n` +
        `🚀 Envio de mensagens em massa\n` +
        `📇 Captura automática de contatos\n` +
        `💻 Rodar 24h com o PC desligado\n` +
        `✅ + 3 bônus exclusivos\n\n` +
        `🔥 Adquira agora:\nhttps://pay.kiwify.com.br/FkTOhRZ?src=pro`
    );
}

function humanMessage() {
    return (
        `Perfeito. Me diga por favor:\n` +
        `• Seu nome\n` +
        `• Qual sua dúvida/objetivo\n\n` +
        `Assim eu já te encaminho certinho. 🙂`
    );
}

function uptimeMessage() {
    return (
        `Para rodar 24h, você tem 3 caminhos comuns:\n\n` +
        `1) VPS (servidor) + PM2\n` +
        `2) Docker em servidor\n` +
        `3) Hospedagem Windows (menos recomendado)\n\n` +
        `Se você me disser qual é seu ambiente (Windows / Linux), eu te passo um passo a passo.`
    );
}

// =====================================
// HANDLERS
// =====================================
async function sendWelcome(chatId, chat) {
    const saudacao = getGreeting();
    await simulateTyping(chat, 1200);

    return safeSend(
        chatId,
        `${saudacao}! 👋\n\n` +
        `Mensagem automática do robô 🤖\n\n` +
        menuMessage()
    );
}

async function handleMenuFlow(chatId, chat, text) {
    const user = getUser(chatId);

    // Se o usuário pedir menu/saudação em qualquer etapa
    if (/^(menu|oi|olá|ola|bom dia|boa tarde|boa noite)$/i.test(text)) {
        user.step = "MENU";
        return sendWelcome(chatId, chat);
    }

    // Primeira interação
    if (user.step === "NEW") {
        user.step = "MENU";
        return sendWelcome(chatId, chat);
    }

    // Etapa: MENU
    if (user.step === "MENU") {
        if (text === "1") {
            await simulateTyping(chat, 1000);
            await safeSend(chatId, proMessage());
            await simulateTyping(chat, 800);
            return safeSend(chatId, `Se quiser, digite 0 para ver o menu novamente.`);
        }

        if (text === "2") {
            user.step = "HUMAN";
            await simulateTyping(chat, 1000);
            return safeSend(chatId, humanMessage());
        }

        if (text === "3") {
            user.step = "UPTIME";
            await simulateTyping(chat, 1000);
            return safeSend(chatId, uptimeMessage());
        }

        if (text === "0") {
            await simulateTyping(chat, 800);
            return safeSend(chatId, menuMessage());
        }

        await simulateTyping(chat, 800);
        return safeSend(chatId, `Não entendi. 🙂\n\n${menuMessage()}`);
    }

    // Etapa: HUMANO (coleta)
    if (user.step === "HUMAN") {
        await simulateTyping(chat, 900);
        user.step = "MENU";
        return safeSend(
            chatId,
            `Perfeito! Recebi sua mensagem. ✅\n` +
            `Vou te responder assim que possível.\n\n` +
            `Digite 0 para ver o menu novamente.`
        );
    }

    // Etapa: UPTIME (coleta)
    if (user.step === "UPTIME") {
        await simulateTyping(chat, 900);
        user.step = "MENU";
        return safeSend(
            chatId,
            `Fechado! 👍\n` +
            `Só confirma: você usa Windows ou Linux?\n\n` +
            `Digite 0 para menu.`
        );
    }

    // fallback
    user.step = "MENU";
    return sendWelcome(chatId, chat);
}

// =====================================
// RECEBE MENSAGENS (SOMENTE PRIVADO)
// =====================================
client.on("message", async(msg) => {
    try {
        // Ignora grupos
        if (!msg.from || msg.from.endsWith("@g.us")) return;

        // Ignora status/broadcast e similares
        if (msg.from === "status@broadcast") return;

        // ignora mensagens do próprio bot
        if (msg.fromMe) return;

        const chat = await msg.getChat();
        if (chat.isGroup) return;

        const chatId = msg.from;
        const text = normalizeText(msg.body || "");
        const user = getUser(chatId);

        // rate limit simples
        if (tooFast(user)) return;

        // Se veio vazio (mídia, figurinha, etc.)
        if (!text) {
            await simulateTyping(chat, 800);
            return safeSend(
                chatId,
                `Recebi sua mensagem. 🙂\n` +
                `Se quiser, digite "menu" para ver as opções.`
            );
        }

        await handleMenuFlow(chatId, chat, text);
    } catch (error) {
        console.error("❌ Erro no processamento:", error);
    }
});

// =====================================
// PROCESS SAFETY
// =====================================
process.on("unhandledRejection", (reason) => {
    console.error("❌ unhandledRejection:", reason);
});

process.on("uncaughtException", (err) => {
    console.error("❌ uncaughtException:", err);
});