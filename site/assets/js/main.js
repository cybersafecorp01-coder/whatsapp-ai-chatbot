// Validação de Formulários
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Validação básica já é feita pelo HTML5 (required, type=email, etc)
            // Aqui você pode adicionar validações customizadas se necessário
        });
    });

    // Máscara para CPF
    const cpfInputs = document.querySelectorAll('input[name="cpf"]');
    cpfInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);

            if (value.length > 8) {
                value = value.slice(0, 3) + '.' + value.slice(3, 6) + '.' + value.slice(6, 9) + '-' + value.slice(9, 11);
            } else if (value.length > 5) {
                value = value.slice(0, 3) + '.' + value.slice(3, 6) + '.' + value.slice(6);
            } else if (value.length > 2) {
                value = value.slice(0, 3) + '.' + value.slice(3);
            }

            e.target.value = value;
        });
    });

    // Máscara para Telefone
    const phoneInputs = document.querySelectorAll('input[name="telefone"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);

            if (value.length > 6) {
                value = '(' + value.slice(0, 2) + ') ' + value.slice(2, 7) + '-' + value.slice(7);
            } else if (value.length > 2) {
                value = '(' + value.slice(0, 2) + ') ' + value.slice(2);
            }

            e.target.value = value;
        });
    });

    // Validação de senhas
    const confirmaSenhaInput = document.querySelector('input[name="confirma_senha"]');
    if (confirmaSenhaInput) {
        confirmaSenhaInput.addEventListener('blur', function(e) {
            const senha = document.querySelector('input[name="senha"]').value;
            if (e.target.value && e.target.value !== senha) {
                e.target.style.borderColor = '#e74c3c';
                e.target.title = 'As senhas não conferem';
            } else {
                e.target.style.borderColor = '#ddd';
                e.target.title = '';
            }
        });
    }

    // Validação de datas de reserva
    const dataCheckoutInput = document.querySelector('input[name="data_checkout"]');
    const dataCheckinInput = document.querySelector('input[name="data_checkin"]');

    if (dataCheckoutInput && dataCheckinInput) {
        dataCheckoutInput.addEventListener('change', function() {
            const checkin = new Date(dataCheckinInput.value);
            const checkout = new Date(dataCheckoutInput.value);

            if (checkout <= checkin) {
                dataCheckoutInput.style.borderColor = '#e74c3c';
                dataCheckoutInput.title = 'Data de checkout deve ser após o check-in';
            } else {
                dataCheckoutInput.style.borderColor = '#ddd';
                dataCheckoutInput.title = '';

                // Calcular noites
                const dias = Math.ceil((checkout - checkin) / (1000 * 60 * 60 * 24));
                console.log('Noites selecionadas:', dias);
            }
        });
    }
});

// Smooth scroll para links internos
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href !== '#') {
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        }
    });
});

// Função para copiar texto
function copiarTexto(texto) {
    navigator.clipboard.writeText(texto).then(() => {
        alert('Copiado para a área de transferência!');
    }).catch(err => {
        console.error('Erro ao copiar:', err);
    });
}

// Função para formatar moeda
function formatarMoeda(valor) {
    return 'R$ ' + parseFloat(valor).toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Função para formatar data
function formatarData(data) {
    const d = new Date(data);
    const dia = String(d.getDate()).padStart(2, '0');
    const mes = String(d.getMonth() + 1).padStart(2, '0');
    const ano = d.getFullYear();
    return dia + '/' + mes + '/' + ano;
}

// Feedback visual em botões
document.querySelectorAll('button').forEach(button => {
    button.addEventListener('click', function() {
        if (this.type === 'submit') return;

        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
        this.disabled = true;

        setTimeout(() => {
            this.innerHTML = originalText;
            this.disabled = false;
        }, 2000);
    });
});

// Mensagens de sucesso/erro (se existirem)
window.addEventListener('load', function() {
    const params = new URLSearchParams(window.location.search);

    if (params.has('success')) {
        console.log('Operação realizada com sucesso!');
    }

    if (params.has('error')) {
        const errorMsg = params.get('error');
        console.log('Erro:', errorMsg);
    }
});