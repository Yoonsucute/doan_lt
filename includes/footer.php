<footer class="app-footer mt-5 py-4">
    <div class="container d-flex flex-column flex-md-row justify-content-between gap-2">
        <span>Share Do An - cong dong chia se source code.</span>
        <span class="text-muted">Bao mat CSRF, XSS, prepared statement va upload validation.</span>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>window.APP_BASE = <?php echo json_encode(rtrim(base_url(''), '/')); ?>;</script>
<script src="<?php echo e(base_url('assets/js/chatbot.js')); ?>"></script>
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
