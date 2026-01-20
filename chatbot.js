// =====================================
// IMPORTAÇÕES
// =====================================
require("dotenv").config();

const qrcode = require("qrcode-terminal");
const { Client, LocalAuth } = require("whatsapp-web.js");
const OpenAI = require("openai");

const chrono = require("chrono-node");
const { DateTime } = require("luxon");

// =====================================
// OPENAI
// =====================================
let openai = null;
if (process.env.OPENAI_API_KEY && process.env.OPENAI_API_KEY.trim()) {
    openai = new OpenAI({ apiKey: process.env.OPENAI_API_KEY });
} else {
    console.log("⚠️ OPENAI_API_KEY não configurada. IA ficará desativada (menu continua funcionando).");
}
const OPENAI_MODEL = "gpt-4.1-mini";

// =====================================
// WEB (pagamento simulado)
// =====================================
const PUBLIC_BASE_URL = process.env.PUBLIC_BASE_URL || "http://localhost:4000";

// =====================================
// CONFIGURAÇÕES
// =====================================
const TZ = "America/Manaus";
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
async function simulateTyping(chat, ms = 1200) {
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
// DATE PARSER (PT-BR) + VALIDAÇÃO
// =====================================
function formatBR(dt) {
    return dt.toFormat("dd/LL/yyyy");
}

function parseAndValidateDatePT(text) {
    const raw = (text || "").trim();

    const year5 = raw.match(/\b(\d{5})\b/);
    if (year5) return { ok: false, reason: "YEAR_5_DIGITS", suggestion: year5[1].slice(0, 4) };

    const ref = new Date();
    const results = chrono.pt.parse(raw, ref);

    if (!results || results.length === 0) {
        const m = raw.match(/\b(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})\b/);
        if (!m) return { ok: false, reason: "NO_DATE" };

        const d = Number(m[1]);
        const mo = Number(m[2]);
        let y = Number(m[3]);
        if (y < 100) y += 2000;

        const dt = DateTime.fromObject({ year: y, month: mo, day: d }, { zone: TZ }).startOf("day");
        if (!dt.isValid) return { ok: false, reason: "INVALID_DATE" };
        return validateDateWindow(dt);
    }

    const parsedDate = results[0].start.date();
    const dt = DateTime.fromJSDate(parsedDate, { zone: TZ }).startOf("day");

    const userHasYear = /\b(19|20)\d{2}\b/.test(raw);
    if (!userHasYear) return { ok: false, reason: "MISSING_YEAR" };

    return validateDateWindow(dt);
}

function validateDateWindow(dt) {
    const today = DateTime.now().setZone(TZ).startOf("day");
    if (dt < today) return { ok: false, reason: "PAST_DATE", parsed: formatBR(dt), today: formatBR(today) };

    const max = today.plus({ years: 2 });
    if (dt > max) return { ok: false, reason: "TOO_FAR", parsed: formatBR(dt), max: formatBR(max) };

    return { ok: true, iso: dt.toISODate(), br: formatBR(dt) };
}

// =====================================
// CPF / DATA NASCIMENTO (validação simples)
// =====================================
function onlyDigits(s) {
    return (s || "").replace(/\D/g, "");
}

function isValidCPF(cpfRaw) {
    const cpf = onlyDigits(cpfRaw);
    if (!cpf || cpf.length !== 11) return false;
    if (/^(\d)\1{10}$/.test(cpf)) return false;

    let sum = 0;
    for (let i = 0; i < 9; i++) sum += Number(cpf[i]) * (10 - i);
    let d1 = (sum * 10) % 11;
    if (d1 === 10) d1 = 0;
    if (d1 !== Number(cpf[9])) return false;

    sum = 0;
    for (let i = 0; i < 10; i++) sum += Number(cpf[i]) * (11 - i);
    let d2 = (sum * 10) % 11;
    if (d2 === 10) d2 = 0;
    if (d2 !== Number(cpf[10])) return false;

    return true;
}

function parseDOB_BR(text) {
    const m = (text || "").match(/\b(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})\b/);
    if (!m) return { ok: false };
    const d = Number(m[1]);
    const mo = Number(m[2]);
    const y = Number(m[3]);
    const dt = DateTime.fromObject({ year: y, month: mo, day: d }, { zone: TZ }).startOf("day");
    if (!dt.isValid) return { ok: false };

    const today = DateTime.now().setZone(TZ).startOf("day");
    if (dt > today) return { ok: false };
    if (today.diff(dt, "years").years < 0) return { ok: false };

    return { ok: true, br: formatBR(dt), iso: dt.toISODate() };
}

// =====================================
// NUMERO POR EXTENSO (pessoas)
// =====================================
const WORD_NUM = {
    um: 1,
    uma: 1,
    dois: 2,
    duas: 2,
    tres: 3,
    três: 3,
    quatro: 4,
    cinco: 5,
    seis: 6,
    sete: 7,
    oito: 8,
    nove: 9,
    dez: 10,
    onze: 11,
    doze: 12,
    treze: 13,
    catorze: 14,
    quatorze: 14,
    quinze: 15,
    dezesseis: 16,
    dezessete: 17,
    dezoito: 18,
    dezenove: 19,
    vinte: 20
};

function tryExtractPeople(textLower) {
    const m1 = textLower.match(/(\d{1,3})\s*(pessoas|pessoa|pax)\b/);
    if (m1) return Number(m1[1]);

    const m2 = textLower.match(/\bsomos\s+(\d{1,3})\b/);
    if (m2) return Number(m2[1]);

    // "duas pessoas", "três pessoas"
    const m3 = textLower.match(/\b(um|uma|dois|duas|tres|três|quatro|cinco|seis|sete|oito|nove|dez|onze|doze|treze|catorze|quatorze|quinze|dezesseis|dezessete|dezoito|dezenove|vinte)\s+(pessoas|pessoa)\b/);
    if (m3 && WORD_NUM[m3[1]] != null) return WORD_NUM[m3[1]];

    return null;
}

function tryExtractYesNo(textLower) {
    if (/\b(sim|s|ok|claro|de acordo|fecho|fechar|vamos|confirmo|confirmar)\b/.test(textLower)) return true;
    if (/\b(não|nao|n|negativo|não quero|nao quero)\b/.test(textLower)) return false;
    return null;
}

function tryExtractSuite(textLower) {
    if (/\b(500|quinhentos)\b/.test(textLower) || /\b1\s*cama\b/.test(textLower) || /\buma\s*cama\b/.test(textLower)) return "suite_500";
    if (/\b(800|oitocentos)\b/.test(textLower) || /\b2\s*camas\b/.test(textLower) || /\bduas\s*camas\b/.test(textLower)) return "suite_800";
    return null;
}

// =====================================
// TOKEN (pagamento/reserva)
// =====================================
function makeToken() {
    return `${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 10)}`.toUpperCase();
}

// =====================================
// ESTADO
// =====================================
const userState = new Map();

function getUser(chatId) {
    if (!userState.has(chatId)) {
        userState.set(chatId, {
            step: "NEW",
            lastMsgAt: 0,
            lead: {
                date: null,
                dateISO: null,
                people: null,
                acceptedDayUse: null,
                wantsStay: null,
                suiteChoice: null,

                // dados do grupo
                peopleData: [], // [{name, cpf, dobBR, dobISO}]
                currentPersonIndex: 0,

                // pagamento
                payment: {
                    token: null,
                    url: null,
                    status: "NONE", // NONE | LINK_SENT | CONFIRMED
                },
            },
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
// MENSAGENS (Monã)
// =====================================
function menuMessage() {
    return (
        `Escolha uma opção:\n\n` +
        `1) Reservar Day Use (informações + disponibilidade)\n` +
        `2) Hospedagem (opção para quem fecha Day Use)\n` +
        `3) Regras e perguntas frequentes\n` +
        `4) Falar com atendimento humano\n` +
        `0) Ver menu novamente`
    );
}

function openingMessage() {
    return (
        `Olá 🌿\n` +
        `Seja bem-vindo(a) ao Monã – Terra Sem Males.\n\n` +
        `Trabalhamos com *Day Use privativo para grupos fechados*, com opção de hospedagem.\n\n` +
        `Para te orientar melhor, pode me informar:\n` +
        `📅 *data desejada*\n` +
        `👥 *número de pessoas*`
    );
}

function dayUsePresentationMessage(date, people) {
    const header =
        date || people ?
        `Perfeito${people ? ` — grupo com *${people}* pessoa(s)` : ""}${date ? ` na data *${date}*` : ""}.\n\n`
            : `Perfeito!\n\n`;

    return (
        header +
        `O Monã funciona no formato de *Day Use exclusivo*, onde todo o espaço fica reservado apenas para o seu grupo.\n\n` +
        `⏰ Horário: *9h às 18h30*\n` +
        `💰 Valor mínimo por grupo: *R$ 1.000*\n\n` +
        `Esse formato de *Day Use privativo* está de acordo para você?\n` +
        `✅ *sim*  |  ❌ *não*`
    );
}

function lodgingOfferMessage() {
    return (
        `Para quem contrata o Day Use, existe a opção de *adicionar hospedagem*, conforme disponibilidade.\n\n` +
        `Temos apenas *2 suítes*:\n` +
        `• Suíte com *1 cama de casal + redes* – *R$ 500/noite*\n` +
        `• Suíte com *2 camas de casal + redes* – *R$ 800/noite*\n\n` +
        `Você gostaria de incluir hospedagem?\n` +
        `Responda: *sim* (e diga qual suíte) ou *não*.`
    );
}

function rulesFaqMessage() {
    return (
        `Regras do Monã (não negociáveis) 🌿\n\n` +
        `• Check-in: 9h | Check-out: até 8h\n` +
        `• Para ficar após check-out: somente com novo Day Use (sujeito à disponibilidade)\n` +
        `• Sem visitantes externos\n` +
        `• Sem piscina artificial e sem som alto\n\n` +
        `Se quiser, me diga *data* e *número de pessoas* e eu sigo com o fluxo.`
    );
}

function humanMessage() {
    return (
        `Perfeito. Me diga por favor:\n` +
        `• Seu *nome*\n` +
        `• *Data desejada*\n` +
        `• *Número de pessoas*\n` +
        `• Qual sua dúvida/objetivo\n\n` +
        `Assim eu já te encaminho certinho. 🙂`
    );
}

function askPersonData(index, total) {
    const n = index + 1;
    return (
        `Para finalizar a reserva, preciso dos dados do grupo.\n\n` +
        `Pessoa ${n}/${total}:\n` +
        `1) *Nome completo*\n` +
        `2) *CPF*\n` +
        `3) *Data de nascimento* (DD/MM/AAAA)\n\n` +
        `Pode enviar tudo em uma mensagem. Ex:\n` +
        `João da Silva, 123.456.789-09, 10/05/1998`
    );
}

function reviewDataMessage(lead) {
    const lines = lead.peopleData
        .map(
            (p, i) =>
                `• Pessoa ${i + 1}: ${p.name} | CPF: ${p.cpfMasked} | Nasc.: ${p.dobBR}`
        )
        .join("\n");

    const stay =
        lead.wantsStay === true
            ? lead.suiteChoice === "suite_500"
                ? "Hospedagem: Suíte R$ 500/noite"
                : lead.suiteChoice === "suite_800"
                    ? "Hospedagem: Suíte R$ 800/noite"
                    : "Hospedagem: sim (suíte a definir)"
            : "Hospedagem: não";

    return (
        `Perfeito ✅ Confere se está tudo certo:\n\n` +
        `Reserva:\n` +
        `• Data: ${lead.date}\n` +
        `• Pessoas: ${lead.people}\n` +
        `• Day Use: 9h às 18h30 (mín. R$ 1.000)\n` +
        `• ${stay}\n\n` +
        `Dados do grupo:\n${lines}\n\n` +
        `Você *confirma os dados e a compra*?\n` +
        `✅ sim | ❌ não`
    );
}

function paymentLinkMessage(lead) {
    return (
        `Fechado ✅\n\n` +
        `Agora vou te enviar o link de pagamento.\n` +
        `🔗 ${lead.payment.url}\n\n` +
        `Após pagar, clique no botão da página para voltar aqui e confirmar automaticamente.`
    );
}

function paymentConfirmedMessage(lead) {
    return (
        `Pagamento confirmado ✅🌿\n\n` +
        `Sua reserva está registrada:\n` +
        `• Data: ${lead.date}\n` +
        `• Grupo: ${lead.people} pessoa(s)\n` +
        `• Day Use: 9h às 18h30\n\n` +
        `Qualquer ajuste, é só me chamar por aqui.`
    );
}

// =====================================
// IA — PERSONALIDADE + REGRAS (Monã)
// =====================================
function buildSystemPrompt(user) {
    const lead = user.lead;

    return (
        `Você é o WhatsApp do Monã – Terra Sem Males.\n` +
        `Seu trabalho é atender e vender pelo WhatsApp com clareza e firmeza.\n\n` +
        `OBJETIVO: vender Day Use privativo. Depois oferecer hospedagem.\n\n` +
        `REGRAS NÃO NEGOCIÁVEIS:\n` +
        `- Check-in: 9h. Check-out: até 8h.\n` +
        `- Após check-out: só com novo Day Use.\n` +
        `- Sem visitantes externos.\n` +
        `- Sem piscina artificial e sem som alto.\n\n` +
        `IMPORTANTE (processo):\n` +
        `- Não confirme reserva antes do sistema pedir/validar dados do grupo.\n` +
        `- Não valide datas no texto (datas são tratadas pelo sistema).\n` +
        `- Se faltar data ou pessoas, peça isso.\n\n` +
        `STATUS:\n` +
        `- Data: ${lead.date || "desconhecida"}\n` +
        `- Pessoas: ${lead.people || "desconhecido"}\n` +
        `- Aceitou Day Use: ${lead.acceptedDayUse === null ? "desconhecido" : lead.acceptedDayUse ? "sim" : "não"}\n` +
        `- Quer hospedagem: ${lead.wantsStay === null ? "desconhecido" : lead.wantsStay ? "sim" : "não"}\n`
    );
}

async function aiReply(user, userText) {
    if (!openai) return `No momento estou sem IA configurada. 🙂\n\n${menuMessage()}`;

    const messages = [{role: "system", content: buildSystemPrompt(user)}, ...user.aiHistory, {role: "user", content: userText}];

    const resp = await openai.chat.completions.create({
        model: OPENAI_MODEL,
        messages,
        temperature: 0.4,
        max_tokens: 220,
    });

    return resp.choices?.[0]?.message?.content?.trim() || `Entendi. 🙂\n\n${menuMessage()}`;
}

// =====================================
// Parse "Nome, CPF, DOB"
// =====================================
function extractPersonTriple(text) {
    const parts = (text || "").split(",").map((s) => s.trim()).filter(Boolean);
    if (parts.length < 3) return {ok: false};

    const name = parts[0];
    const cpf = parts[1];
    const dob = parts.slice(2).join(", "); // se tiver vírgula extra

    return {ok: true, name, cpf, dob};
}

function splitPeopleLines(text) {
    // quebra por linhas; aceita 1..N pessoas
    return (text || "")
        .split(/\n+/)
        .map((l) => l.trim())
        .filter(Boolean);
}

function tryParseManyPeople(text) {
    const lines = splitPeopleLines(text);
    const people = [];

    for (const line of lines) {
        const triple = extractPersonTriple(line);
        if (!triple.ok) continue;
        people.push(triple);
    }

    return people; // pode ser []
}

function hasDuplicateCPF(peopleData, cpfDigits) {
    const c = onlyDigits(cpfDigits);
    return peopleData.some((p) => onlyDigits(p.cpf) === c);
}


function maskCPF(cpfRaw) {
    const cpf = onlyDigits(cpfRaw);
    if (cpf.length !== 11) return cpfRaw;
    return `${cpf.slice(0, 3)}.***.***-${cpf.slice(9, 11)}`;
}

// =====================================
// FLUXO (MENU + RESERVA + PAGAMENTO SIMULADO)
// =====================================
async function sendWelcome(chatId, chat, user) {
    const saudacao = getGreeting();
    await simulateTyping(chat, 900);

    const msg = `${saudacao}! 👋\n\nSou o atendimento do *Monã – Terra Sem Males* 🌿\n\n${menuMessage()}`;
    pushHistory(user, "assistant", msg);
    return safeSend(chatId, msg);
}

async function handleMenuFlow(chatId, chat, rawText) {
    const user = getUser(chatId);
    const lead = user.lead;

    const text = normalizeText(rawText);
    const key = normalizeKey(text);

    // ========= retorno do site: "PAGUEI <token>"
    // ========= retorno do site / manual: "PAGUEI <token>"
    if (/\b(ja\s*paguei|já\s*paguei|paguei|pago)\b/i.test(text)) {
        await simulateTyping(chat, 900);

        const token = (lead.payment.token || "").trim();
        if (!token) {
            return safeSend(chatId, `Ainda não gerei o link de pagamento pra essa reserva 🙂\nQuer que eu gere agora? (✅ sim | ❌ não)`);
        }

        try {
            const resp = await fetch(`${PUBLIC_BASE_URL}/api/payments/${token}`);
            const data = await resp.json();
            const status = data?.payment?.status;

            if (status === "CONFIRMED") {
                lead.payment.status = "CONFIRMED";
                user.step = "MENU";
                return safeSend(chatId, paymentConfirmedMessage(lead));
            }
        } catch (_) {}

        return safeSend(chatId, `Ainda não apareceu como confirmado no confirmações.\n\n🔗 ${lead.payment.url}\n\nSe você pagou agora, pode levar alguns instantes. Quer que eu verifique novamente? (responda: *verificar*)`);
    }


    // atalhos universais
    if (/^(menu|oi|olá|ola|bom dia|boa tarde|boa noite)$/i.test(key)) {
        user.step = "MENU";
        return sendWelcome(chatId, chat, user);
    }

    // primeira interação
    if (user.step === "NEW") {
        user.step = "MENU";
        return sendWelcome(chatId, chat, user);
    }

    // =========================
    // MENU
    // =========================
    if (user.step === "MENU") {
        if (key === "1") {
            user.step = "QUALIFY";
            await simulateTyping(chat, 900);
            pushHistory(user, "assistant", openingMessage());
            return safeSend(chatId, openingMessage());
        }

        if (key === "2") {
            await simulateTyping(chat, 900);
            return safeSend(chatId, lodgingOfferMessage());
        }

        if (key === "3") {
            await simulateTyping(chat, 900);
            return safeSend(chatId, rulesFaqMessage());
        }

        if (key === "4") {
            user.step = "HUMAN";
            await simulateTyping(chat, 900);
            return safeSend(chatId, humanMessage());
        }

        if (key === "0") {
            await simulateTyping(chat, 700);
            return safeSend(chatId, menuMessage());
        }

        // Texto livre -> IA puxa pro fluxo
        pushHistory(user, "user", text);
        await simulateTyping(chat, 900);

        const p = tryExtractPeople(key);
        if (p) lead.people = p;

        const dateAttempt = parseAndValidateDatePT(text);
        if (dateAttempt.ok) {
            lead.date = dateAttempt.br;
            lead.dateISO = dateAttempt.iso;
        }

        const ai = await aiReply(user, text);
        pushHistory(user, "assistant", ai);
        user.step = "QUALIFY";
        return safeSend(chatId, ai);
    }

    // =========================
    // HUMANO
    // =========================
    if (user.step === "HUMAN") {
        await simulateTyping(chat, 900);
        user.step = "MENU";
        return safeSend(chatId, `Perfeito! Recebi sua mensagem. ✅\n\nDigite *0* para ver o menu novamente.`);
    }

    // =========================
    // QUALIFY (data e pessoas)
    // =========================
    if (user.step === "QUALIFY") {
        pushHistory(user, "user", text);

        const p = tryExtractPeople(key);
        if (p) lead.people = p;

        const dateAttempt = parseAndValidateDatePT(text);
        if (dateAttempt.ok) {
            lead.date = dateAttempt.br;
            lead.dateISO = dateAttempt.iso;
        } else {
            if (dateAttempt.reason === "YEAR_5_DIGITS") {
                await simulateTyping(chat, 900);
                return safeSend(chatId, `Só confirmando: você quis dizer o ano *${dateAttempt.suggestion}*? 🙂`);
            }
            if (dateAttempt.reason === "PAST_DATE") {
                await simulateTyping(chat, 900);
                return safeSend(chatId, `Essa data (*${dateAttempt.parsed}*) já passou.\nHoje é *${dateAttempt.today}*. Qual data futura você deseja?`);
            }
            if (dateAttempt.reason === "TOO_FAR") {
                await simulateTyping(chat, 900);
                return safeSend(chatId, `Essa data (*${dateAttempt.parsed}*) está bem distante.\nMe diga uma data até *${dateAttempt.max}* 🙂`);
            }
            if (dateAttempt.reason === "MISSING_YEAR") {
                await simulateTyping(chat, 900);
                return safeSend(chatId, `Perfeito 🙂 Só confirma o *ano* também?\nEx.: *01/02/2026*`);
            }
        }

        if (lead.date && lead.people) {
            user.step = "AWAIT_ACCEPT";
            await simulateTyping(chat, 900);
            return safeSend(chatId, dayUsePresentationMessage(lead.date, lead.people));
        }

        await simulateTyping(chat, 900);
        const ai = await aiReply(user, text);
        pushHistory(user, "assistant", ai);
        return safeSend(chatId, ai);
    }

    // =========================
    // AWAIT_ACCEPT (sim/não)
    // =========================
    if (user.step === "AWAIT_ACCEPT") {
        const yn = tryExtractYesNo(key);

        if (yn === false) {
            lead.acceptedDayUse = false;
            user.step = "MENU";
            await simulateTyping(chat, 900);
            return safeSend(chatId, `Sem problema 🙂\nSe quiser, me diga outra data ou digite *0* para menu.`);
        }

        if (yn === true) {
            lead.acceptedDayUse = true;
            user.step = "OFFER_STAY";
            await simulateTyping(chat, 900);
            return safeSend(chatId, lodgingOfferMessage());
        }

        await simulateTyping(chat, 900);
        return safeSend(chatId, `Só confirmando: está de acordo com o *Day Use privativo*? ✅ sim | ❌ não`);
    }

    // =========================
    // OFFER_STAY
    // =========================
    if (user.step === "OFFER_STAY") {
        const yn = tryExtractYesNo(key);
        const suite = tryExtractSuite(key);

        if (yn === false) {
            lead.wantsStay = false;
            user.step = "COLLECT_PERSON_DATA";
            lead.peopleData = [];
            lead.currentPersonIndex = 0;

            await simulateTyping(chat, 900);
            return safeSend(chatId, askPersonData(lead.currentPersonIndex, lead.people));
        }

        if (yn === true) {
            lead.wantsStay = true;
            if (suite) lead.suiteChoice = suite;

            if (!lead.suiteChoice) {
                await simulateTyping(chat, 900);
                return safeSend(
                    chatId,
                    `Perfeito ✅ Qual suíte?\n1) *R$ 500/noite* (1 cama)\n2) *R$ 800/noite* (2 camas)\n\nResponda *1* ou *2*.`
                );
            }

            user.step = "COLLECT_PERSON_DATA";
            lead.peopleData = [];
            lead.currentPersonIndex = 0;

            await simulateTyping(chat, 900);
            return safeSend(chatId, askPersonData(lead.currentPersonIndex, lead.people));
        }

        if (key === "1") {
            lead.wantsStay = true;
            lead.suiteChoice = "suite_500";
            user.step = "COLLECT_PERSON_DATA";
            lead.peopleData = [];
            lead.currentPersonIndex = 0;

            await simulateTyping(chat, 900);
            return safeSend(chatId, askPersonData(lead.currentPersonIndex, lead.people));
        }

        if (key === "2") {
            lead.wantsStay = true;
            lead.suiteChoice = "suite_800";
            user.step = "COLLECT_PERSON_DATA";
            lead.peopleData = [];
            lead.currentPersonIndex = 0;

            await simulateTyping(chat, 900);
            return safeSend(chatId, askPersonData(lead.currentPersonIndex, lead.people));
        }

        await simulateTyping(chat, 900);
        return safeSend(chatId, `Você gostaria de incluir hospedagem? ✅ sim | ❌ não`);
    }

    // =========================
    // COLLECT_PERSON_DATA
    // =========================
    if (user.step === "COLLECT_PERSON_DATA") {
        await simulateTyping(chat, 900);

        const candidates = tryParseManyPeople(text);

        // se não veio no formato esperado, pede exemplo
        if (!candidates.length) {
            return safeSend(chatId, `Pode me enviar assim, tudo junto:\nNome, CPF, Data de nascimento (DD/MM/AAAA)\n\nVocê pode enviar *várias pessoas*, uma por linha.`);
        }

        for (const triple of candidates) {
            if (lead.peopleData.length >= Number(lead.people)) break;

            if (!triple.name || !triple.cpf || !triple.dob) continue;

            if (!isValidCPF(triple.cpf)) {
                return safeSend(chatId, `Esse CPF parece inválido 🙂\nRevise e envie novamente no formato:\nNome, CPF, Data de nascimento`);
            }

            if (hasDuplicateCPF(lead.peopleData, triple.cpf)) {
                return safeSend(chatId, `Esse CPF já foi usado nessa reserva 🙂\nEnvie um CPF diferente para a Pessoa ${lead.peopleData.length + 1}.`);
            }

            const dob = parseDOB_BR(triple.dob);
            if (!dob.ok) {
                return safeSend(chatId, `Data de nascimento inválida 🙂\nUse DD/MM/AAAA (ex.: 10/05/1998).`);
            }

            lead.peopleData.push({
                name: triple.name,
                cpf: onlyDigits(triple.cpf),
                cpfMasked: maskCPF(triple.cpf),
                dobBR: dob.br,
                dobISO: dob.iso,
            });
        }

        // completou?
        if (lead.peopleData.length < Number(lead.people)) {
            const idx = lead.peopleData.length;
            return safeSend(chatId, askPersonData(idx, lead.people));
        }

        // terminou -> revisar
        user.step = "REVIEW_PURCHASE";
        return safeSend(chatId, reviewDataMessage(lead));
    }

    // =========================
    // REVIEW_PURCHASE (confirmar dados e compra)
    // =========================
    // =========================
    // REVIEW_PURCHASE (confirmar dados e compra)
    // =========================
    if (user.step === "REVIEW_PURCHASE") {
        const yn = tryExtractYesNo(key);

        if (yn === true) {
            const token = makeToken();
            lead.payment.token = token;
            lead.payment.status = "LINK_SENT";

            // ✅ cria/atualiza a reserva no servidor (token = reservationId)
            // ✅ e manda o cliente do grupo (pessoa 1) como pagador
            const payer = lead.peopleData?.[0];

            try {
                const resp = await fetch(`${PUBLIC_BASE_URL}/api/asaas/create-payment`, {
                    method: "POST",
                    headers: {"Content-Type": "application/json"},
                    body: JSON.stringify({
                        reservationId: token,
                        customer: {
                            name: payer?.name || "Cliente Monã",
                            cpfCnpj: payer?.cpf || "",     // CPF sem máscara
                            mobilePhone: "",               // opcional
                            billingType: "PIX",            // PIX ou BOLETO
                        },
                        // opcional: mandar leadSummary para salvar (se quiser no server)
                        leadSummary: {
                            chatId,
                            date: lead.date,
                            dateISO: lead.dateISO,
                            people: lead.people,
                            wantsStay: lead.wantsStay,
                            suiteChoice: lead.suiteChoice,
                            peopleData: lead.peopleData,
                        },
                    }),
                });

                const data = await resp.json();
                if (!data.ok) throw new Error(data.error || "asaas create-payment failed");

                // ✅ link REAL do Asaas
                lead.payment.url = data.invoiceUrl || data?.payment?.invoiceUrl || null;

                if (!lead.payment.url) throw new Error("invoiceUrl vazio (Asaas)");

            } catch (e) {
                console.error("❌ erro criando cobrança Asaas:", e);
                // fallback: mantém um link interno, mas ideal é não cair aqui
                lead.payment.url = `${PUBLIC_BASE_URL}/pay/${token}`;
            }

            user.step = "WAIT_PAYMENT";
            await simulateTyping(chat, 900);
            return safeSend(chatId, paymentLinkMessage(lead));
        }

        await simulateTyping(chat, 900);
        return safeSend(chatId, `Você confirma os dados e a compra? ✅ sim | ❌ não`);
    }

    // =========================
    // WAIT_PAYMENT
    // =========================
    if (user.step === "WAIT_PAYMENT") {
        await simulateTyping(chat, 900);

        // consulta status real
        if (lead.payment.token) {
            try {
                const resp = await fetch(`${PUBLIC_BASE_URL}/api/payments/${lead.payment.token}`);
                const data = await resp.json();
                const status = data?.payment?.status;

                if (status === "CONFIRMED") {
                    lead.payment.status = "CONFIRMED";
                    user.step = "MENU";
                    return safeSend(chatId, paymentConfirmedMessage(lead));
                }
            } catch (_) {}
        }

        return safeSend(
            chatId,
            `Perfeito 🙂\n` +
            `Quando o pagamento for confirmado, eu te aviso por aqui.\n\n` +
            `🔗 ${lead.payment.url}\n\n` +
            `Se você já pagou, responda: *já paguei*`
        );
    }

    // fallback
    user.step = "MENU";
    return sendWelcome(chatId, chat, user);
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
            await simulateTyping(chat, 700);
            return safeSend(chatId, `Recebi sua mensagem 🙂\nSe quiser, digite *0* para ver o menu.`);
        }

        await handleMenuFlow(chatId, chat, text);
    } catch (error) {
        console.error("❌ Erro no processamento:", error);
    }
});

// =====================================
// PROCESS SAFETY
// =====================================
process.on("unhandledRejection", (reason) => console.error("❌ unhandledRejection:", reason));
process.on("uncaughtException", (err) => console.error("❌ uncaughtException:", err));