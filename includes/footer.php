<footer class="app-footer mt-5 py-4">
    <div class="container d-flex flex-column flex-md-row justify-content-between gap-2">
        <span>CodeDoAn - kho source code và đồ án lập trình.</span>
        <span class="text-muted">Bảo mật CSRF, XSS, prepared statement và upload validation.</span>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>window.APP_BASE = <?php echo json_encode(rtrim(base_url(''), '/')); ?>;</script>
<?php $chatbotVersion = is_file(__DIR__ . '/../assets/js/chatbot.js') ? filemtime(__DIR__ . '/../assets/js/chatbot.js') : time(); ?>
<script src="<?php echo e(base_url('assets/js/chatbot.js?v=' . $chatbotVersion)); ?>"></script>
<script>
    document.querySelectorAll('.toast').forEach((el) => new bootstrap.Toast(el, {delay: 3500}).show());
    window.addEventListener('load', () => document.getElementById('page-loader')?.classList.add('hidden'));

    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.dataset.theme = savedTheme;
    document.getElementById('themeToggle')?.addEventListener('click', () => {
        const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.theme = nextTheme;
        localStorage.setItem('theme', nextTheme);
    });

    if (document.querySelector('.hero-swiper')) {
        new Swiper('.hero-swiper', {
            loop: true,
            autoplay: {delay: 3200},
            pagination: {el: '.swiper-pagination', clickable: true}
        });
    }
</script>
</body>
</html>
