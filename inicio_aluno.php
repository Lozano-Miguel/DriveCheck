<?php
include('functions.php');

// Verificar a sessão do aluno
verificarSessaoAluno();

// Terminar a sessão se o usuário optar por sair
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    terminarSessao();
}

// Conexão com o banco de dados
$conn = dbConnection();

// Obtém o id do usuário atual
$id_utilizador = $_SESSION['id_utilizador'];

// Consulta para obter os eventos
$display_query = "
SELECT 
    aula.titulo_aula, 
    aula.data_aula, 
    aula.notas_aula, 
    tipo_aula.titulo_tipo_aula, 
    utilizador.username_utilizador AS nome_instrutor
FROM 
    aula
INNER JOIN 
    utilizador ON aula.fk_id_instrutor = utilizador.id_utilizador
INNER JOIN 
    tipo_aula ON aula.fk_tipo_aula = tipo_aula.id_tipo_aula
WHERE 
    aula.fk_id_aluno = ? 
    AND aula.eliminado_aula = 0";
$stmt = $conn->prepare($display_query);
$stmt->bind_param("i", $id_utilizador);
$stmt->execute();
$results = $stmt->get_result();

$data_arr = array();

if ($results->num_rows > 0) {
    while ($data_row = $results->fetch_assoc()) {
        $data_arr[] = array(
            'title' => $data_row['titulo_aula'],
            'start' => date("Y-m-d", strtotime($data_row['data_aula'])),
            'notas' => $data_row['notas_aula'],
            'tipo_aula' => $data_row['titulo_tipo_aula'],
            'nome_instrutor' => $data_row['nome_instrutor'],
            'color' => '#' . substr(uniqid(), -6)
        );
    }
} 

// Convertendo array PHP para JSON
$data_json = json_encode($data_arr);

// Lógica para obter o número de aulas teóricas e práticas concluídas
$query_aulas_teoricas = "SELECT COUNT(*) AS total FROM aula WHERE fk_id_aluno = ? AND fk_tipo_aula = 1 AND presenca_aula = 1";
$query_aulas_praticas = "SELECT COUNT(*) AS total FROM aula WHERE fk_id_aluno = ? AND fk_tipo_aula = 0 AND presenca_aula = 1";

$stmt_aulas_teoricas = $conn->prepare($query_aulas_teoricas);
$stmt_aulas_teoricas->bind_param("i", $id_utilizador);
$stmt_aulas_teoricas->execute();
$result_aulas_teoricas = $stmt_aulas_teoricas->get_result();
$numero_aulas_teoricas_concluidas = $result_aulas_teoricas->fetch_assoc()['total'];

$stmt_aulas_praticas = $conn->prepare($query_aulas_praticas);
$stmt_aulas_praticas->bind_param("i", $id_utilizador);
$stmt_aulas_praticas->execute();
$result_aulas_praticas = $stmt_aulas_praticas->get_result();
$numero_aulas_praticas_concluidas = $result_aulas_praticas->fetch_assoc()['total'];

// Processar a requisição de alteração de senha
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["senha_atual"]) && isset($_POST["nova_senha"]) && isset($_POST["confirmar_senha"])) {
    $senha_atual = $_POST["senha_atual"];
    $nova_senha = $_POST["nova_senha"];
    $confirmar_senha = $_POST["confirmar_senha"];
    
    // Verificar se a senha atual do usuário está correta
    $sqlVerificarSenha = "SELECT * FROM utilizador WHERE id_utilizador = ? AND password_utilizador = ?";
    $stmtVerificarSenha = $conn->prepare($sqlVerificarSenha);
    $stmtVerificarSenha->bind_param("is", $id_utilizador, $senha_atual);
    $stmtVerificarSenha->execute();
    $resultVerificarSenha = $stmtVerificarSenha->get_result();
    
    if ($resultVerificarSenha->num_rows == 1) {
        // Senha atual está correta, agora verificamos se a nova senha e a confirmação coincidem
        if ($nova_senha === $confirmar_senha) {
            // Atualizar a senha no banco de dados
            $sqlAtualizarSenha = "UPDATE utilizador SET password_utilizador = ? WHERE id_utilizador = ?";
            $stmtAtualizarSenha = $conn->prepare($sqlAtualizarSenha);
            $stmtAtualizarSenha->bind_param("si", $nova_senha, $id_utilizador);
            
            if ($stmtAtualizarSenha->execute()) {
                echo "<script>alert('Senha atualizada com sucesso.');</script>";
            } else {
                echo "<script>alert('Erro ao atualizar a senha.');</script>";
            }
        } else {
            echo "<script>alert('A nova senha e a confirmação da senha não coincidem.');</script>";
        }
    } else {
        echo "<script>alert('Senha atual incorreta.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
<meta charset="UTF-8">
<title>Página do Aluno</title>
<!-- CSS for full calendar -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.min.css" rel="stylesheet" />
<!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"/>
<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<!-- Moment.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.min.js"></script>
<!-- FullCalendar JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-md navbar-light bg-light">
    <div class="container">
        <a class='navbar-brand'>
			<img src='src/logo.png' alt='Logo' height='40' class='d-inline-block align-text-top'>
		</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="#">Página Inicial</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-toggle="modal" data-target="#modalEstado">Estado</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-toggle="modal" data-target="#modalAlterarSenha">Alterar Senha</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href='inicio_aluno.php?logout=true'>Terminar Sessão</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- /Navbar -->
<br>
<!-- Main content -->
<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <div id="calendar"></div>
        </div>
    </div>
</div>
<!-- /Main content -->

<!-- Modal para exibir o estado do aluno -->
<div class="modal fade" id="modalEstado" tabindex="-1" role="dialog" aria-labelledby="modalEstadoTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEstadoTitle">Estado do Aluno</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Aulas teóricas concluídas: <?php echo $numero_aulas_teoricas_concluidas; ?> / <b>28</b></p>
                <p>Aulas práticas concluídas: <?php echo $numero_aulas_praticas_concluidas; ?> / <b>32</b></p>
            </div>
        </div>
    </div>
</div>
<!-- /Modal para exibir o estado do aluno -->

<!-- Modal para mostrar detalhes da aula -->
<div class="modal fade" id="modalDetalhesAula" tabindex="-1" aria-labelledby="modalDetalhesAulaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetalhesAulaLabel">Detalhes da Aula</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>Data da Aula:</strong> <span id="detalhe_data"></span></p>
                <p><strong>Notas da Aula:</strong> <span id="detalhe_notas"></span></p>
                <p><strong>Tipo da Aula:</strong> <span id="detalhe_tipo_aula"></span></p>
                <p><strong>Professor:</strong> <span id="detalhe_professor"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para alterar a senha -->
<div class="modal fade" id="modalAlterarSenha" tabindex="-1" aria-labelledby="modalAlterarSenhaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAlterarSenhaLabel">Alterar Senha</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulário para alterar a senha -->
                <form method="post" action="">
                    <div class="form-group">
                        <label for="senha_atual">Senha Atual</label>
                        <input type="password" class="form-control" id="senha_atual" name="senha_atual" required>
                    </div>
                    <div class="form-group">
                        <label for="nova_senha">Nova Senha</label>
                        <input type="password" class="form-control" id="nova_senha" name="nova_senha" required>
                    </div>
                    <div class="form-group">
                        <label for="confirmar_senha">Confirmar Nova Senha</label>
                        <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Alterar Senha</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Script for displaying events -->
<script>
$(document).ready(function() {
    var eventData = <?php echo $data_json; ?>;
    display_events(eventData);
}); 

function display_events(eventData) {
    var calendar = $('#calendar').fullCalendar({
        defaultView: 'month',
        timeZone: 'local',
        editable: true,
        selectable: true,
        selectHelper: true,
        select: function(start, end) {
            $('#event_start_date').val(moment(start).format('YYYY-MM-DD'));
            $('#event_entry_modal').modal('show');
        },
        events: eventData,
        eventRender: function(event, element, view) { 
            element.bind('click', function() {
                // Preenche os detalhes da aula no modal
                $('#detalhe_titulo').text(event.title);
                // Formata a data no formato dia/mês/ano
                var dataFormatada = new Date(event.start).toLocaleDateString('pt-PT');
                $('#detalhe_data').text(dataFormatada);
                $('#detalhe_notas').text(event.notas);
                $('#detalhe_tipo_aula').text(event.tipo_aula);
                $('#detalhe_professor').text(event.nome_instrutor);
                // Abre o modal
                $('#modalDetalhesAula').modal('show');
            });
        }
    });
}
</script>
<!-- /Script for displaying events -->
</body>
</html>
