// Admin Dashboard Scripts

document.addEventListener('DOMContentLoaded', function() {
    // Filtro de status em tabelas
    const filterSelect = document.getElementById('filtro-status');
    if (filterSelect) {
        filterSelect.addEventListener('change', function(e) {
            const status = e.target.value;
            const rows = document.querySelectorAll('table tbody tr');

            rows.forEach(row => {
                if (status === '') {
                    row.style.display = '';
                } else {
                    const badges = row.querySelectorAll('.badge');
                    let found = false;
                    badges.forEach(badge => {
                        if (badge.textContent.toLowerCase().includes(status)) {
                            found = true;
                        }
                    });
                    row.style.display = found ? '' : 'none';
                }
            });
        });
    }

    // Busca em tabelas
    const searchInputs = document.querySelectorAll('input[type="search"]');
    searchInputs.forEach(input => {
        input.addEventListener('keyup', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('table tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    });

    // Confirmação para ações perigosas
    document.querySelectorAll('.btn-danger').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (this.dataset.confirm !== 'false') {
                e.preventDefault();
                const action = this.dataset.action || 'esta ação';
                if (!confirm('Tem certeza que deseja fazer ' + action + '?')) {
                    return false;
                }
            }
        });
    });

    // Tooltip em ícones
    document.querySelectorAll('[data-tooltip]').forEach(el => {
        el.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.dataset.tooltip;
            tooltip.style.position = 'absolute';
            tooltip.style.background = '#333';
            tooltip.style.color = '#fff';
            tooltip.style.padding = '5px 10px';
            tooltip.style.borderRadius = '3px';
            tooltip.style.fontSize = '12px';
            tooltip.style.whiteSpace = 'nowrap';
            tooltip.style.pointerEvents = 'none';
            tooltip.style.zIndex = '1000';

            document.body.appendChild(tooltip);

            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
            tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';

            this.addEventListener('mouseleave', function() {
                tooltip.remove();
            });
        });
    });

    // Alternar visibilidade de seções
    document.querySelectorAll('[data-toggle]').forEach(el => {
        el.addEventListener('click', function() {
            const target = document.querySelector(this.dataset.toggle);
            if (target) {
                target.style.display = target.style.display === 'none' ? '' : 'none';
            }
        });
    });
});

// Função para animar números (contadores)
function animarNumero(elemento, final, duracao = 1000) {
    const inicio = parseInt(elemento.textContent);
    const incremento = (final - inicio) / (duracao / 16);
    let atual = inicio;

    const interval = setInterval(() => {
        atual += incremento;
        if (atual >= final) {
            elemento.textContent = final;
            clearInterval(interval);
        } else {
            elemento.textContent = Math.floor(atual);
        }
    }, 16);
}

// Animar stat cards ao carregar
window.addEventListener('load', function() {
    document.querySelectorAll('.stat-info h3').forEach(el => {
        const valor = parseInt(el.textContent);
        animarNumero(el, valor, 1500);
    });
});

// Exportar tabela como CSV
function exportarCSV(tabela, nomeArquivo = 'dados.csv') {
    let csv = [];

    // Cabeçalhos
    const headers = [];
    tabela.querySelectorAll('thead th').forEach(th => {
        headers.push('"' + th.textContent.trim() + '"');
    });
    csv.push(headers.join(','));

    // Dados
    tabela.querySelectorAll('tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach(td => {
            row.push('"' + td.textContent.trim().replace(/"/g, '""') + '"');
        });
        csv.push(row.join(','));
    });

    // Criar download
    const elemento = document.createElement('a');
    elemento.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv.join('\n'));
    elemento.download = nomeArquivo;
    elemento.click();
}

// Imprimir tabela
function imprimirTabela(tabela) {
    const printWindow = window.open('', '', 'height=400,width=800');
    printWindow.document.write('<html><head><title>Impressão</title>');
    printWindow.document.write('<link rel="stylesheet" href="../../assets/css/admin.css">');
    printWindow.document.write('</head><body>');
    printWindow.document.write(tabela.outerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}

// Validação de formulário em tempo real
function validarFormulario(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    let valido = true;

    form.querySelectorAll('[required]').forEach(campo => {
        if (!campo.value || campo.value.trim() === '') {
            campo.style.borderColor = '#e74c3c';
            campo.title = 'Este campo é obrigatório';
            valido = false;
        } else {
            campo.style.borderColor = '#ddd';
            campo.title = '';
        }
    });

    return valido;
}