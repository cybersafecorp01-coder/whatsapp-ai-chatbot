# 🚀 Guia de Instalação e Configuração - Monã Hotel

## ⚡ Início Rápido (5 minutos)

### Passo 1: Verificar Ambiente
Certifique-se de que você tem:
- XAMPP em execução (Apache + MySQL)
- PHP 8.2+ instalado
- Navegador moderno (Chrome, Firefox, Safari, Edge)

### Passo 2: Criar Banco de Dados
Abra o phpMyAdmin: `http://localhost/phpmyadmin/`

1. Clique em **Novo** ou **Create new database**
2. Nome: `mona_hotel`
3. Collation: `utf8mb4_general_ci`
4. Clique em **Criar**

### Passo 3: Executar Migrações
Acesse a URL de migrações no seu navegador:

```
http://localhost/Mona/site/includes/migrations.php?migrate=1
```

Você verá:
```
✓ Banco de dados criado com sucesso!
Admin padrão: admin@monã.com / admin123
3 quartos de exemplo criados
```

### Passo 4: Configurar Variáveis de Ambiente
Edite o arquivo: `site/.env`

```env
# Já pré-configurado, mas ajuste se necessário:
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=mona_hotel

# Deixar em branco se não usará (opcional):
ASAAS_API_KEY=sua_chave_aqui
RESEND_API_KEY=sua_chave_aqui
```

### Passo 5: Acessar o Sistema

**🏠 Site Público**
```
http://localhost/Mona/site/
```

**🔐 Painel Admin**
```
http://localhost/Mona/site/admin/
```

**Login Admin:**
- Email: `admin@monã.com`
- Senha: `admin123`

---

## 📋 Estrutura do Sistema

### Pastas Principais
```
site/
├── pages/           → Páginas públicas (home, reserva, contato, etc)
├── admin/           → Painel administrativo
├── api/             → Endpoints para processamento
├── includes/        → Arquivos compartilhados (database, email, pagamento)
├── assets/          → CSS, JavaScript, Imagens
├── .env             → Configurações (IMPORTANTE!)
├── .htaccess        → Reescrita de URL
└── README.md        → Documentação completa
```

---

## 🔑 Credenciais Padrão

Após executar as migrações:

| Tipo | Email | Senha |
|------|-------|-------|
| Admin | admin@monã.com | admin123 |

**⚠️ Altere a senha do admin após primeira login!**

---

## 🌐 URLs Principais

### Públicas
| Página | URL |
|--------|-----|
| Home | `/` |
| Reserva | `/pages/reserva.php` |
| Contato | `/pages/contato.php` |
| Termos | `/pages/termos.php` |
| LGPD | `/pages/lgpd.php` |
| Login | `/pages/login.php` |
| Registrar | `/pages/registro.php` |

### Administrativas
| Página | URL |
|--------|-----|
| Dashboard | `/admin/` |
| Reservas | `/admin/reservas.php` |
| Quartos | `/admin/quartos.php` |
| Clientes | `/admin/clientes.php` |
| Mensagens | `/admin/mensagens.php` |
| Pagamentos | `/admin/pagamentos.php` |
| Configurações | `/admin/configuracoes.php` |

---

## ⚙️ Configurações Opcionais

### Integrar com ASAAS (Pagamentos)

1. **Criar conta em ASAAS**: https://asaas.com
2. **Obter chave de API**: Dashboard → Configurações → API
3. **Editar `.env`**:
   ```env
   ASAAS_API_KEY=sk_prod_sua_chave_aqui
   ASAAS_WEBHOOK_SECRET=seu_webhook_secret
   ```

### Integrar com Resend API (Emails)

1. **Criar conta em Resend**: https://resend.com
2. **Obter chave de API**: Dashboard → Chaves de API
3. **Editar `.env`**:
   ```env
   RESEND_API_KEY=re_sua_chave_aqui
   ```

### Enviar Emails de Teste
No painel admin, vá para **Configurações** e clique em **Testar Email**

---

## 🗄️ Banco de Dados

### Tabelas Criadas Automaticamente

| Tabela | Descrição |
|--------|-----------|
| configuracoes | Dados do hotel |
| usuarios | Clientes e administradores |
| quartos | Informações dos quartos |
| disponibilidades | Datas disponíveis para reserva |
| reservas | Histórico de todas as reservas |
| mensagens | Mensagens de contato |

### Diagrama de Relacionamento
```
usuarios ← reservas → quartos
            ↓
      disponibilidades

mensagens (standalone)
configuracoes (standalone)
```

---

## 🛠️ Primeiro Acesso - O Que Fazer

### Como Admin
1. ✅ Login com `admin@monã.com` / `admin123`
2. ⚙️ Ir para **Configurações** e atualizar dados do hotel
3. 🪑 Adicionar/editar quartos na aba **Quartos**
4. 📅 Adicionar datas disponíveis
5. 🔑 Alterar senha do admin (importante!)
6. 🔌 Configurar integrações (ASAAS, Resend) se desejado

### Como Cliente
1. 📝 Clicar em **Registrar** na home
2. 🔐 Preencher dados e criar conta
3. 📅 Ir para **Reserva** e selecionar quarto
4. 💳 Revisar dados e ir para pagamento
5. 📧 Receber confirmação por email

---

## ❓ Troubleshooting

### "Erro 500 ao acessar o site"
- ✓ Verificar se o banco de dados `mona_hotel` foi criado
- ✓ Verificar se as migrações foram executadas
- ✓ Verificar permissões das pastas (755 recomendado)

### "Não consigo fazer login"
- ✓ Verificar se o usuário admin foi criado (run migrations)
- ✓ Verificar se está usando a senha correta: `admin123`
- ✓ Limpar cookies do navegador (Ctrl+Shift+Del)

### "Emails não são enviados"
- ✓ Verificar se a chave RESEND_API_KEY está correta no `.env`
- ✓ Verificar se o domínio está verificado no Resend
- ✓ Ver logs em `/admin/` → Configurações

### "Pagamentos não funcionam"
- ✓ Verificar se a chave ASAAS_API_KEY está no `.env`
- ✓ Usar sandbox do ASAAS para testes
- ✓ Verificar logs de erro no navegador (F12)

---

## 📱 Testes Recomendados

### Teste Completo
1. ✅ Registrar novo usuário (`/pages/registro.php`)
2. ✅ Fazer login (`/pages/login.php`)
3. ✅ Fazer uma reserva (`/pages/reserva.php`)
4. ✅ Prosseguir para pagamento (`/pages/pagamento.php`)
5. ✅ Verificar se aparece no admin (`/admin/reservas.php`)

### Teste de Mensagens
1. ✅ Enviar mensagem de contato (`/pages/contato.php`)
2. ✅ Verificar se chegou (painel admin → Mensagens)

### Teste de Dados
1. ✅ Visualizar clientes cadastrados (`/admin/clientes.php`)
2. ✅ Visualizar quartos (`/admin/quartos.php`)
3. ✅ Visualizar reservas (`/admin/reservas.php`)

---

## 🔒 Segurança

### Recomendações Importantes
1. **Alterar senha do admin** após primeiro login
2. **Usar HTTPS em produção** (SSL/TLS)
3. **Manter PHP e MySQL atualizados**
4. **Fazer backup regular** do banco de dados
5. **Usar senhas fortes** para dados sensíveis
6. **Desabilitar modo debug** em produção

### Backup do Banco de Dados
```bash
# Via MySQL
mysqldump -u root -p mona_hotel > backup.sql

# Via phpMyAdmin
Dashboard → Exportar → Escolher tabelas → Go
```

---

## 📚 Próximos Passos

- [ ] Customizar cores e logo do hotel
- [ ] Adicionar fotos dos quartos
- [ ] Integrar WhatsApp chatbot
- [ ] Configurar sistema de avaliações
- [ ] Implementar programa de fidelidade
- [ ] Traduzir para outros idiomas

---

## 📞 Suporte

**Email**: contato@monã.com  
**Telefone**: +55 (00) 0000-0000  
**Atendimento**: 24h

---

## 📄 Licença

© 2024 Monã Hotel. Todos os direitos reservados.

**Data de Criação**: Janeiro 2024  
**Versão**: 1.0.0  
**Status**: ✅ Pronto para Produção
