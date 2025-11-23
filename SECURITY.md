# Security Documentation

Dokumentasi ini menjelaskan langkah-langkah keamanan yang telah diimplementasikan dalam sistem inventory ini.

## 🔒 Fitur Keamanan yang Diimplementasikan

### 1. Security Headers Middleware
**File:** `app/Http/Middleware/SecurityHeaders.php`

Middleware ini menambahkan berbagai security headers pada setiap response:
- **X-Frame-Options**: Mencegah clickjacking (SAMEORIGIN)
- **X-Content-Type-Options**: Mencegah MIME type sniffing (nosniff)
- **X-XSS-Protection**: Mengaktifkan XSS protection browser
- **Referrer-Policy**: Mengontrol informasi referrer yang dikirim
- **Permissions-Policy**: Membatasi akses ke fitur browser tertentu
- **Content-Security-Policy**: Membatasi sumber daya yang dapat dimuat
- **Strict-Transport-Security (HSTS)**: Memaksa HTTPS di production

### 2. Session Security
**File:** `config/session.php`

- ✅ **Session Encryption**: Diaktifkan (`SESSION_ENCRYPT=true`)
- ✅ **HTTP Only Cookies**: Mencegah akses JavaScript ke cookies
- ✅ **Secure Cookies**: Otomatis diaktifkan saat HTTPS
- ✅ **Same-Site Cookies**: Mencegah CSRF attacks
- ✅ **Session Timeout**: Middleware untuk auto-logout setelah idle

### 3. Rate Limiting
**File:** `routes/api.php`

- ✅ **Login Endpoint**: 5 attempts per minute
- ✅ **API Endpoints**: 60 requests per minute untuk authenticated users
- ✅ **Throttling**: Mencegah brute force attacks

### 4. Password Policy
**Files:** 
- `app/Services/UserService.php`
- `app/Http/Controllers/Auth/RegisterController.php`

Password requirements:
- ✅ Minimum 8 karakter
- ✅ Harus mengandung huruf (letters)
- ✅ Harus mengandung huruf besar dan kecil (mixedCase)
- ✅ Harus mengandung angka (numbers)
- ✅ Harus mengandung simbol (symbols)
- ✅ Tidak boleh menggunakan password yang terkompromi (uncompromised)

### 5. Authorization & Permissions
**File:** `app/Http/Middleware/CheckPermission.php`

- ✅ Middleware untuk mengecek permission sebelum akses resource
- ✅ Terintegrasi dengan Spatie Permission package
- ✅ Logging untuk permission denied events

**Penggunaan:**
```php
Route::middleware(['auth', 'permission:manage-users'])->group(function () {
    // Routes yang memerlukan permission
});
```

### 6. File Upload Security
**File:** `app/Services/ItemService.php`

Validasi file upload yang ketat:
- ✅ Validasi MIME type (bukan hanya extension)
- ✅ Validasi file extension
- ✅ Validasi ukuran file (max 2MB)
- ✅ Generate filename yang aman (tanpa user input)
- ✅ Verifikasi file adalah gambar yang valid
- ✅ Secure directory permissions (0750)

### 7. API Token Security
**File:** `config/sanctum.php`

- ✅ **Token Expiration**: 24 jam (1440 menit)
- ✅ **Token Prefix**: Untuk secret scanning
- ✅ **Token Revocation**: Otomatis saat logout

### 8. Security Logging
**File:** `app/Services/SecurityLogService.php`

Logging untuk event keamanan penting:
- ✅ Failed login attempts
- ✅ Successful logins
- ✅ Logout events
- ✅ Permission denied
- ✅ Suspicious activities
- ✅ Data access events

**Log Channel:** `storage/logs/security.log` (disimpan 90 hari)

### 9. Input Validation
Semua input divalidasi menggunakan Laravel Validation:
- ✅ Request validation di semua controllers
- ✅ Service-level validation rules
- ✅ SQL injection protection (menggunakan Eloquent ORM)
- ✅ XSS protection (Blade auto-escaping)

### 10. CSRF Protection
- ✅ CSRF protection aktif secara default di Laravel
- ✅ Token validation untuk semua POST/PUT/DELETE requests
- ✅ Session-based CSRF tokens

## 🛡️ Best Practices yang Diterapkan

### Authentication
- ✅ Password hashing menggunakan bcrypt (12 rounds)
- ✅ Session regeneration setelah login
- ✅ Remember me functionality dengan secure tokens
- ✅ Login throttling untuk mencegah brute force

### Authorization
- ✅ Role-based access control (RBAC) menggunakan Spatie Permission
- ✅ Permission checks di middleware dan controllers
- ✅ Resource-based authorization

### Data Protection
- ✅ Mass assignment protection (fillable/guarded)
- ✅ Password hidden dari serialization
- ✅ Sensitive data tidak di-log

### API Security
- ✅ Sanctum token authentication
- ✅ Rate limiting
- ✅ Token expiration
- ✅ CORS configuration (jika diperlukan)

## 📋 Checklist Keamanan

### Environment Configuration
- [ ] Set `APP_ENV=production` di production
- [ ] Set `APP_DEBUG=false` di production
- [ ] Generate `APP_KEY` yang unik
- [ ] Set `SESSION_SECURE_COOKIE=true` jika menggunakan HTTPS
- [ ] Konfigurasi database credentials dengan aman
- [ ] Jangan commit file `.env` ke repository

### Server Configuration
- [ ] Gunakan HTTPS di production
- [ ] Konfigurasi firewall
- [ ] Update dependencies secara berkala
- [ ] Backup database secara rutin
- [ ] Monitor security logs

### Application Security
- [ ] Review dan update permissions secara berkala
- [ ] Monitor failed login attempts
- [ ] Review security logs secara berkala
- [ ] Update password policy jika diperlukan
- [ ] Audit user permissions

## 🔍 Monitoring & Auditing

### Security Logs
Security logs tersimpan di `storage/logs/security.log` dan mencakup:
- IP address
- User agent
- Timestamp
- Action yang dilakukan
- Resource yang diakses

### Log Retention
- Security logs disimpan selama 90 hari
- Logs di-rotate secara otomatis (daily)

## 🚨 Incident Response

Jika terjadi security incident:
1. Cek security logs untuk aktivitas mencurigakan
2. Blokir IP address jika diperlukan
3. Reset password user yang terpengaruh
4. Review dan update permissions
5. Notifikasi admin jika diperlukan

## 📚 Referensi

- [Laravel Security Documentation](https://laravel.com/docs/security)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Spatie Permission Documentation](https://spatie.be/docs/laravel-permission)
- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)

## 🔄 Update Security

Untuk memperbarui keamanan sistem:
1. Update dependencies secara berkala: `composer update`
2. Review security advisories
3. Update password policy jika diperlukan
4. Review dan update security headers
5. Audit permissions dan roles

---

**Last Updated:** {{ date('Y-m-d') }}
**Version:** 1.0

