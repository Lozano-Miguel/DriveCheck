<?php
include('functions.php');
verificarSessaoProfessor();
$conn = dbConnection();
terminarSessao();

// Processar a requisição de atualização de presença
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id_aula"])) {
    $id_aula = $_POST["id_aula"];
    $presenca_aula = $_POST["presenca_aula"];
    
    // Obter ID do aluno e tipo de aula a partir da tabela aula
    $sqlObterAlunoTipo = "SELECT fk_id_aluno, fk_tipo_aula FROM aula WHERE id_aula = ?";
    $stmtObterAlunoTipo = $conn->prepare($sqlObterAlunoTipo);
    $stmtObterAlunoTipo->bind_param("i", $id_aula);
    $stmtObterAlunoTipo->execute();
    $resultObterAlunoTipo = $stmtObterAlunoTipo->get_result();
    $dadosAula = $resultObterAlunoTipo->fetch_assoc();
    $idAluno = $dadosAula['fk_id_aluno'];
    $tipoAula = $dadosAula['fk_tipo_aula'];
    
    // Contar aulas de acordo com o tipo de presença
    $limiteAulas = $presenca_aula == 1 ? 28 : 32;
    $totalAulas = contarAulas($conn, $idAluno, $presenca_aula);
    
    if ($totalAulas >= $limiteAulas) {
        echo "<script>
                alert('O aluno já atingiu o máximo de aulas permitidas para este tipo.');
                window.history.back();
              </script>";
    } else {
        $sqlAtualizarPresenca = "UPDATE aula SET presenca_aula = ? WHERE id_aula = ?";
        $stmtAtualizarPresenca = $conn->prepare($sqlAtualizarPresenca);
        $stmtAtualizarPresenca->bind_param("ii", $presenca_aula, $id_aula);
        
        if ($stmtAtualizarPresenca->execute()) {
            // Atualizar a página para refletir a mudança
            header("Location: inicio_professor.php");
            exit;
        } else {
            echo "Erro ao atualizar a presença.";
        }
    }
}

function contarAulas($conn, $idAluno, $tipoAula) {
    $sql = "SELECT COUNT(*) as total FROM aula WHERE fk_id_aluno = ? AND presenca_aula = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $idAluno, $tipoAula);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'];
}


// Processar a requisição de alteração de senha
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["senha_atual"]) && isset($_POST["nova_senha"]) && isset($_POST["confirmar_senha"])) {
    $senha_atual = $_POST["senha_atual"];
    $nova_senha = $_POST["nova_senha"];
    $confirmar_senha = $_POST["confirmar_senha"];
    
    // Verificar se a senha atual do usuário está correta
    $sqlVerificarSenha = "SELECT * FROM utilizador WHERE id_utilizador = ? AND password_utilizador = ?";
    $stmtVerificarSenha = $conn->prepare($sqlVerificarSenha);
    $stmtVerificarSenha->bind_param("is", $_SESSION['id_utilizador'], $senha_atual);
    $stmtVerificarSenha->execute();
    $resultVerificarSenha = $stmtVerificarSenha->get_result();
    
    if ($resultVerificarSenha->num_rows == 1) {
        // Senha atual está correta, agora verificamos se a nova senha e a confirmação coincidem
        if ($nova_senha === $confirmar_senha) {
            // Atualizar a senha no banco de dados
            $sqlAtualizarSenha = "UPDATE utilizador SET password_utilizador = ? WHERE id_utilizador = ?";
            $stmtAtualizarSenha = $conn->prepare($sqlAtualizarSenha);
            $stmtAtualizarSenha->bind_param("si", $nova_senha, $_SESSION['id_utilizador']);
            
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

// Consulta SQL para obter as aulas do professor
$sqlAulasProfessor = "SELECT a.id_aula, u.username_utilizador AS aluno, t.titulo_tipo_aula AS tipo_aula, a.data_aula, a.presenca_aula, a.notas_aula
FROM aula a
INNER JOIN utilizador u ON a.fk_id_aluno = u.id_utilizador
INNER JOIN tipo_aula t ON a.fk_tipo_aula = t.id_tipo_aula
WHERE a.fk_id_instrutor = ? AND a.eliminado_aula = 0";

$stmtAulasProfessor = $conn->prepare($sqlAulasProfessor);
$stmtAulasProfessor->bind_param("i", $_SESSION['id_utilizador']);
$stmtAulasProfessor->execute();
$resultAulasProfessor = $stmtAulasProfessor->get_result();
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página do Professor</title>
    <!-- Chamada do Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
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
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#modalAlterarSenha">Alterar Senha</a>
                    </li>
                    <li class='nav-item'>
                        <a class='nav-link' href='inicio_professor.php?logout=true'>Terminar Sessão</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Aulas do Professor</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Data da Aula</th>
                                <th>Aluno</th>
                                <th>Tipo de Aula</th>
                                <th>Notas</th>
                                <th>Presença</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($row = $resultAulasProfessor->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?= $row['data_aula'] ?></td>
                                    <td><?= $row['aluno'] ?></td>
                                    <td><?= $row['tipo_aula'] ?></td>
                                    <td><?= $row['notas_aula'] ?></td>
                                    <td>
                                        <form method="post" action="">
                                            <input type="hidden" name="id_aula" value="<?= $row['id_aula'] ?>">
                                            <input type="hidden" name="presenca_aula" value="<?= $row['presenca_aula'] ? 0 : 1 ?>">
                                            <button type="submit" class="btn <?= $row['presenca_aula'] ? 'btn-danger' : 'btn-success' ?>">
                                                <?= $row['presenca_aula'] ? 'Retirar Presença' : 'Marcar Presença' ?>
                                            </button>
                                        </form>
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

<!-- Modal para alterar a senha -->
<!-- Modal para alterar a senha -->
<div class="modal fade" id="modalAlterarSenha" tabindex="-1" aria-labelledby="modalAlterarSenhaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAlterarSenhaLabel">Alterar Senha</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
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
                    </div><br>
                    <button type="submit" class="btn btn-primary">Alterar Senha</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Chamada do Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha256-fh8VA992XMpeCZiRuU4xii75UIG6KvHrbUF8yIS/2/4=" crossorigin="anonymous"></script>

</body>
</html>

