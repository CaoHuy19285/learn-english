<style>
   /* Cấu trúc chính của Footer */
.wordwise-footer {
    background-color: #f3f4f6; 
    color: #4b5563;
    font-family: 'Inter', sans-serif;
    padding: 60px 0 20px 0;
    border-top: 1px solid #e5e7eb;
    font-size: 14px;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 30px;
}

/* Khu vực trên: 4 cột - căn đều */
.footer-top {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-evenly; /* Căn đều các cột */
    padding-bottom: 40px;
    border-bottom: 1px solid #e5e7eb;
}

.footer-col {
    flex: 1; /* Tất cả cột đều rộng bằng nhau */
    min-width: 180px;
    padding: 0 15px; /* Khoảng cách đều bên trong */
    box-sizing: border-box;
}

/* Logo */
.footer-logo {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    margin-bottom: 18px;
}
.footer-logo img {
    height: 36px;
    width: auto;
}
.footer-logo span {
    font-size: 20px;
    font-weight: 800;
    color: #1e1b4b;
}

.footer-description {
    line-height: 1.6;
    margin-bottom: 22px;
    color: #4b5563;
}

/* Tiêu đề cột */
.footer-heading {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 20px;
}

/* Danh sách links */
.footer-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.footer-list li {
    margin-bottom: 14px;
}
.footer-list a {
    color: #4b5563;
    text-decoration: none;
    transition: color 0.2s;
}
.footer-list a:hover {
    color: #4f46e5;
}

/* Cột Liên hệ - tăng khoảng cách */
.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 16px;
    line-height: 1.6;
}
.contact-item svg {
    flex-shrink: 0;
    margin-top: 3px;
    width: 16px;
    height: 16px;
    color: #4f46e5;
}
.contact-item span {
    display: block;
    line-height: 1.5; /* Tăng khoảng cách dòng cho địa chỉ */
}

/* Footer dưới */
.footer-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    padding-top: 25px;
    font-size: 13px;
    color: #6b7280;
}
.footer-bottom-links {
    display: flex;
    gap: 20px;
}
.footer-bottom-links a {
    color: #6b7280;
    text-decoration: none;
    transition: color 0.2s;
}
.footer-bottom-links a:hover {
    color: #111827;
}

/* Responsive */
@media (max-width: 768px) {
    .footer-top {
        flex-direction: column;
        align-items: stretch;
        gap: 30px;
    }
    .footer-col {
        padding: 0;
    }
    .footer-bottom {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    .footer-bottom-links {
        justify-content: center;
        flex-wrap: wrap;
    }
}
</style>

<footer class="wordwise-footer">
    <div class="footer-container">
        <div class="footer-top">
            
            <!-- Cột thương hiệu (đã xóa social-links) -->
            <div class="footer-col">
                <a href="index.php" class="footer-logo">
                    <img src="public/images/CARD MOI.png" alt="WordWise Logo">
                    <span>WordWise</span>
                </a>
                <p class="footer-description">
                    Chúng tôi thiết kế và phát triển nền tảng học từ vựng trực quan — từ phương pháp ghi nhớ khoa học đến kho tàng từ vựng phong phú — xây dựng để giúp bạn chinh phục tiếng Anh dễ dàng.
                </p>
            </div>

            <div class="footer-col">
                <h4 class="footer-heading">Học tập</h4>
                <ul class="footer-list">
                    <li><a href="#">Kho từ vựng</a></li>
                    <li><a href="#">Ngữ pháp cơ bản</a></li>
                    <li><a href="#">Bài tập trắc nghiệm</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4 class="footer-heading">Về WordWise</h4>
                <ul class="footer-list">
                    <li><a href="introduce.php">Giới thiệu</a></li>
                    <li><a href="method.php">Phương pháp học</a></li>
                    <li><a href="#">Blog tiếng Anh</a></li>
                    <li><a href="#">Case study</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4 class="footer-heading">Liên hệ</h4>
                <div class="contact-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    <span>72 Nguyen Hue Boulevard, District 1, Ho Chi Minh City 700000, Vietnam</span>
                </div>
                <div class="contact-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    <span>+84 28 3822 4567</span>
                </div>
                <div class="contact-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    <span>support@wordwise.edu.vn</span>
                </div>
                <div class="contact-item" style="margin-top: 20px; color: #6b7280; font-size: 14px;">
                    Mon – Fri, 9:00 AM – 6:00 PM (ICT)
                </div>
            </div>
            
        </div>

        <div class="footer-bottom">
            <div class="copyright">
                © 2026 WordWise System. Bảo lưu mọi quyền.
            </div>
            <div class="footer-bottom-links">
                <a href="#">Chính sách bảo mật</a>
                <a href="#">Điều khoản dịch vụ</a>
                <a href="#">Chính sách cookie</a>
            </div>
        </div>
    </div>
</footer>