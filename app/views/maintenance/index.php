<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode | <?= $appName ?? 'Sistem Manajemen Kos' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
        }
        
        .maintenance-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 25px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            max-width: 600px;
            width: 90%;
            position: relative;
            backdrop-filter: blur(10px);
        }
        
        .maintenance-icon {
            font-size: 5rem;
            color: #667eea;
            margin-bottom: 2rem;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
        
        .maintenance-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        
        .maintenance-subtitle {
            font-size: 1.2rem;
            color: #667eea;
            margin-bottom: 2rem;
            font-weight: 500;
        }
        
        .maintenance-description {
            color: #6c757d;
            margin-bottom: 2.5rem;
            line-height: 1.6;
            font-size: 1rem;
        }
        
        .estimated-time {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-left: 5px solid #667eea;
        }
        
        .estimated-time h6 {
            color: #495057;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .estimated-time p {
            color: #6c757d;
            margin: 0;
            font-size: 0.95rem;
        }
        
        .contact-info {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .contact-info h6 {
            color: #1976d2;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            color: #424242;
        }
        
        .contact-item i {
            margin-right: 0.5rem;
            color: #1976d2;
            width: 20px;
        }
        
        .social-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            transition: transform 0.3s ease;
            font-size: 1.2rem;
        }
        
        .social-link:hover {
            transform: translateY(-3px);
            color: white;
            text-decoration: none;
        }
        
        .status-indicator {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }
        
        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 70%;
            right: 10%;
            animation-delay: 2s;
        }
        
        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 15%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }
        
        .progress-container {
            margin-top: 2rem;
        }
        
        .progress {
            height: 8px;
            border-radius: 4px;
            background: #e9ecef;
        }
        
        .progress-bar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 4px;
            animation: progress 8s ease-in-out infinite;
        }
        
        @keyframes progress {
            0%, 100% { width: 30%; }
            50% { width: 70%; }
        }
        
        .last-updated {
            font-size: 0.85rem;
            color: #adb5bd;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <!-- Floating Background Shapes -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <div class="maintenance-container">
        <!-- Status Indicator -->
        <div class="status-indicator">
            <i class="bi bi-tools"></i>
            Under Maintenance
        </div>
        
        <!-- Maintenance Icon -->
        <div class="maintenance-icon">
            <i class="bi bi-gear-fill"></i>
        </div>
        
        <!-- Main Content -->
        <h1 class="maintenance-title">Sedang Maintenance</h1>
        <p class="maintenance-subtitle">We'll be back soon!</p>
        
        <div class="maintenance-description">
            <p>Maaf atas ketidaknyamanannya. Kami sedang melakukan pemeliharaan sistem untuk meningkatkan kualitas layanan dan performa aplikasi. Sistem akan kembali normal secepatnya.</p>
        </div>
        
        <!-- Estimated Time -->
        <div class="estimated-time">
            <h6><i class="bi bi-clock"></i> Estimasi Waktu</h6>
            <p>Pemeliharaan sistem diperkirakan selesai dalam <strong>2-4 jam</strong></p>
            <p>Terakhir diperbarui: <span id="current-time"><?= date('d M Y, H:i') ?> WIB</span></p>
        </div>
        
        <!-- Progress Bar -->
        <div class="progress-container">
            <div class="progress">
                <div class="progress-bar" role="progressbar"></div>
            </div>
            <small class="text-muted">Memperbarui sistem...</small>
        </div>
        
        <!-- Contact Information -->
        <div class="contact-info">
            <h6><i class="bi bi-headset"></i> Bantuan & Informasi</h6>
            <div class="contact-item">
                <i class="bi bi-envelope"></i>
                <span>admin@sistemkos.com</span>
            </div>
            <div class="contact-item">
                <i class="bi bi-telephone"></i>
                <span>+62 812-3456-7890</span>
            </div>
            <div class="contact-item">
                <i class="bi bi-globe"></i>
                <span>www.sistemkos.com</span>
            </div>
        </div>
        
        <!-- Social Links -->
        <div class="social-links">
            <a href="#" class="social-link" title="Facebook">
                <i class="bi bi-facebook"></i>
            </a>
            <a href="#" class="social-link" title="Twitter">
                <i class="bi bi-twitter"></i>
            </a>
            <a href="#" class="social-link" title="Instagram">
                <i class="bi bi-instagram"></i>
            </a>
            <a href="#" class="social-link" title="WhatsApp">
                <i class="bi bi-whatsapp"></i>
            </a>
        </div>
        
        <!-- Last Updated -->
        <div class="last-updated">
            <i class="bi bi-info-circle"></i>
            Halaman ini akan otomatis refresh setiap 30 detik
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto refresh every 30 seconds
        setTimeout(function() {
            location.reload();
        }, 30000);
        
        // Update current time every minute
        setInterval(function() {
            const now = new Date();
            const options = {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                timeZone: 'Asia/Jakarta'
            };
            document.getElementById('current-time').textContent = 
                now.toLocaleDateString('id-ID', options) + ' WIB';
        }, 60000);
        
        // Add some interactive effects
        document.querySelector('.maintenance-icon').addEventListener('click', function() {
            this.style.animation = 'none';
            setTimeout(() => {
                this.style.animation = 'bounce 2s infinite';
            }, 100);
        });
        
        // Simulate progress updates
        let progress = 30;
        setInterval(function() {
            progress += Math.random() * 5;
            if (progress > 95) progress = 30;
            document.querySelector('.progress-bar').style.width = progress + '%';
        }, 3000);
    </script>
</body>
</html>