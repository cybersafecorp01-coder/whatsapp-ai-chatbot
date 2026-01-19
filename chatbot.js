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
        headless: true,
        args: [
            "--no-sandbox",
            "--disable-setuid-sandbox",
            "--disable-dev-shm-usage",
            "--disable-gpu",
        ],
    },
    sendSeen: false, // ❌ importante: desativa envio automático de lidas
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
    console.log("✅ WhatsApp conectado com sucesso.");
});

// =====================================
// DESCONEXÃO
// =====================================
client.on("disconnected", (reason) => {
    console.log("⚠️ Desconectado:", reason);
});

// =====================================
// INICIALIZA
// =====================================
client.initialize();

// =====================================
// FUNÇÃO DE DELAY
// =====================================
const delay = (ms) => new Promise((res) => setTimeout(res, ms));

// =====================================
// FUNÇÃO DE TYPING NATURAL
// =====================================
// =====================================
// FUNÇÃO DE TYPING NATURAL SEGURA
// =====================================
const typing = async(chat, ms = 2000) => {
    if (!chat) return;
    try {
        if (chat.sendStateTyping) await chat.sendStateTyping();
        await new Promise(res => setTimeout(res, ms));
    } catch (err) {
        console.log("⚠️ Typing ignorado:", err.message);
    }
};

// =====================================
// ENVIO DE MENSAGENS SEGURAS
// =====================================
const sendSafe = async(chat, message) => {
    try {
        await sendSafe(chat, "Aqui vai sua mensagem");
    } catch (err) {
        console.log("⚠️ Mensagem ignorada:", err.message);
    }
};

// =====================================
// FUNIL DE MENSAGENS (SOMENTE PRIVADO)
// =====================================
client.on("message", async(msg) => {
    try {
        // ❌ IGNORA GRUPOS
        if (!msg.from || msg.from.endsWith("@g.us")) return;
        const chat = await msg.getChat();
        if (chat.isGroup) return;

        const texto = msg.body ? msg.body.trim().toLowerCase() : "";

        // =====================================
        // MENSAGEM DE ACOLHIMENTO / MENU PRINCIPAL
        // =====================================
        if (/^(menu|oi|olá|ola|bom dia|boa tarde|boa noite)$/i.test(texto)) {

            const hora = new Date().getHours();
            let saudacao = "Olá";
            if (hora >= 5 && hora < 12) saudacao = "Bom dia";
            else if (hora >= 12 && hora < 18) saudacao = "Boa tarde";
            else saudacao = "Boa noite";

            await typing(chat, 2000);

            try {
                await client.sendMessage(
                    msg.from,
                    `${saudacao}! 👋\n\n` +
                    `Seja bem-vindo(a) ao Monã Amazon Lodge.\n` +
                    `Aqui, a Amazônia é sentida, não apenas visitada.\n\n` +
                    `🌿 Nosso atendimento é exclusivo e personalizado.\n` +
                    `Para guiá-lo(a) melhor, escolha uma das opções abaixo digitando o número correspondente:\n\n` +
                    `1️⃣ Conhecer o Day Use (experiência privativa de 9h às 18h30)\n` +
                    `2️⃣ Informações sobre Hospedagem nas suítes\n` +
                    `3️⃣ Tirar dúvidas sobre regras e princípios do Lodge\n` +
                    `4️⃣ Falar com nosso anfitrião (conversas sensoriais)\n` +
                    `5️⃣ Receber sugestões de datas e experiências\n\n` +
                    `Digite o número da opção desejada ou escreva sua pergunta.`
                );
            } catch (err) {
                console.log("⚠️ Erro no envio da mensagem de menu:", err.message);
            }
            return;
        }

        // =====================================
        // OPÇÕES DO MENU
        // =====================================
        const opcoes = {
            "1": `🌿 Day Use - Exclusivo para grupos fechados\n🕘 Horário: 9h às 18h30\n💰 Valor mínimo: R$ 1.000 por grupo\n🏞 Espaço 100% privativo, com vivências sensoriais e contemplativas.\n\nDeseja verificar disponibilidade para o seu grupo? (sim/não)`,
            "2": `🏡 Hospedagem nas suítes (apenas como adicional ao Day Use):\n1️⃣ Suíte 1: R$ 500 (casal + redes)\n2️⃣ Suíte 2: R$ 800 (até 4 pessoas)\n\nTodas as suítes respeitam silêncio, contato com a natureza e golden hour.\n\nDeseja reservar uma suíte ou saber mais detalhes? (sim/não)`,
            "3": `📜 Regras essenciais e princípios do Lodge:\n- Check-in: 9h | Check-out: 8h\n- Permanência extra exige novo Day Use\n- Uso consciente da floresta e do rio\n- Silêncio, sem som alto ou visitantes externos\n\nO tempo é parte da experiência. Essas regras existem para preservar a essência do Monã.\n\nDeseja saber mais sobre nossa filosofia e experiências?`,
            "4": `🤝 Nosso anfitrião está à disposição para conversar com você.\nSinta-se à vontade para contar sobre sua expectativa da visita,\nquantas pessoas virão e o tipo de experiência que deseja viver.\n\n💬 Escreva para iniciar a conversa.`,
            "5": `✨ Podemos sugerir datas próximas que preservem a exclusividade e a experiência sensorial.\nPor favor, informe uma data de interesse ou período desejado.`
        };

        if (opcoes[texto]) {
            await typing(chat);
            try {
                await client.sendMessage(msg.from, opcoes[texto]);
            } catch (err) {
                console.log(`⚠️ Erro no envio da opção ${texto}:`, err.message);
            }
            return;
        }

        // =====================================
        // RESPOSTAS GERAIS
        // =====================================
        await typing(chat);
        try {
            await client.sendMessage(
                msg.from,
                `🌿 Que interessante! 👀\nEstou aqui para ajudá-lo(a) a viver a experiência exclusiva do Monã.\n\nVocê pode digitar "menu" para ver novamente as opções disponíveis.`
            );
        } catch (err) {
            console.log("⚠️ Erro no envio da resposta geral:", err.message);
        }

    } catch (error) {
        console.error("❌ Erro no processamento da mensagem:", error);
    }
});