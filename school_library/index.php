<?php require_once __DIR__ . "/includes/header.php"; ?>

<style>
  :root {
    --gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --gradient-2: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --gradient-3: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  }
  
  .hero-wrap{
    background: 
      radial-gradient(1000px circle at 0% 0%, rgba(102, 126, 234, 0.15), transparent 40%),
      radial-gradient(800px circle at 100% 0%, rgba(240, 147, 251, 0.12), transparent 40%),
      radial-gradient(900px circle at 30% 100%, rgba(79, 172, 254, 0.1), transparent 50%),
      radial-gradient(700px circle at 80% 80%, rgba(25, 135, 84, 0.08), transparent 45%);
    border-radius: 24px;
    overflow: hidden;
    position: relative;
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
  }
  
  .hero-wrap::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    animation: shimmer 8s infinite;
  }
  
  .hero-card{
    border-radius: 24px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 
      0 20px 60px rgba(0, 0, 0, 0.12),
      0 8px 32px rgba(0, 0, 0, 0.08),
      inset 0 1px 0 rgba(255, 255, 255, 0.2);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    position: relative;
    overflow: hidden;
  }
  
  .hero-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: var(--gradient-1);
  }
  
  .icon-badge{
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    background: var(--gradient-1);
    border: 2px solid rgba(255, 255, 255, 0.3);
    font-size: 28px;
    color: white;
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    transform: translateY(0);
    transition: all 0.3s ease;
    animation: float 3s ease-in-out infinite;
  }
  
  .icon-badge:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 24px rgba(102, 126, 234, 0.4);
  }
  
  .mini-card{
    border-radius: 20px;
    border: 1px solid rgba(0,0,0,.06);
    box-shadow: 
      0 16px 32px rgba(0,0,0,.06),
      0 4px 16px rgba(0,0,0,.04);
    background: #fff;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
  }
  
  .mini-card:hover {
    transform: translateY(-8px);
    box-shadow: 
      0 24px 48px rgba(0,0,0,.1),
      0 8px 24px rgba(0,0,0,.06);
  }
  
  .mini-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: var(--gradient-2);
    opacity: 0;
    transition: opacity 0.3s ease;
  }
  
  .mini-card:hover::after {
    opacity: 1;
  }
  
  .chip{
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 50px;
    background: linear-gradient(135deg, rgba(13,110,253,0.1), rgba(25,135,84,0.1));
    border: 1px solid rgba(13,110,253,0.15);
    font-size: 0.92rem;
    font-weight: 500;
    color: #2d3748;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
  }
  
  .chip:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(13,110,253,0.15);
    border-color: rgba(13,110,253,0.3);
  }
  
  .btn-pill { 
    border-radius: 50px; 
    padding: 12px 28px;
    font-weight: 600;
    letter-spacing: 0.3px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
  }
  
  .btn-pill::before {
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
  
  .btn-pill:hover::before {
    width: 300px;
    height: 300px;
  }
  
  .btn-dark.btn-pill {
    background: linear-gradient(135deg, #2d3748, #4a5568);
    border: none;
    box-shadow: 0 8px 24px rgba(45, 55, 72, 0.3);
  }
  
  .btn-dark.btn-pill:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 32px rgba(45, 55, 72, 0.4);
  }
  
  .btn-outline-dark.btn-pill {
    border-width: 2px;
    border-color: #2d3748;
    background: transparent;
  }
  
  .btn-outline-dark.btn-pill:hover {
    background: #2d3748;
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(45, 55, 72, 0.2);
  }
  
  .feature-dot{
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: var(--gradient-3);
    box-shadow: 0 0 0 8px rgba(79, 172, 254, 0.2);
    flex: 0 0 auto;
    margin-top: 4px;
    position: relative;
    animation: pulse 2s infinite;
  }
  
  .feature-dot::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: white;
  }
  
  .floating-element {
    animation: float 6s ease-in-out infinite;
  }
  
  .floating-element:nth-child(2) {
    animation-delay: 1s;
  }
  
  .floating-element:nth-child(3) {
    animation-delay: 2s;
  }
  
  .gradient-text {
    background: var(--gradient-1);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    display: inline-block;
  }
  
  .info-card {
    border: 1px solid rgba(13, 110, 253, 0.2);
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.05), rgba(25, 135, 84, 0.05));
    border-radius: 16px;
    backdrop-filter: blur(10px);
  }
  
  @keyframes float {
    0%, 100% {
      transform: translateY(0);
    }
    50% {
      transform: translateY(-10px);
    }
  }
  
  @keyframes pulse {
    0%, 100% {
      box-shadow: 0 0 0 8px rgba(79, 172, 254, 0.2);
    }
    50% {
      box-shadow: 0 0 0 12px rgba(79, 172, 254, 0.1);
    }
  }
  
  @keyframes shimmer {
    0% {
      transform: translateX(-100%);
    }
    100% {
      transform: translateX(100%);
    }
  }
  
  .fade-in-up {
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.8s ease forwards;
  }
  
  .fade-in-up:nth-child(1) { animation-delay: 0.1s; }
  .fade-in-up:nth-child(2) { animation-delay: 0.3s; }
  .fade-in-up:nth-child(3) { animation-delay: 0.5s; }
  .fade-in-up:nth-child(4) { animation-delay: 0.7s; }
  
  @keyframes fadeInUp {
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  
  .glow {
    position: absolute;
    width: 200px;
    height: 200px;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.3;
    z-index: 0;
    pointer-events: none;
  }
  
  .glow-1 {
    background: #667eea;
    top: -100px;
    right: -100px;
  }
  
  .glow-2 {
    background: #f5576c;
    bottom: -100px;
    left: -100px;
  }
</style>

<div class="hero-wrap p-4 p-md-5 position-relative">
  <!-- Background Glow Effects -->
  <div class="glow glow-1"></div>
  <div class="glow glow-2"></div>
  
  <div class="card hero-card p-4 p-md-5">
    <div class="row g-5 align-items-center">
      <!-- LEFT HERO -->
      <div class="col-lg-7">
        <div class="d-flex align-items-center gap-3 mb-4 fade-in-up">
          <div class="icon-badge">📚</div>
          <div>
            <div class="chip">School Library Management System</div>
          </div>
        </div>

        <h1 class="fw-bold display-5 mb-3 fade-in-up">
          Welcome to <span class="gradient-text">School Library</span>
        </h1>
        <p class="text-muted fs-5 mb-4 fade-in-up">
          Manage books, issue & return, and track loans easily with a clean dashboard for Admin and Users.
        </p>

        <div class="d-flex flex-wrap gap-3 mb-4">
          <span class="chip floating-element">✅ Admin Panel</span>
          <span class="chip floating-element">✅ User Catalog</span>
          <span class="chip floating-element">✅ Issue / Return</span>
          <span class="chip floating-element">✅ Fine Calculation</span>
        </div>

        <div class="d-flex flex-wrap gap-3 fade-in-up">
          <?php if (!is_logged_in()): ?>
            <a class="btn btn-dark btn-pill" href="/school_library/login.php">
              <span class="position-relative">Login</span>
            </a>
            <a class="btn btn-outline-dark btn-pill" href="/school_library/register.php">
              <span class="position-relative">Create Account</span>
            </a>
          <?php else: ?>
            <?php if (current_user()['role'] === 'admin'): ?>
              <a class="btn btn-dark btn-pill" href="/school_library/admin/dashboard.php">
                <span class="position-relative">Go to Admin Dashboard</span>
              </a>
              <a class="btn btn-outline-dark btn-pill" href="/school_library/admin/books.php">
                <span class="position-relative">Manage Books</span>
              </a>
            <?php else: ?>
              <a class="btn btn-dark btn-pill" href="/school_library/user/dashboard.php">
                <span class="position-relative">Go to My Dashboard</span>
              </a>
              <a class="btn btn-outline-dark btn-pill" href="/school_library/user/catalog.php">
                <span class="position-relative">Browse Catalog</span>
              </a>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- RIGHT SIDE CARDS -->
      <div class="col-lg-5">
        <div class="mini-card p-4 mb-4 floating-element">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h5 class="fw-bold mb-1">Default Admin</h5>
              <div class="text-muted small mb-2">Use this to access the Admin panel</div>
            </div>
            <span class="badge bg-gradient-primary rounded-pill px-3 py-2" style="background: var(--gradient-1); border: none;">
              ADMIN
            </span>
          </div>

          

          <div class="alert alert-info mt-3 mb-0 rounded-4 border-0" style="background: rgba(13, 110, 253, 0.08);">
            <div class="d-flex align-items-center gap-2">
              <span class="fs-5">💡</span>
              <div class="small">Tip: Register as a user, then admin can issue books to your account.</div>
            </div>
          </div>
        </div>

        <div class="mini-card p-4 floating-element">
          <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
            <span>⚙️</span> How it works
          </h6>
          
          <div class="d-flex gap-3 mb-3">
            <div class="feature-dot"></div>
            <div>
              <div class="fw-semibold">User registers</div>
              <div class="text-muted small">Create a user account and login.</div>
            </div>
          </div>
          
          <div class="d-flex gap-3 mb-3">
            <div class="feature-dot"></div>
            <div>
              <div class="fw-semibold">Admin adds books</div>
              <div class="text-muted small">Books, categories and copies are managed.</div>
            </div>
          </div>
          
          <div class="d-flex gap-3">
            <div class="feature-dot"></div>
            <div>
              <div class="fw-semibold">Issue & return</div>
              <div class="text-muted small">Copies update automatically and fines are calculated.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  // Add scroll-triggered animations
  document.addEventListener('DOMContentLoaded', function() {
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }
      });
    }, observerOptions);

    // Observe elements for scroll animations
    document.querySelectorAll('.fade-in-up').forEach(el => {
      observer.observe(el);
    });
  });
</script>

<?php require_once __DIR__ . "/includes/footer.php"; ?>