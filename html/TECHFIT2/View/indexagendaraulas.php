<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit - Agendar Aulas</title>
    <link rel="stylesheet" href="View/header-footer.css">
    <link rel="stylesheet" href="View/agendaraulas.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<!-- CABEÇALHO PADRONIZADO -->
<?php require_once 'include/header.php'; ?>

<main class="agenda-container">
    <h1>Agendar Aulas</h1>

    <div class="calendario-controles">
        <button class="nav-btn" id="mesAnterior"><i class="fas fa-chevron-left"></i></button>
        
        <div class="mes-ano-selector">
            <select id="mesSelect">
                <option value="0">Janeiro</option>
                <option value="1">Fevereiro</option>
                <option value="2">Março</option>
                <option value="3">Abril</option>
                <option value="4">Maio</option>
                <option value="5">Junho</option>
                <option value="6">Julho</option>
                <option value="7">Agosto</option>
                <option value="8">Setembro</option>
                <option value="9">Outubro</option>
                <option value="10">Novembro</option>
                <option value="11">Dezembro</option>
            </select>
            
            <select id="anoSelect"></select>
        </div>
        
        <button class="nav-btn" id="mesSeguinte"><i class="fas fa-chevron-right"></i></button>
    </div>

    <div class="calendario">
        <h2 id="mesAnoAtual"></h2>
        
        <div class="dias-semana">
            <div class="dia-semana">Dom</div>
            <div class="dia-semana">Seg</div>
            <div class="dia-semana">Ter</div>
            <div class="dia-semana">Qua</div>
            <div class="dia-semana">Qui</div>
            <div class="dia-semana">Sex</div>
            <div class="dia-semana">Sáb</div>
        </div>
        
        <div class="dias-grid" id="diasGrid"></div>
    </div>
</main>

<!-- MODAL -->
<div class="modal" id="modal">
    <div class="modal-content">
        <span class="close" id="fechar">&times;</span>
        <h2>Agendar Aula</h2>
        <div class="data-selecionada" id="dataSelecionada"></div>

        <label>Professor:</label>
        <select id="professor" required>
            <option value="">Selecione um professor</option>
            <option value="joao">João Personal</option>
            <option value="maria">Maria Funcional</option>
            <option value="carlos">Carlos Musculação</option>
        </select>

        <label>Horário:</label>
        <select id="horario" required>
            <option value="">Selecione um horário</option>
            <option value="07:00">07:00</option>
            <option value="08:00">08:00</option>
            <option value="09:00">09:00</option>
            <option value="10:00">10:00</option>
            <option value="11:00">11:00</option>
            <option value="14:00">14:00</option>
            <option value="15:00">15:00</option>
            <option value="16:00">16:00</option>
            <option value="17:00">17:00</option>
            <option value="18:00">18:00</option>
            <option value="19:00">19:00</option>
            <option value="20:00">20:00</option>
        </select>

        <button class="confirmar-btn" id="confirmarBtn">Confirmar Agendamento</button>
        
        <div class="mensagem-sucesso" id="mensagemSucesso">
            ✓ Aula agendada com sucesso!
        </div>
    </div>
</div>

<!-- RODAPÉ PADRONIZADO -->
<?php require_once 'include/footer.php'; ?>

<script>
let mesAtual = new Date().getMonth();
    let anoAtual = new Date().getFullYear();
    let diaSelecionado = null;

    // Elementos do DOM
    const mesSelect = document.getElementById('mesSelect');
    const anoSelect = document.getElementById('anoSelect');
    const mesAnoAtual = document.getElementById('mesAnoAtual');
    const diasGrid = document.getElementById('diasGrid');
    const modal = document.getElementById('modal');
    const fechar = document.getElementById('fechar');
    const dataSelecionada = document.getElementById('dataSelecionada');
    const confirmarBtn = document.getElementById('confirmarBtn');
    const professorSelect = document.getElementById('professor');
    const horarioSelect = document.getElementById('horario');
    const mensagemSucesso = document.getElementById('mensagemSucesso');

    // Inicialização
    function init() {
        popularSelectAno();
        mesSelect.value = mesAtual;
        anoSelect.value = anoAtual;
        renderizarCalendario();
        setupEventListeners();
    }

    // Popular select de anos (ano atual + 1)
    function popularSelectAno() {
        for (let i = 0; i <= 1; i++) {
            const ano = anoAtual + i;
            const option = document.createElement('option');
            option.value = ano;
            option.textContent = ano;
            anoSelect.appendChild(option);
        }
    }

    // Renderizar calendário
    function renderizarCalendario() {
        const nomesMeses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
                           'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        
        mesAnoAtual.textContent = `${nomesMeses[mesAtual]} ${anoAtual}`;
        
        // Limpar grid
        diasGrid.innerHTML = '';
        
        // Calcular primeiro dia do mês e total de dias
        const primeiroDia = new Date(anoAtual, mesAtual, 1).getDay();
        const totalDias = new Date(anoAtual, mesAtual + 1, 0).getDate();
        
        // Data de hoje
        const hoje = new Date();
        hoje.setHours(0, 0, 0, 0);
        
        // Adicionar células vazias antes do primeiro dia
        for (let i = 0; i < primeiroDia; i++) {
            const diaVazio = document.createElement('div');
            diaVazio.classList.add('dia', 'vazio');
            diasGrid.appendChild(diaVazio);
        }
        
        // Adicionar dias do mês
        for (let dia = 1; dia <= totalDias; dia++) {
            const diaElement = document.createElement('button');
            diaElement.classList.add('dia');
            diaElement.textContent = dia;
            diaElement.dataset.dia = dia;
            
            // Verificar se é hoje
            const dataAtual = new Date(anoAtual, mesAtual, dia);
            dataAtual.setHours(0, 0, 0, 0);
            
            if (dataAtual.getTime() === hoje.getTime()) {
                diaElement.classList.add('hoje');
            }
            
            // Desabilitar dias passados
            if (dataAtual < hoje) {
                diaElement.classList.add('passado');
                diaElement.disabled = true;
            } else {
                diaElement.addEventListener('click', () => abrirModal(dia));
            }
            
            diasGrid.appendChild(diaElement);
        }
    }

    // Abrir modal
    function abrirModal(dia) {
        diaSelecionado = dia;
        const nomesMeses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
                           'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        
        const diaSemana = new Date(anoAtual, mesAtual, dia).toLocaleDateString('pt-BR', { weekday: 'long' });
        dataSelecionada.textContent = `${diaSemana}, ${dia} de ${nomesMeses[mesAtual]} de ${anoAtual}`;
        
        // Resetar formulário
        professorSelect.value = '';
        horarioSelect.value = '';
        mensagemSucesso.style.display = 'none';
        
        modal.classList.add('mostrar');
    }

    // Fechar modal
    function fecharModal() {
        modal.classList.remove('mostrar');
    }

    // Confirmar agendamento
    function confirmarAgendamento() {
        const professor = professorSelect.value;
        const horario = horarioSelect.value;
        
        if (!professor || !horario) {
            alert('Por favor, selecione o professor e o horário!');
            return;
        }
        
        // Mostrar mensagem de sucesso
        mensagemSucesso.style.display = 'block';
        
        // Aqui você pode adicionar código para enviar os dados ao servidor
        console.log({
            data: `${anoAtual}-${mesAtual + 1}-${diaSelecionado}`,
            professor: professor,
            horario: horario
        });
        
        // Fechar modal após 2 segundos
        setTimeout(() => {
            fecharModal();
        }, 2000);
    }

    // Navegar entre meses
    function mesAnterior() {
        if (mesAtual === 0) {
            mesAtual = 11;
            anoAtual--;
        } else {
            mesAtual--;
        }
        atualizarSelects();
        renderizarCalendario();
    }

    function mesSeguinte() {
        if (mesAtual === 11) {
            mesAtual = 0;
            anoAtual++;
        } else {
            mesAtual++;
        }
        atualizarSelects();
        renderizarCalendario();
    }

    function atualizarSelects() {
        mesSelect.value = mesAtual;
        anoSelect.value = anoAtual;
    }

    // Event listeners
    function setupEventListeners() {
        fechar.addEventListener('click', fecharModal);
        
        window.addEventListener('click', (e) => {
            if (e.target === modal) fecharModal();
        });
        
        confirmarBtn.addEventListener('click', confirmarAgendamento);
        
        document.getElementById('mesAnterior').addEventListener('click', mesAnterior);
        document.getElementById('mesSeguinte').addEventListener('click', mesSeguinte);
        
        mesSelect.addEventListener('change', (e) => {
            mesAtual = parseInt(e.target.value);
            renderizarCalendario();
        });
        
        anoSelect.addEventListener('change', (e) => {
            anoAtual = parseInt(e.target.value);
            renderizarCalendario();
        });
    }

    // Iniciar aplicação
    init();
</script>

</body>
</html>