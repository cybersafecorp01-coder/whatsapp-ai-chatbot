# 🎨 Guia de Customização - Monã Hotel

Este guia ajuda você a personalizar o sistema de hospedagem Monã Hotel para suas necessidades específicas.

---

## 🎯 Mudanças Rápidas

### 1. Alterar Nome do Hotel

**Arquivo**: `site/.env`
```env
APP_NAME=Seu Hotel Aqui
APP_EMAIL=seu-email@seuhotel.com
```

**Arquivo**: `site/pages/home.php` (linha ~1)
```html
<title>Seu Hotel Aqui - Hospedagem de Luxo</title>
```

**Arquivo**: `site/admin/index.php` (linha ~40)
```html
<div class="logo">
    <h2>Seu Hotel <span>Admin</span></h2>
</div>
```

---

### 2. Alterar Cores

**Arquivo**: `site/assets/css/style.css` (linhas 1-20)

Procure por:
```css
:root {
    --cor-primaria: #1a3a2f;      /* Verde escuro */
    --cor-secundaria: #2d5f54;    /* Verde claro */
    --cor-branco: #ffffff;
    /* ... */
}
```

E altere as cores HEX para suas cores preferidas:
- `#1a3a2f` → Sua cor primária
- `#2d5f54` → Sua cor secundária
- `#f0e68c` → Sua cor destaque (gold/ouro)

**Arquivo**: `site/assets/css/admin.css` (linhas 1-20)

Mesma estrutura de cores para o painel admin.

---

### 3. Alterar Logo/Marca

Abra cada arquivo `.php` e procure por:

```html
<h1>Monã <span>Hotel</span></h1>
```

Altere para:
```html
<h1>Seu <span>Hotel</span></h1>
```

Ou substitua por uma imagem:
```html
<img src="../assets/images/logo.png" alt="Logo" style="height: 40px;">
```

---

### 4. Alterar Informações de Contato

**Arquivo**: `site/.env`
```env
APP_EMAIL=novo-email@hotel.com
```

**Arquivo**: `site/admin/configuracoes.php`

No formulário, atualize:
- Nome do hotel
- Email
- Telefone
- Endereço
- Descrição

Estes dados aparecerão em todo o site automaticamente.

---

## 🏨 Gerenciar Quartos

### Adicionar Novo Quarto

1. Acesse: `http://localhost/Mona/site/admin/quartos.php`
2. Clique em **Adicionar Quarto**
3. Preencha os dados:
   - Nome (ex: "Suíte Master")
   - Tipo (simples, duplo, suite, suite_luxo)
   - Capacidade (1-4 pessoas)
   - Preço por noite (ex: 500.00)
   - Descrição
   - Quantidade de unidades

4. Clique em **Salvar**

### Adicionar Datas Disponíveis

Após criar um quarto:

1. Ainda em **Quartos**
2. Seção "Adicionar Datas Disponíveis"
3. Selecione o quarto
4. Selecione a data
5. Clique em **Adicionar**

**Nota**: As datas aparecem automaticamente para os clientes na página de reserva.

---

## 📧 Configurar Emails

### Com Resend API

1. Crie conta em: https://resend.com
2. Obtenha sua API Key
3. Edite `site/.env`:
   ```env
   RESEND_API_KEY=re_sua_chave_aqui
   ```

4. Teste enviando uma mensagem de contato

### Customizar Modelo de Email

**Arquivo**: `site/includes/email.php`

Procure por `templateConfirmacaoReserva()` (linha ~95)

Personalize o HTML do email:
```php
private function templateConfirmacaoReserva($usuario, $reserva, $quarto) {
    return "
        <h2>Confirmação de Reserva</h2>
        <p>Olá {$usuario['nome']},</p>
        <!-- Customize aqui -->
    ";
}
```

---

## 💳 Configurar Pagamentos

### Com ASAAS

1. Crie conta em: https://asaas.com
2. Obtenha sua API Key e Webhook Secret
3. Edite `site/.env`:
   ```env
   ASAAS_API_KEY=sk_prod_sua_chave
   ASAAS_WEBHOOK_SECRET=seu_webhook_secret
   ```

4. Configure o webhook em ASAAS:
   - URL: `http://seu-dominio.com/site/includes/payment.php`
   - Eventos: `payment_received`, `payment_failed`

### Modo Teste (Sandbox)

Use as credenciais de teste do ASAAS:
```env
ASAAS_API_KEY=sk_sandbox_sua_chave_teste
```

**Cartões de teste**:
- Sucesso: `4111 1111 1111 1111` com CVV `123`
- Falha: `5555 5555 5555 4444` com CVV `123`

---

## 📝 Personalizar Páginas Legais

### Editar Termos e Condições

**Arquivo**: `site/pages/termos.php`

Procure por `<h2>1. Definições</h2>` e customize o conteúdo conforme necessário.

### Editar Política LGPD

**Arquivo**: `site/pages/lgpd.php`

Atualize as seções com informações do seu hotel.

### Editar Política de Privacidade

**Arquivo**: `site/pages/politica-privacidade.php`

Customize com políticas específicas da sua empresa.

---

## 🎨 Alterar Design

### Mudar Tipografia

**Arquivo**: `site/assets/css/style.css` (linha ~15)

```css
--fonte-principal: Arial, sans-serif;
```

Altere para outras fontes como:
- `Georgia, serif` - Elegante
- `'Times New Roman', serif` - Clássica
- `Verdana, sans-serif` - Moderna

### Adicionar Imagens de Fundo

**Arquivo**: `site/pages/home.php` (linha ~50)

```html
<section id="home" class="hero" style="background-image: url('../assets/images/hero.jpg');">
```

### Customizar Estrutura da Landing Page

**Arquivo**: `site/pages/home.php`

Adicione seções customizadas:
```html
<section class="gallery">
    <h2>Galeria</h2>
    <!-- Adicione suas imagens -->
</section>
```

E adicione CSS em `style.css`:
```css
.gallery {
    padding: 80px 20px;
    background: var(--cor-branco);
}
```

---

## 🔧 Modificar Banco de Dados

### Adicionar Novos Campos a Usuários

1. Abra phpMyAdmin
2. Vá para tabela `usuarios`
3. Clique em **Estrutura**
4. Clique em **Adicionar 1 coluna**
5. Preencha os dados
6. Salve

**Exemplo**: Adicionar campo "data_nascimento"
```sql
ALTER TABLE usuarios ADD data_nascimento DATE NULL;
```

### Adicionar Novo Campo a Quartos

```sql
ALTER TABLE quartos ADD wifi_gratis BOOLEAN DEFAULT 1;
ALTER TABLE quartos ADD ar_condicionado BOOLEAN DEFAULT 1;
ALTER TABLE quartos ADD tv_plasma BOOLEAN DEFAULT 1;
```

Depois, customize as páginas para usar esses campos.

---

## 🤖 Adicionar Novas Funcionalidades

### Adicionar Sistema de Reviews

1. Crie nova tabela:
```sql
CREATE TABLE avaliacoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reserva_id INT NOT NULL,
    nota INT (1-5),
    comentario TEXT,
    data_avaliacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id)
);
```

2. Crie página `pages/avaliar.php`
3. Adicione no admin `admin/avaliacoes.php`

### Adicionar Promoções

1. Crie tabela:
```sql
CREATE TABLE promocoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(20) UNIQUE,
    desconto DECIMAL(5,2),
    data_inicio DATE,
    data_fim DATE,
    ativa BOOLEAN DEFAULT 1
);
```

2. Modifique `api/do-reserve.php` para aplicar desconto

---

## 🌐 Deploy para Produção

### Checklist Pré-Deploy

- [ ] Alterar senha do admin
- [ ] Configurar `.env` com dados reais
- [ ] Usar HTTPS (SSL/TLS)
- [ ] Fazer backup do banco de dados
- [ ] Testar todos os formulários
- [ ] Testar sistema de pagamento
- [ ] Verificar todos os links
- [ ] Testar responsividade em mobile
- [ ] Otimizar imagens

### Passos para Deploy

1. **FTP/SFTP**: Upload dos arquivos para servidor
2. **Banco de dados**: Exportar e importar no servidor
3. **Configurar `.env`**: Com dados do servidor
4. **Testar**: Faça um teste completo
5. **DNS**: Apontar domínio
6. **SSL**: Instalar certificado HTTPS

---

## 📱 Otimizar para Mobile

### Testar Responsividade

Use o DevTools do Chrome (F12):
1. Clique no ícone de dispositivo móvel
2. Selecione diferentes tamanhos
3. Verifique layout e funcionalidades

### Melhorias Recomendadas

```css
/* Adicionar em style.css para melhor mobile */
@media (max-width: 480px) {
    .navbar-menu {
        display: none; /* Transformar em menu hamburger */
    }
    
    .hero h2 {
        font-size: 24px;
    }
}
```

---

## 🐛 Solução de Problemas

### Formulários não enviam emails

- [ ] Verificar chave RESEND_API_KEY
- [ ] Verificar se o servidor permite cURL
- [ ] Verificar logs de erro (F12 → Console)

### Pagamentos não funcionam

- [ ] Verificar chave ASAAS_API_KEY
- [ ] Verificar modo sandbox vs produção
- [ ] Verificar dados do cartão de teste

### Banco de dados com erro

- [ ] Verificar credenciais em `.env`
- [ ] Verificar se tabelas foram criadas (migrations)
- [ ] Fazer backup antes de qualquer alteração

---

## 📚 Recursos Úteis

- **PHP Documentation**: https://www.php.net/docs.php
- **MySQL Documentation**: https://dev.mysql.com/doc/
- **CSS Reference**: https://developer.mozilla.org/en-US/docs/Web/CSS
- **JavaScript Guide**: https://developer.mozilla.org/en-US/docs/Web/JavaScript

---

## 💬 Suporte

Se tiver dúvidas:
1. Consulte a documentação (README.md, SETUP.md)
2. Verifique os erros no console (F12)
3. Entre em contato: contato@monã.com

---

**Última atualização**: Janeiro 2024  
**Versão**: 1.0.0
