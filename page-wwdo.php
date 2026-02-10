<?php
/*
Template Name: Bau Cua Demo
*/
get_header();
?>


<div class="bc-wrap">
    <h1>Bầu Cua – Demo tính 216 kết quả</h1>


    <!-- SECTION 1: BÀN BẦU CUA -->
    <section class="bc-section bc-input">
        <h2>🎲 Bàn Bầu Cua</h2>
        <p>Nhập giá trị đặt cho từng mặt</p>


        <form id="bc-form">
            <div class="bc-grid">
                <?php for ($i = 1; $i <= 6; $i++): ?>
                <div class="bc-item">
                    <span class="bc-face"><?php echo $faces[$i]; ?></span>
                    <input type="number" name="face[<?php echo $i; ?>]" value="<?php echo $i * 10; ?>" min="0" />
                </div>
                <?php endfor; ?>
            </div>


            <button type="button" id="bc-roll">🎲 Xóc</button>
        </form>
    </section>


    <!-- SECTION 2: KẾT QUẢ -->
    <section class="bc-section bc-output">
        <h2 class="bc-toggle">📊 Kết quả (click để mở / đóng)</h2>
        <div id="bc-result" class="bc-result hidden"></div>
    </section>
</div>


<?php
wp_enqueue_style('bc-style', get_template_directory_uri() . '/css/wwdo.css');
wp_enqueue_script('bc-script', get_template_directory_uri() . '/js/wwdo.js', [], false, true);
get_footer(); ?>