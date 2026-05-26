<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/functions.php";

if (is_logged_in()) {
    header("Location: " . (current_user()['role']==='admin' ? "/school_library/admin/dashboard.php" : "/school_library/user/dashboard.php"));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, full_name, email, password_hash, role, status FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc();

    if ($u && $u['status'] === 'active' && password_verify($pass, $u['password_hash'])) {
        $_SESSION['user'] = [
            'id' => (int)$u['id'],
            'full_name' => $u['full_name'],
            'email' => $u['email'],
            'role' => $u['role']
        ];
        set_flash('success', 'Login successful!');
        header("Location: " . ($u['role']==='admin' ? "/school_library/admin/dashboard.php" : "/school_library/user/dashboard.php"));
        exit;
    } else {
        set_flash('danger', 'Invalid login or blocked account.');
    }
}
?>

<style>
  :root {
    --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --gradient-secondary: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --gradient-warning: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  }
  
  .login-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    position: relative;
    overflow: hidden;
  }
  
  .login-wrapper::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
      radial-gradient(1000px circle at 10% 20%, rgba(102, 126, 234, 0.1), transparent 60%),
      radial-gradient(800px circle at 90% 80%, rgba(79, 172, 254, 0.08), transparent 60%),
      radial-gradient(600px circle at 50% 50%, rgba(255, 255, 255, 0.05), transparent 70%);
  }
  
  .login-container {
    width: 100%;
    max-width: 480px;
    perspective: 1000px;
  }
  
  .login-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 28px;
    border: 1px solid rgba(255, 255, 255, 0.4);
    box-shadow: 
      0 32px 64px rgba(0, 0, 0, 0.15),
      0 16px 32px rgba(0, 0, 0, 0.1),
      0 8px 16px rgba(0, 0, 0, 0.05),
      inset 0 1px 0 rgba(255, 255, 255, 0.8);
    overflow: hidden;
    position: relative;
    z-index: 1;
    animation: cardFlip 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    transform-style: preserve-3d;
  }
  
  .login-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 6px;
    background: var(--gradient-primary);
  }
  
  .login-header {
    position: relative;
    padding: 50px 40px 30px;
    text-align: center;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), transparent);
  }
  
  .login-icon {
    width: 90px;
    height: 90px;
    background: var(--gradient-primary);
    border-radius: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    color: white;
    margin: 0 auto 25px;
    box-shadow: 
      0 16px 32px rgba(102, 126, 234, 0.3),
      0 8px 16px rgba(102, 126, 234, 0.2);
    animation: float 4s ease-in-out infinite;
    transform-style: preserve-3d;
  }
  
  .login-icon::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    border-radius: 24px;
    animation: shine 3s infinite;
  }
  
  .login-body {
    padding: 0 40px 40px;
  }
  
  .form-label {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    letter-spacing: 0.3px;
  }
  
  .form-label::before {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--gradient-secondary);
  }
  
  .input-group {
    position: relative;
    margin-bottom: 28px;
  }
  
  .form-control {
    padding: 16px 20px;
    padding-left: 48px;
    border-radius: 16px;
    border: 2px solid #e2e8f0;
    background: #f8fafc;
    font-size: 16px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    width: 100%;
  }
  
  .input-icon {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 20px;
    color: #667eea;
    transition: all 0.3s ease;
    z-index: 2;
  }
  
  .form-control:focus {
    border-color: #667eea;
    background: white;
    box-shadow: 
      0 0 0 4px rgba(102, 126, 234, 0.2),
      0 8px 24px rgba(102, 126, 234, 0.1);
    transform: translateY(-3px);
  }
  
  .form-control:focus + .input-icon {
    transform: translateY(-50%) scale(1.2);
    color: #764ba2;
  }
  
  .password-toggle {
    position: absolute;
    right: 18px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #718096;
    cursor: pointer;
    font-size: 18px;
    transition: all 0.3s ease;
    padding: 4px;
    border-radius: 6px;
  }
  
  .password-toggle:hover {
    color: #667eea;
    background: rgba(102, 126, 234, 0.1);
    transform: translateY(-50%) scale(1.1);
  }
  
  .login-btn {
    background: var(--gradient-primary);
    border: none;
    color: white;
    padding: 18px;
    font-size: 16px;
    font-weight: 600;
    letter-spacing: 0.5px;
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    box-shadow: 0 12px 32px rgba(102, 126, 234, 0.4);
    margin-top: 10px;
    width: 100%;
  }
  
  .login-btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.7s, height 0.7s;
  }
  
  .login-btn:hover {
    transform: translateY(-4px);
    box-shadow: 
      0 20px 40px rgba(102, 126, 234, 0.5),
      0 8px 16px rgba(102, 126, 234, 0.3);
    letter-spacing: 1px;
  }
  
  .login-btn:hover::before {
    width: 400px;
    height: 400px;
  }
  
  .login-btn:active {
    transform: translateY(-2px);
  }
  
  .login-footer {
    text-align: center;
    margin-top: 30px;
    padding-top: 25px;
    border-top: 1px solid #e2e8f0;
  }
  
  .register-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    position: relative;
    padding: 6px 12px;
    border-radius: 8px;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }
  
  .register-link::after {
    content: '→';
    opacity: 0;
    transform: translateX(-10px);
    transition: all 0.3s ease;
  }
  
  .register-link:hover {
    background: rgba(102, 126, 234, 0.1);
    padding-right: 20px;
  }
  
  .register-link:hover::after {
    opacity: 1;
    transform: translateX(0);
  }
  
  .role-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 50px;
    background: var(--gradient-secondary);
    color: white;
    font-size: 14px;
    font-weight: 600;
    margin-top: 15px;
    box-shadow: 0 4px 12px rgba(79, 172, 254, 0.3);
    animation: pulse 2s infinite;
  }
  
  .floating-elements {
    position: absolute;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 0;
  }
  
  .float-element {
    position: absolute;
    font-size: 24px;
    opacity: 0.1;
    animation: floatAround 20s linear infinite;
  }
  
  .float-element:nth-child(1) { top: 10%; left: 5%; animation-delay: 0s; }
  .float-element:nth-child(2) { top: 20%; right: 15%; animation-delay: 5s; }
  .float-element:nth-child(3) { bottom: 30%; left: 10%; animation-delay: 10s; }
  .float-element:nth-child(4) { bottom: 20%; right: 5%; animation-delay: 15s; }
  
  @keyframes cardFlip {
    from {
      opacity: 0;
      transform: rotateY(20deg) translateY(40px);
    }
    to {
      opacity: 1;
      transform: rotateY(0) translateY(0);
    }
  }
  
  @keyframes float {
    0%, 100% {
      transform: translateY(0) rotate(0deg);
    }
    50% {
      transform: translateY(-15px) rotate(5deg);
    }
  }
  
  @keyframes floatAround {
    0% {
      transform: translate(0, 0) rotate(0deg);
    }
    25% {
      transform: translate(100px, 50px) rotate(90deg);
    }
    50% {
      transform: translate(50px, 100px) rotate(180deg);
    }
    75% {
      transform: translate(-50px, 50px) rotate(270deg);
    }
    100% {
      transform: translate(0, 0) rotate(360deg);
    }
  }
  
  @keyframes shine {
    0% {
      transform: translateX(-100%);
    }
    100% {
      transform: translateX(100%);
    }
  }
  
  @keyframes pulse {
    0%, 100% {
      box-shadow: 0 4px 12px rgba(79, 172, 254, 0.3);
    }
    50% {
      box-shadow: 0 4px 20px rgba(79, 172, 254, 0.5);
    }
  }
  
  .form-group {
    animation: slideIn 0.6s ease forwards;
    opacity: 0;
    transform: translateX(-20px);
  }
  
  .form-group:nth-child(1) { animation-delay: 0.2s; }
  .form-group:nth-child(2) { animation-delay: 0.4s; }
  
  @keyframes slideIn {
    to {
      opacity: 1;
      transform: translateX(0);
    }
  }
  
  .shake {
    animation: shake 0.5s ease-in-out;
  }
  
  @keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-8px); }
    20%, 40%, 60%, 80% { transform: translateX(8px); }
  }
</style>

<div class="floating-elements">
  <div class="float-element">📚</div>
  <div class="float-element">🔑</div>
  <div class="float-element">📖</div>
  <div class="float-element">🎓</div>
</div>

<div class="login-wrapper">
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <div class="login-icon">
          🔐
        </div>
        <h2 class="fw-bold mb-2">Welcome Back</h2>
        <p class="text-muted mb-3">Sign in to access your library account</p>
        <div class="role-badge">
          <span>📚</span>
          <span>Library Access Portal</span>
        </div>
      </div>
      
      <div class="login-body">
        <?php if (has_flash()): ?>
          <div class="alert alert-<?php echo get_flash_type(); ?> alert-dismissible fade show rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
              <span class="fs-5"><?php echo get_flash_type() === 'success' ? '✅' : '⚠️'; ?></span>
              <div><?php echo get_flash_message(); ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>
        
        <form method="post" id="loginForm">
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <div class="input-group">
              <span class="input-icon">📧</span>
              <input class="form-control" type="email" name="email" required placeholder="admin@school.lk">
            </div>
          </div>
          
          <div class="form-group">
            <label class="form-label">Password</label>
            <div class="input-group">
              <span class="input-icon">🔒</span>
              <input class="form-control" type="password" name="password" required id="password" placeholder="Enter your password">
              <button type="button" class="password-toggle" id="togglePassword">
                👁️
              </button>
            </div>
          </div>
          
          <button type="submit" class="login-btn">
            Sign In
          </button>
        </form>
        
        <div class="login-footer">
          <span class="text-muted">Don't have an account? </span>
          <a href="/school_library/register.php" class="register-link">
            Create Account
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    
    // Password toggle functionality
    togglePassword.addEventListener('click', function() {
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      this.textContent = type === 'password' ? '👁️' : '👁️‍🗨️';
    });
    
    // Form validation with animation
    form.addEventListener('submit', function(e) {
      const emailInput = form.querySelector('input[type="email"]');
      const passwordInput = form.querySelector('input[type="password"]');
      let isValid = true;
      
      // Clear previous error styles
      [emailInput, passwordInput].forEach(input => {
        input.style.borderColor = '#e2e8f0';
        input.style.boxShadow = 'none';
      });
      
      // Validate email
      if (!emailInput.value.trim() || !emailInput.validity.valid) {
        emailInput.style.borderColor = '#f56565';
        emailInput.style.boxShadow = '0 0 0 4px rgba(245, 101, 101, 0.15)';
        isValid = false;
      }
      
      // Validate password
      if (!passwordInput.value.trim()) {
        passwordInput.style.borderColor = '#f56565';
        passwordInput.style.boxShadow = '0 0 0 4px rgba(245, 101, 101, 0.15)';
        isValid = false;
      }
      
      if (!isValid) {
        e.preventDefault();
        
        // Shake animation for invalid form
        form.classList.remove('shake');
        void form.offsetWidth; // Trigger reflow
        form.classList.add('shake');
        
        // Remove shake class after animation
        setTimeout(() => {
          form.classList.remove('shake');
        }, 500);
      }
    });
    
    // Add focus/blur effects
    const inputs = form.querySelectorAll('.form-control');
    inputs.forEach(input => {
      input.addEventListener('focus', function() {
        this.parentElement.style.transform = 'translateY(-2px)';
      });
      
      input.addEventListener('blur', function() {
        this.parentElement.style.transform = 'translateY(0)';
      });
    });
    
    // Demo credentials hint
    const emailInput = form.querySelector('input[type="email"]');
    emailInput.addEventListener('focus', function() {
      if (this.value === '') {
        this.placeholder = 'Try: admin@school.lk';
      }
    });
    
    emailInput.addEventListener('blur', function() {
      if (this.value === '') {
        this.placeholder = 'admin@school.lk';
      }
    });
  });
</script>

<?php require_once __DIR__ . "/includes/footer.php"; ?>