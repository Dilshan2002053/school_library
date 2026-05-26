<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/functions.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    if (strlen($pass) < 6) {
        set_flash('danger', 'Password must be at least 6 characters.');
    } else {
        $hash = password_hash($pass, PASSWORD_BCRYPT);

        try {
            $stmt = $conn->prepare("INSERT INTO users(full_name,email,password_hash,role) VALUES(?,?,?,'user')");
            $stmt->bind_param("sss", $name, $email, $hash);
            $stmt->execute();
            set_flash('success', 'Registered successfully. Now login.');
            header("Location: /school_library/login.php");
            exit;
        } catch (Exception $e) {
            set_flash('danger', 'Email already exists.');
        }
    }
}
?>

<style>
  :root {
    --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  }
  
  .register-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
    position: relative;
    overflow: hidden;
  }
  
  .register-wrapper::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
      radial-gradient(800px circle at 20% 20%, rgba(102, 126, 234, 0.08), transparent 50%),
      radial-gradient(600px circle at 80% 80%, rgba(240, 147, 251, 0.06), transparent 50%);
  }
  
  .register-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 28px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 
      0 32px 64px rgba(0, 0, 0, 0.12),
      0 16px 32px rgba(0, 0, 0, 0.08),
      0 8px 16px rgba(0, 0, 0, 0.04),
      inset 0 1px 0 rgba(255, 255, 255, 0.8);
    overflow: hidden;
    position: relative;
    z-index: 1;
    animation: cardAppear 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    max-width: 480px;
    width: 100%;
  }
  
  .register-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 6px;
    background: var(--gradient-primary);
  }
  
  .register-header {
    position: relative;
    padding: 40px 40px 20px;
    text-align: center;
  }
  
  .register-icon {
    width: 80px;
    height: 80px;
    background: var(--gradient-primary);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    color: white;
    margin: 0 auto 20px;
    box-shadow: 0 12px 24px rgba(102, 126, 234, 0.3);
    animation: float 4s ease-in-out infinite;
  }
  
  .register-body {
    padding: 0 40px 40px;
  }
  
  .form-label {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  
  .form-label::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #667eea;
  }
  
  .form-control {
    padding: 14px 18px;
    border-radius: 14px;
    border: 2px solid #e2e8f0;
    background: #f8fafc;
    font-size: 16px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }
  
  .form-control:focus {
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
    transform: translateY(-2px);
  }
  
  .form-control:hover:not(:focus) {
    border-color: #cbd5e0;
  }
  
  .password-hint {
    font-size: 13px;
    color: #718096;
    margin-top: 6px;
    padding-left: 24px;
    position: relative;
  }
  
  .password-hint::before {
    content: '💡';
    position: absolute;
    left: 0;
  }
  
  .register-btn {
    background: var(--gradient-primary);
    border: none;
    color: white;
    padding: 16px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 14px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
    margin-top: 10px;
  }
  
  .register-btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
  }
  
  .register-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 32px rgba(102, 126, 234, 0.4);
  }
  
  .register-btn:hover::before {
    width: 300px;
    height: 300px;
  }
  
  .register-btn:active {
    transform: translateY(-1px);
  }
  
  .login-link {
    text-align: center;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
  }
  
  .login-link a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    position: relative;
    padding: 4px 8px;
    border-radius: 6px;
    transition: all 0.3s ease;
  }
  
  .login-link a::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background: #667eea;
    transform: scaleX(0);
    transition: transform 0.3s ease;
  }
  
  .login-link a:hover::after {
    transform: scaleX(1);
  }
  
  .form-group {
    margin-bottom: 24px;
    animation: slideUp 0.5s ease forwards;
    opacity: 0;
    transform: translateY(20px);
  }
  
  .form-group:nth-child(1) { animation-delay: 0.1s; }
  .form-group:nth-child(2) { animation-delay: 0.2s; }
  .form-group:nth-child(3) { animation-delay: 0.3s; }
  .form-group:nth-child(4) { animation-delay: 0.4s; }
  
  @keyframes cardAppear {
    from {
      opacity: 0;
      transform: translateY(40px) scale(0.95);
    }
    to {
      opacity: 1;
      transform: translateY(0) scale(1);
    }
  }
  
  @keyframes slideUp {
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  
  @keyframes float {
    0%, 100% {
      transform: translateY(0);
    }
    50% {
      transform: translateY(-10px);
    }
  }
  
  .floating-shapes {
    position: absolute;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 0;
  }
  
  .shape {
    position: absolute;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(240, 147, 251, 0.1));
    animation: float 6s ease-in-out infinite;
  }
  
  .shape:nth-child(1) {
    width: 100px;
    height: 100px;
    top: 10%;
    left: 5%;
    animation-delay: 0s;
  }
  
  .shape:nth-child(2) {
    width: 150px;
    height: 150px;
    bottom: 15%;
    right: 10%;
    animation-delay: 2s;
  }
  
  .shape:nth-child(3) {
    width: 80px;
    height: 80px;
    top: 40%;
    right: 15%;
    animation-delay: 4s;
  }
</style>

<div class="floating-shapes">
  <div class="shape"></div>
  <div class="shape"></div>
  <div class="shape"></div>
</div>

<div class="register-wrapper">
  <div class="register-card">
    <div class="register-header">
      <div class="register-icon">
        👤
      </div>
      <h2 class="fw-bold mb-2">Join Our Library</h2>
      <p class="text-muted">Create your account to start borrowing books</p>
    </div>
    
    <div class="register-body">
      <?php if (has_flash()): ?>
        <div class="alert alert-<?php echo get_flash_type(); ?> alert-dismissible fade show rounded-3" role="alert">
          <?php echo get_flash_message(); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
      
      <form method="post" id="registerForm">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input class="form-control" name="full_name" required placeholder="Enter your full name">
          <div class="password-hint">Enter your full name as per school records</div>
        </div>
        
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input class="form-control" type="email" name="email" required placeholder="your.email@school.lk">
          <div class="password-hint">Use your school email for verification</div>
        </div>
        
        <div class="form-group">
          <label class="form-label">Password</label>
          <input class="form-control" type="password" name="password" required id="password" placeholder="Create a strong password">
          <div class="password-hint">Minimum 6 characters</div>
        </div>
        
        <button type="submit" class="register-btn w-100">
          Create Account
        </button>
      </form>
      
      <div class="login-link">
        <span class="text-muted">Already have an account? </span>
        <a href="/school_library/login.php">Login here</a>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const passwordInput = document.getElementById('password');
    
    // Add input validation styling
    form.addEventListener('submit', function(e) {
      const inputs = form.querySelectorAll('input[required]');
      let isValid = true;
      
      inputs.forEach(input => {
        if (!input.value.trim()) {
          input.style.borderColor = '#f56565';
          input.style.boxShadow = '0 0 0 4px rgba(245, 101, 101, 0.15)';
          isValid = false;
        } else {
          input.style.borderColor = '#48bb78';
          input.style.boxShadow = '0 0 0 4px rgba(72, 187, 120, 0.15)';
        }
      });
      
      if (!isValid) {
        e.preventDefault();
        
        // Shake animation for invalid form
        form.style.animation = 'none';
        setTimeout(() => {
          form.style.animation = 'shake 0.5s ease-in-out';
        }, 10);
        
        setTimeout(() => {
          form.style.animation = '';
        }, 500);
      }
    });
    
    // Password strength indicator
    passwordInput.addEventListener('input', function() {
      const strength = calculatePasswordStrength(this.value);
      const hint = this.nextElementSibling.nextElementSibling;
      
      if (strength === 'weak') {
        hint.style.color = '#f56565';
        hint.textContent = 'Password is too weak';
      } else if (strength === 'medium') {
        hint.style.color = '#ed8936';
        hint.textContent = 'Password strength: Medium';
      } else if (strength === 'strong') {
        hint.style.color = '#48bb78';
        hint.textContent = 'Password strength: Strong';
      } else {
        hint.style.color = '#718096';
        hint.textContent = 'Minimum 6 characters';
      }
    });
    
    function calculatePasswordStrength(password) {
      if (password.length === 0) return 'empty';
      if (password.length < 6) return 'weak';
      
      let score = 0;
      if (/[a-z]/.test(password)) score++;
      if (/[A-Z]/.test(password)) score++;
      if (/[0-9]/.test(password)) score++;
      if (/[^a-zA-Z0-9]/.test(password)) score++;
      
      if (password.length >= 12) score++;
      
      if (score <= 2) return 'weak';
      if (score <= 4) return 'medium';
      return 'strong';
    }
    
    // Add shake animation
    const style = document.createElement('style');
    style.textContent = `
      @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
      }
    `;
    document.head.appendChild(style);
  });
</script>

<?php require_once __DIR__ . "/includes/footer.php"; ?>