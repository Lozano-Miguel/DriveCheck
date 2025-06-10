// scripts.js

// Função para adicionar funcionalidade de mostrar/ocultar senha
function togglePasswordVisibility(passwordInputId, toggleButtonId) {
    const passwordInput = document.getElementById(passwordInputId);
    const togglePasswordButton = document.getElementById(toggleButtonId);

    togglePasswordButton.addEventListener('click', function() {
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;
        this.textContent = type === 'password' ? 'Mostrar' : 'Ocultar';
    });
}

// Adicionado script de validação de data
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    form.addEventListener('submit', function(event) {
        const dataAula = document.getElementById('data_aula').value;
        const dataAtual = new Date().toISOString().split('T')[0];
        
        if (dataAula < dataAtual) {
            alert("Não é possível agendar aulas para datas que já passaram.");
            event.preventDefault();
        }
    });
});
