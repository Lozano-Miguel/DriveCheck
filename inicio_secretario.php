<?php
include('functions.php');
$conn = dbConnection();
verificarSessaoSecretario();
terminarSessao();

// Função para carregar opções de usuários do banco de dados com base no tipo de utilizador
function carregarOpcoesUtilizadores($conn, $tipoUtilizador) {
    $sql = "SELECT id_utilizador, username_utilizador FROM utilizador WHERE fk_tipoutilizador = ? AND eliminado_utilizador = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $tipoUtilizador);
    $stmt->execute();
    $result = $stmt->get_result();
    $opcoes = array();
    while ($row = $result->fetch_assoc()) {
        $opcoes[] = $row;
    }
    return $opcoes;
}

// Carregar opções de alunos e instrutores
$alunos = carregarOpcoesUtilizadores($conn, 1); // Suponha que o tipo de aluno seja 1
$instrutores = carregarOpcoesUtilizadores($conn, 2); // Suponha que o tipo de instrutor seja 2

// Processar o formulário de agendamento de aula
if (isset($_POST['adicionar_aula'])) {
    $tipo_aula = $_POST['tipo_aula'];
    $data_aula = $_POST['data_aula'];
    $hora_aula = $_POST['hora_aula'];
    $aluno = $_POST['aluno'];
    $instrutor = $_POST['instrutor'];
    $notas = $_POST['notas'];

    // Combinar data e hora
    $datetime_aula = $data_aula . ' ' . $hora_aula;

    // Verificar se a data é no passado
    $dataAtual = date('Y-m-d H:i:s');
    if ($datetime_aula < $dataAtual) {
        echo "<script>
                alert('Não é possível agendar aulas para datas que já passaram.');
                window.history.back();
              </script>";
    } else {
        $sqlInserirAula = "INSERT INTO aula (fk_tipo_aula, data_aula, fk_id_aluno, fk_id_instrutor, notas_aula) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sqlInserirAula);
        $stmt->bind_param("issis", $tipo_aula, $datetime_aula, $aluno, $instrutor, $notas);
        if ($stmt->execute()) {
           
        } else {
            echo "Erro ao agendar aula: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Processar a ação de eliminar aula
if (isset($_GET['eliminar_aula'])) {
    $id_aula = $_GET['eliminar_aula'];
    $sqlEliminarAula = "UPDATE aula SET eliminado_aula = 1 WHERE id_aula = ?";
    $stmtEliminar = $conn->prepare($sqlEliminarAula);
    $stmtEliminar->bind_param("i", $id_aula);
    if ($stmtEliminar->execute()) {
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    } else {
        echo "Erro ao eliminar aula: " . $stmtEliminar->error;
    }
    $stmtEliminar->close();
}

?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página do Secretário</title>
    <!-- Chamada do Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>

<?php
// Função para incluir o navbar superior
function includeNavbar() {
    ?>
    <nav class='navbar navbar-expand-md navbar-light bg-light'>
        <div class='container-fluid'>
            <a class='navbar-brand'>
                <img src='src/logo.png' alt='Logo' height='40' class='d-inline-block align-text-top'>
            </a>
            <button class='navbar-toggler' type='button' data-bs-toggle='collapse' data-bs-target='#navbarNav' aria-controls='navbarNav' aria-expanded='false' aria-label='Toggle navigation'>
                <span class='navbar-toggler-icon'></span>
            </button>
            <div class='collapse navbar-collapse' id='navbarNav'>
                <ul class='navbar-nav ms-auto'>
                    <li class='nav-item'>
                        <a class='nav-link' href='#' id='home-btn'>Página Inicial</a>
                    </li>
                    <li class='nav-item'>
                        <a class='nav-link' href='utilizadores_secretario.php' id='home-btn'>Utilizadores</a>
                    </li>
                    <li class='nav-item'>
                        <a class='nav-link' href='inicio_secretario.php?logout=true'>Terminar Sessão</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php
}

// Função para mostrar uma tabela de dados
function mostrarTabela($conn, $sqlQuery, $tableTitle, $tableHeaders) {
    $stmt = $conn->prepare($sqlQuery);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        ?>
        <div class='container my-5'>
            <div class='row justify-content-center'>
                <div class='col-md-8'>
                    <div class='card'>
                        <div class='card-header d-flex justify-content-between align-items-center'>
                            <span><b><?= $tableTitle ?></b></span>
                            <?php if ($tableTitle == "Aulas Marcadas") : ?>
                                <button class='btn btn-primary' id='open-add-aula-modal-btn'>Agendar Aula</button>
                            <?php endif; ?>
                        </div>
                        <div class='card-body'>
                            <table class='table'>
                                <thead>
                                <tr>
                                    <?php foreach ($tableHeaders as $header) : ?>
                                        <th class='text-center'><?= $header ?></th>
                                    <?php endforeach; ?>
                                    <th class='text-center'>Ações</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <?php foreach ($row as $key => $value) : ?>
                                            <td class='text-center'><?= $value ?></td>
                                        <?php endforeach; ?>
                                        <td class='text-center'>
                                            <?php if ($tableTitle == "Aulas Marcadas") : ?>
                                                <form method="GET" action="<?= $_SERVER['PHP_SELF'] ?>">
                                                    <input type="hidden" name="eliminar_aula" value="<?= $row["id_aula"] ?>">
                                                    <button type="submit" class='btn btn-danger btn-sm'>Eliminar Aula</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    } else {
        echo "Erro ao executar a consulta: " . $stmt->error;
    }
}

includeNavbar();

// Consulta SQL para obter aulas marcadas
$sqlAulasMarcadas = "SELECT aula.id_aula, utilizador.username_utilizador AS aluno, instrutor.username_utilizador AS instrutor, tipo_aula.titulo_tipo_aula AS tipo_aula, aula.data_aula, aula.notas_aula
FROM aula
INNER JOIN utilizador ON aula.fk_id_aluno = utilizador.id_utilizador
INNER JOIN utilizador AS instrutor ON aula.fk_id_instrutor = instrutor.id_utilizador
INNER JOIN tipo_aula ON aula.fk_tipo_aula = tipo_aula.id_tipo_aula
WHERE aula.eliminado_aula = 0";

$tableHeadersAulasMarcadas = ["ID", "Aluno", "Instrutor", "Tipo de Aula", "Data", "Notas"];

$stmtAulasMarcadas = $conn->prepare($sqlAulasMarcadas);
$stmtAulasMarcadas->execute();
$resultAulasMarcadas = $stmtAulasMarcadas->get_result();

if ($resultAulasMarcadas) {
    mostrarTabela($conn, $sqlAulasMarcadas, "Aulas Marcadas", $tableHeadersAulasMarcadas);  
} else {
    echo "Erro ao executar a consulta: " . $stmtAulasMarcadas->error;
}
?>

<!-- Modal para adicionar aula -->
<div class="modal fade" id="modalAdicionarAula" tabindex="-1" aria-labelledby="modalAdicionarAulaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAdicionarAulaLabel">Agendar Aula</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="tipo_aula" class="form-label">Tipo de Aula</label>
                        <select class="form-control" id="tipo_aula" name="tipo_aula" required>
                            <option value="Prática">Prática</option>
                            <option value="Teórica">Teórica</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="data_aula" class="form-label">Data da Aula</label>
                        <input type="date" class="form-control" id="data_aula" name="data_aula" required>
                    </div>
                    <div class="mb-3">
                        <label for="hora_aula" class="form-label">Hora da Aula</label>
                        <input type="time" class="form-control" id="hora_aula" name="hora_aula" required>
                    </div>
                    <div class="mb-3">
                        <label for="aluno" class="form-label">Aluno</label>
                        <select class="form-control" id="aluno" name="aluno" required>
                            <?php foreach ($alunos as $alunoOption) : ?>
                                <option value="<?= $alunoOption['id_utilizador'] ?>"><?= $alunoOption['username_utilizador'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="instrutor" class="form-label">Instrutor</label>
                        <select class="form-control" id="instrutor" name="instrutor" required>
                            <?php foreach ($instrutores as $instrutorOption) : ?>
                                <option value="<?= $instrutorOption['id_utilizador'] ?>"><?= $instrutorOption['username_utilizador'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="notas" class="form-label">Notas</label>
                        <textarea class="form-control" id="notas" name="notas"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" name="adicionar_aula" class="btn btn-primary">Agendar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Chamada do Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha256-fh8VA992XMpeCZiRuU4xii75UIG6KvHrbUF8yIS/2/4=" crossorigin="anonymous"></script>
<script>
    // Obtém uma referência ao botão "Agendar Aula"
    const openAddAulaModalBtn = document.getElementById('open-add-aula-modal-btn');

    // Obtém uma referência ao modal de adicionar aula
    const modalAdicionarAula = document.getElementById('modalAdicionarAula');

    // Adiciona um evento de clique ao botão
    openAddAulaModalBtn.addEventListener('click', function() {
        // Abre o modal
        const modal = new bootstrap.Modal(modalAdicionarAula);
        modal.show();
    });
</script>
</body>
</html>
