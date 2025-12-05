<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Kamil Maju Persada</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="icon" type="image/png" href="{{ asset('assets/image/logo/ptkmp-logo.png') }}">
  
  <!-- Cloudflare Turnstile -->
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>
<body class="min-h-screen bg-gray-50">

  <div class="min-h-screen flex">
    
    <!-- Left Panel - Welcome Section -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-green-600 via-green-500 to-green-700 relative overflow-hidden">
      
      <!-- Floating Shapes -->
      <div class="absolute top-20 left-10 w-32 h-32 bg-white/10 rounded-full animate-pulse"></div>
      <div class="absolute top-40 right-20 w-20 h-20 bg-white/5 rounded-full animate-bounce"></div>
      <div class="absolute bottom-32 left-20 w-24 h-24 bg-white/10 rounded-full animate-ping"></div>
      
      <!-- Content -->
      <div class="flex flex-col justify-center items-start p-12 z-10 text-white">
        
       <!-- Logo -->
        <div class="flex items-center mb-8">
          <div class="w-36 h-36 bg-white rounded-full flex items-center justify-center mr-3 overflow-hidden">
                <img src="{{ asset('assets/image/logo/ptkmp-logo.png') }}" alt="Logo PT KMP" class="w-32 h-32 object-contain">
            </div>
          <span class="text-4xl font-bold">PT Kamil Maju Persada</span>
        </div>

        <h1 class="text-7xl font-bold mb-4 leading-tight">
          Selamat Datang!
        </h1>
        
      </div>
    </div>

    <!-- Right Panel - Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
      
      <div class="w-full max-w-md">
        
        <!-- Mobile Logo -->
        <div class="lg:hidden text-center mb-8">
          <div class="w-32 h-32 mx-auto flex items-center justify-center rounded-full bg-white shadow-lg mb-4">
            <img src="{{ asset('assets/image/logo/ptkmp-logo.png') }}" 
                alt="Logo PT KMP" 
                class="w-28 h-28 object-contain">
          </div>
          <h2 class="text-2xl font-bold text-gray-800">PT Kamil Maju Persada</h2>
        </div>


        <!-- Welcome Text -->
        <div class="mb-8">
          <h3 class="text-2xl font-bold text-gray-800 mb-2">Masuk ke Akun Anda</h3>
        </div>

        <!-- Status Message (Hidden by default) -->
        <div id="status-message" class="mb-4 p-4 rounded-xl border border-green-200 bg-green-50 text-green-700 text-sm hidden">
          <i class="fas fa-exclamation-circle mr-2"></i>
          <span id="status-text"></span>
        </div>

        <!-- Form -->
        <form id="login-form" class="space-y-6" action="{{ route('login.attempt') }}" method="POST">
          @csrf
          
          <!-- Email/Username -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
              Email / Username
            </label>
            <div class="relative">
              <input
                id="email"
                name="email"
                type="text"
                required
                autofocus
                placeholder="Masukkan email atau username"
                class="w-full rounded-xl border border-gray-300 pl-12 pr-4 py-3 text-gray-700 placeholder-gray-400 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200"
              >
              <div class="absolute left-4 top-1/2 -translate-y-1/2">
                <i class="fas fa-user text-gray-400"></i>
              </div>
            </div>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
              Password
            </label>
            <div class="relative">
              <input
                id="password"
                name="password"
                type="password"
                required
                placeholder="Masukkan password"
                class="w-full rounded-xl border border-gray-300 pl-12 pr-12 py-3 text-gray-700 placeholder-gray-400 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200"
              >
              <div class="absolute left-4 top-1/2 -translate-y-1/2">
                <i class="fas fa-lock text-gray-400"></i>
              </div>
              <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                <i id="eye" class="fas fa-eye"></i>
              </button>
            </div>
          </div>

          <!-- Cloudflare Turnstile CAPTCHA -->
          <div>
            <div class="cf-turnstile" 
                 data-sitekey="{{ config('services.turnstile.site_key') }}"
                 data-callback="onTurnstileSuccess"
                 data-expired-callback="onTurnstileExpired"
                 data-error-callback="onTurnstileError">
            </div>
            <div id="captcha-error" class="hidden mt-2 text-sm text-red-600">
              <i class="fas fa-exclamation-circle mr-1"></i>
              <span>Mohon verifikasi bahwa Anda bukan robot.</span>
            </div>
          </div>

          <!-- Submit Button -->
          <button type="submit" id="submit-btn" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-xl font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fas fa-sign-in-alt mr-2"></i>
            Masuk
          </button>
        </form>

        <!-- Divider -->
        <div class="my-8 flex items-center">
          <div class="flex-1 border-t border-gray-200"></div>
          <div class="flex-1 border-t border-gray-200"></div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center">
          <p class="text-xs text-gray-500">
            © 2025 PT. Kamil Maju Persada. All rights reserved.
          </p>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Turnstile CAPTCHA variables
    let turnstileToken = null;

    // Turnstile callbacks
    function onTurnstileSuccess(token) {
      turnstileToken = token;
      document.getElementById('captcha-error').classList.add('hidden');
      document.getElementById('submit-btn').disabled = false;
    }

    function onTurnstileExpired() {
      turnstileToken = null;
      document.getElementById('submit-btn').disabled = true;
      showMessage('CAPTCHA telah kadaluarsa. Mohon verifikasi ulang.');
    }

    function onTurnstileError() {
      turnstileToken = null;
      document.getElementById('submit-btn').disabled = true;
      showMessage('Terjadi kesalahan pada verifikasi CAPTCHA. Silakan refresh halaman.');
    }

    function togglePassword() {
      const input = document.getElementById('password');
      const eye = document.getElementById('eye');
      if (input.type === 'password') {
        input.type = 'text';
        eye.classList.remove('fa-eye');
        eye.classList.add('fa-eye-slash');
      } else {
        input.type = 'password';
        eye.classList.remove('fa-eye-slash');
        eye.classList.add('fa-eye');
      }
    }

    function showMessage(message, type = 'error') {
      const statusDiv = document.getElementById('status-message');
      const statusText = document.getElementById('status-text');
      const icon = statusDiv.querySelector('i');
      
      statusText.textContent = message;
      statusDiv.classList.remove('hidden');
      
      if (type === 'success') {
        statusDiv.className = 'mb-4 p-4 rounded-xl border border-green-200 bg-green-50 text-green-700 text-sm';
        icon.className = 'fas fa-check-circle mr-2';
      } else if (type === 'warning') {
        statusDiv.className = 'mb-4 p-4 rounded-xl border border-yellow-200 bg-yellow-50 text-yellow-700 text-sm';
        icon.className = 'fas fa-exclamation-triangle mr-2';
      } else {
        statusDiv.className = 'mb-4 p-4 rounded-xl border border-red-200 bg-red-50 text-red-700 text-sm';
        icon.className = 'fas fa-times-circle mr-2';
      }
      
      // Hide message after 5 seconds
      setTimeout(() => {
        statusDiv.classList.add('hidden');
      }, 5000);
    }

    // Form submission handling
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('login-form');
      const submitButton = document.getElementById('submit-btn');
      
      // Disable submit button initially until CAPTCHA is completed
      submitButton.disabled = true;
      
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        
        // Validate inputs
        if (!email || !password) {
          showMessage('Mohon lengkapi semua field yang diperlukan.');
          return;
        }
        
        // Validate CAPTCHA
        if (!turnstileToken) {
          document.getElementById('captcha-error').classList.remove('hidden');
          showMessage('Mohon verifikasi bahwa Anda bukan robot.');
          return;
        }
        
        // Submit login request
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
        
        const formData = new FormData(form);
        formData.append('cf-turnstile-response', turnstileToken);
        
        fetch(form.action, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
          }
        })
        .then(response => {
          // Check if response is 403 (account not active)
          if (response.status === 403) {
            return response.json().then(data => {
              showMessage(data.message, 'warning');
              throw new Error('Account not active');
            });
          }
          return response.json();
        })
        .then(data => {
          if (data.success) {
            showMessage(data.message, 'success');
            setTimeout(() => {
              window.location.href = data.redirect;
            }, 1500);
          } else {
            showMessage(data.message);
            // Reset Turnstile on error
            if (window.turnstile) {
              window.turnstile.reset();
              turnstileToken = null;
            }
          }
        })
        .catch(error => {
          console.error('Error:', error);
          if (error.message !== 'Account not active') {
            showMessage('Terjadi kesalahan. Silakan coba lagi.');
          }
          // Reset Turnstile on error
          if (window.turnstile) {
            window.turnstile.reset();
            turnstileToken = null;
          }
        })
        .finally(() => {
          // Reset button
          submitButton.disabled = false;
          submitButton.innerHTML = '<i class="fas fa-sign-in-alt mr-2"></i>Masuk';
        });
      });

      // Add interactive feedback for inputs
      const inputs = document.querySelectorAll('input[type="text"], input[type="password"]');
      inputs.forEach(input => {
        input.addEventListener('focus', function() {
          this.parentElement.classList.add('ring-2', 'ring-green-500');
        });
        
        input.addEventListener('blur', function() {
          this.parentElement.classList.remove('ring-2', 'ring-green-500');
        });
      });
    });
  </script>
</body>
</html>