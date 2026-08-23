<!-- ================= FOOTER ================= -->
<footer class="site-footer" id="contact">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="footer-logo">
                    <img src="<?= logo_white_url() ?>" alt="<?= e(setting('site_name')) ?>" onerror="this.style.display='none'">
                </div>
                <p class="footer-about"><?= e(c('footer_about')) ?></p>
            </div>

            <div class="footer-col">
                <h4>Link</h4>
                <ul>
                    <li><a href="<?= BASE_URL ?>index.php"><?= e(c('nav_home')) ?></a></li>
                    <li><a href="#collection"><?= e(c('nav_gemstones')) ?></a></li>
                    <li><a href="#journey"><?= e(c('nav_about')) ?></a></li>
                    <li><a href="#contact"><?= e(c('nav_contact')) ?></a></li>
                </ul>
            </div>

            <div class="footer-col footer-contact">
                <h4>Get in touch</h4>
                <p><?= e_nl(c('footer_address')) ?></p>
                <p>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 6 10 7L22 6"/></svg>
                    <a href="mailto:<?= e(c('footer_email')) ?>"><?= e(c('footer_email')) ?></a>
                </p>
                <p>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.6A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.6a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.5-1.2a2 2 0 0 1 2.1-.5c.8.3 1.7.6 2.6.7a2 2 0 0 1 1.7 2z"/></svg>
                    <span><?= e(c('footer_phone1')) ?><br><?= e(c('footer_phone2')) ?></span>
                </p>
            </div>

            <div class="footer-col footer-social">
                <h4>Find Us</h4>
                <ul>
                    <li>
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M13 22v-8h3l.5-3.5H13V8.3c0-1 .3-1.7 1.8-1.7H17V3.5c-.3 0-1.4-.1-2.6-.1-2.6 0-4.4 1.6-4.4 4.5v2.6H7V14h3v8z"/></svg>
                        <a href="<?= e(c('footer_facebook')) ?>" target="_blank" rel="noopener">Facebook</a>
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg>
                        <a href="<?= e(c('footer_instagram')) ?>" target="_blank" rel="noopener">Instagram</a>
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 3c.3 2.3 1.7 3.9 4 4v3c-1.4 0-2.8-.4-4-1.1V15a6 6 0 1 1-6-6c.3 0 .7 0 1 .1v3.2A2.8 2.8 0 1 0 13 15V3z"/></svg>
                        <a href="<?= e(c('footer_tiktok')) ?>" target="_blank" rel="noopener">Tiktok</a>
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M22 5.9c-.7.3-1.5.5-2.3.6.8-.5 1.5-1.3 1.8-2.3-.8.5-1.7.8-2.6 1a4 4 0 0 0-6.9 3.7A11.4 11.4 0 0 1 3.8 4.6a4 4 0 0 0 1.2 5.4c-.6 0-1.2-.2-1.8-.5a4 4 0 0 0 3.2 4c-.5.1-1.1.2-1.7.1a4 4 0 0 0 3.7 2.8A8 8 0 0 1 2 18.1 11.3 11.3 0 0 0 8.1 20c7.4 0 11.5-6.2 11.5-11.5v-.5c.8-.6 1.5-1.3 2-2.1z"/></svg>
                        <a href="<?= e(c('footer_twitter')) ?>" target="_blank" rel="noopener">Twitter</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <span><?= e(c('footer_copyright')) ?></span>
            <div class="links">
                <a href="#">Terms and Conditions</a>
                <a href="#">Privacy Policy</a>
            </div>
        </div>
    </div>
</footer>

<script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>
