# 🏨 Monã Hotel - Sistema de Gerenciamento de Hospedagem

Sistema profissional de hospedagem com design de luxo, dashboard administrativo, integração com ASAAS para pagamentos e Resend API para emails.

## ✨ Características

### 👥 Para Clientes
- **Landing Page Elegante** - Design moderno e responsivo
- **Formulário de Reserva** - Interface intuitiva e prática
- **Autenticação Segura** - Login e registro com validação
- **Gerenciamento de Conta** - Histórico de reservas
- **Páginas Legais** - Termos, LGPD, Política de Privacidade

### 🔧 Para Administração
- **Dashboard Completo** - Estatísticas em tempo real
- **Gerenciar Reservas** - Visualizar, confirmar, cancelar
- **Gerenciar Quartos** - CRUD e disponibilidades
- **Gerenciar Clientes** - Lista de usuários cadastrados
- **Gerenciar Mensagens** - Formulário de contato
- **Histórico de Pagamentos** - Rastrear transações
- **Configurações** - Dados do hotel e integrações

## 🎨 Design

- **Cores**: Branco e Verde Escuro (#1a3a2f)
- **Fonte**: Arial
- **Estilo**: Luxo, elegante e profissional
- **Responsivo**: Mobile, Tablet e Desktop

## 🛠️ Tecnologias

- **Backend**: PHP 8.2+
- **Banco de Dados**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework CSS**: Bootstrap 5.3
- **Ícones**: FontAwesome 6.4
- **Segurança**: PDO, bcrypt, HTTPS

## 📋 Pré-requisitos

- XAMPP com Apache 2.4+ e PHP 8.2+
- MySQL 5.7+
- Chaves de API:
  - ASAAS (Pagamentos)
  - Resend (Emails)

## 🚀 Instalação

### 1. Extrair os arquivos
```bash
Coloque os arquivos na pasta: c:\xampp\htdocs\Mona\site\
```

### 2. Configurar Banco de Dados
Acesse: `http://localhost/Mona/site/includes/migrations.php?migrate=1`

Isso vai criar automaticamente:
- ✓ 6 tabelas no banco de dados
- ✓ Usuário admin padrão (admin@monã.com / admin123)
- ✓ 3 quartos de exemplo

### 3. Configurar Variáveis de Ambiente

Edite o arquivo `.env` na raiz do site:

```env
# Banco de Dados
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=mona_hotel

# ASAAS (Pagamentos)
ASAAS_API_KEY=sua_chave_aqui
ASAAS_WEBHOOK_SECRET=seu_webhook_secret

# Resend API (Emails)
RESEND_API_KEY=sua_chave_resend_aqui

# Configurações
APP_URL=http://localhost/Mona/site
APP_EMAIL=contato@monã.com
```

## 📍 URLs do Sistema

### Público
- **Home**: http://localhost/Mona/site/
- **Reserva**: http://localhost/Mona/site/pages/reserva.php
- **Contato**: http://localhost/Mona/site/pages/contato.php
- **Termos**: http://localhost/Mona/site/pages/termos.php
- **LGPD**: http://localhost/Mona/site/pages/lgpd.php
- **Login**: http://localhost/Mona/site/pages/login.php
- **Registro**: http://localhost/Mona/site/pages/registro.php

### Admin
- **Dashboard**: http://localhost/Mona/site/admin/
- **Reservas**: http://localhost/Mona/site/admin/reservas.php
- **Quartos**: http://localhost/Mona/site/admin/quartos.php
- **Clientes**: http://localhost/Mona/site/admin/clientes.php
- **Mensagens**: http://localhost/Mona/site/admin/mensagens.php
- **Pagamentos**: http://localhost/Mona/site/admin/pagamentos.php
- **Configurações**: http://localhost/Mona/site/admin/configuracoes.php

## 🔐 Credenciais Padrão

**Admin**
- Email: `admin@monã.com`
- Senha: `admin123`

## 📁 Estrutura de Pastas

```
site/
├── .env                    # Variáveis de ambiente
├── .htaccess               # Configurações Apache
├── index.php               # Ponto de entrada
│
├── pages/                  # Páginas públicas
│   ├── home.php
│   ├── reserva.php
│   ├── contato.php
│   ├── termos.php
│   ├── lgpd.php
│   ├── politica-privacidade.php
│   ├── login.php
│   └── registro.php
│
├── admin/                  # Painel administrativo
│   ├── index.php          # Dashboard
│   ├── reservas.php
│   ├── quartos.php
│   ├── clientes.php
│   ├── mensagens.php
│   ├── pagamentos.php
│   └── configuracoes.php
│
├── api/                    # Endpoints da API
│   ├── do-login.php
│   ├── do-register.php
│   ├── do-reserve.php
│   ├── logout.php
│   ├── send-contact.php
│   └── update-config.php
│
├── includes/               # Arquivos compartilhados
│   ├── db.php             # Classe de banco de dados
│   ├── migrations.php      # Criação de tabelas
│   ├── payment.php        # Integração ASAAS
│   └── email.php          # Integração Resend
│
└── assets/                # Recursos estáticos
    ├── css/
    │   ├── style.css      # Estilos principais
    │   └── admin.css      # Estilos admin
    ├── js/
    │   ├── main.js        # JavaScript principal
    │   └── admin.js       # JavaScript admin
    └── images/            # Imagens do site
```

## 🗄️ Banco de Dados

### Tabelas Criadas

1. **configuracoes** - Dados do hotel
2. **usuarios** - Clientes e admins
3. **quartos** - Informações dos quartos
4. **disponibilidades** - Datas disponíveis
5. **reservas** - Histórico de reservas
6. **mensagens** - Mensagens de contato

## 🔌 Integrações

### ASAAS (Pagamentos)
- Aceita cartão de crédito
- Webhooks para confirmação de pagamento
- Reembolsos automáticos

### Resend API (Emails)
- Confirmação de reserva
- Recuperação de senha
- Notificações de pagamento
- Emails de bem-vindo

## 🔒 Segurança

- ✓ Senhas com bcrypt
- ✓ Proteção CSRF
- ✓ SQL Injection prevention (PDO)
- ✓ XSS Protection
- ✓ Validação de dados
- ✓ Headers de segurança

## 📞 Suporte

**Email**: contato@monã.com
**Telefone**: +55 (00) 0000-0000
**Atendimento**: 24h, 7 dias por semana

## 📄 Documentação Adicional

- [Guia de Uso do Admin](admin-guide.md)
- [Guia de API](api-guide.md)
- [FAQ](faq.md)

## 📅 Roadmap

- [ ] Aplicativo mobile
- [ ] Sistema de reviews/avaliações
- [ ] Programa de fidelidade
- [ ] Chat ao vivo
- [ ] Múltiplos idiomas
- [ ] Integração com calendário Google

## 👨‍💼 Desenvolvido por

Monã Hotel Digital
© 2024 - Todos os direitos reservados.

---

**Última atualização**: Janeiro 2024
**Versão**: 1.0.0
