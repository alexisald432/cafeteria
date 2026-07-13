<?php
session_start();
// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - CaféAdmin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: var(--bg-primary);
            background-image: radial-gradient(circle at top right, rgba(200, 169, 126, 0.05), transparent 40%),
                              radial-gradient(circle at bottom left, rgba(200, 169, 126, 0.05), transparent 40%);
        }
        .login-container {
            background-color: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: var(--radius-lg);
            padding: 3rem;
            width: 100%;
            max-width: 400px;
            box-shadow: var(--shadow-lg);
            text-align: center;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .login-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        .login-logo .logo-icon {
            background: linear-gradient(135deg, var(--accent), var(--accent-hover));
            width: 48px;
            height: 48px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-dark);
            font-size: 1.5rem;
            box-shadow: var(--shadow-glow);
        }
        .login-form {
            text-align: left;
        }
        .login-form .form-group {
            margin-bottom: 1.5rem;
        }
        .login-form label {
            color: var(--text-secondary);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }
        .login-btn {
            width: 100%;
            margin-top: 1rem;
            padding: 0.8rem;
            font-size: 1.1rem;
        }
        .error-msg {
            color: var(--error);
            background-color: rgba(244, 67, 54, 0.1);
            border: 1px solid rgba(244, 67, 54, 0.2);
            padding: 0.8rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            display: none;
            text-align: center;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-logo">
            <div class="logo-icon">
                <i class="fas fa-coffee"></i>
            </div>
            <span>CaféAdmin</span>
        </div>
        
        <h2 style="margin-bottom: 0.5rem; font-size: 1.5rem;">Bienvenido de nuevo</h2>
        <p style="color: var(--text-muted); margin-bottom: 2rem; font-size: 0.95rem;">Ingresa tus credenciales para acceder</p>
        
        <div class="error-msg" id="loginError"></div>

        <form class="login-form" id="loginForm">
            <div class="form-group">
                <label for="username">Usuario (Nombre)</label>
                <div class="input-icon" style="position: relative;">
                    <i class="fas fa-user" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="text" id="username" class="form-control" style="padding-left: 2.5rem;" placeholder="Ej. pedro, admin" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="input-icon" style="position: relative;">
                    <i class="fas fa-lock" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="password" id="password" class="form-control" style="padding-left: 2.5rem;" placeholder="Tu contraseña" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary login-btn">
                Iniciar Sesión <i class="fas fa-sign-in-alt" style="margin-left: 0.5rem;"></i>
            </button>
        </form>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const errorDiv = document.getElementById('loginError');
            const btn = this.querySelector('button');
            
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
            btn.disabled = true;

            fetch('api/auth_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'index.php';
                } else {
                    errorDiv.textContent = data.error || 'Error de autenticación';
                    errorDiv.style.display = 'block';
                    btn.innerHTML = 'Iniciar Sesión <i class="fas fa-sign-in-alt" style="margin-left: 0.5rem;"></i>';
                    btn.disabled = false;
                }
            })
            .catch(error => {
                errorDiv.textContent = 'Error de conexión con el servidor';
                errorDiv.style.display = 'block';
                btn.innerHTML = 'Iniciar Sesión <i class="fas fa-sign-in-alt" style="margin-left: 0.5rem;"></i>';
                btn.disabled = false;
            });
        });
    </script>
</body>
</html>
