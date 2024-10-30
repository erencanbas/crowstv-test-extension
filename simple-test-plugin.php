<?php
/*
Plugin Name: Test Plugin
Description: A plugin to create and display tests and result calculation.
Version: 1.0
Author: Crows Tv
*/

// Eklentinin dosya yollarını dahil ediyoruz
require_once plugin_dir_path(__FILE__) . 'includes/database.php';
require_once plugin_dir_path(__FILE__) . 'admin/menu.php';
require_once plugin_dir_path(__FILE__) . 'includes/enqueue.php';

// Eklenti etkinleştirildiğinde veritabanı tablolarını oluştur
//register_activation_hook(__FILE__, 'sqp_create_tables');

// CSS ve JS dosyalarını dahil et
add_action('admin_enqueue_scripts', 'sqp_enqueue_admin_scripts');

// "Test" içerik türünü kaydediyoruz
function sqp_register_test_post_type()
{
    $labels = array(
        'name' => 'Tests',
        'singular_name' => 'Test',
        'menu_name' => 'Tests',
        'name_admin_bar' => 'Test',
        'add_new' => 'Add New Test',
        'add_new_item' => 'Add New Test (Henüz aktif değil)',
        'new_item' => 'New Test',
        'edit_item' => 'Edit Test',
        'view_item' => 'View Test',
        'all_items' => 'All Tests',
        'search_items' => 'Search Test',
        'not_found' => 'Test not found.',
        'not_found_in_trash' => 'No test found in the trash.'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'test'),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => 5,
        'supports' => array('title', 'editor', 'thumbnail', 'revisions')
    );

    register_post_type('test', $args);
}
add_action('init', 'sqp_register_test_post_type');

// "Test Soruları" adlı meta box'u ekliyoruz
function sqp_add_test_meta_boxes()
{
    add_meta_box(
        'sqp_test_questions',
        'Test Questions',
        'sqp_render_test_questions_meta_box',
        'test',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'sqp_add_test_meta_boxes');

function sqp_add_test_result_meta_boxes() 
{
    add_meta_box(
        'sqp_test_results',
        'Question Results',
        'sqp_render_test_results_meta_box',
        'test',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes','sqp_add_test_result_meta_boxes');

function sqp_render_test_results_meta_box($post) {
    wp_nonce_field('sqp_save_test_results', 'sqp_test_results_nonce');
    
    // Mevcut sonuçları al
    $results = get_post_meta($post->ID, '_sqp_test_results', true);

    // WordPress medya kitaplığı fonksiyonlarını ekle
    wp_enqueue_media();
    ?>

    <div id="results-container">
        <?php 
        // Şıklar için döngü
        $options = ['A', 'B', 'C', 'D'];
        foreach ($options as $option) {
            $image = isset($results[$option]['image']) ? $results[$option]['image'] : '';
            $text = isset($results[$option]['text']) ? $results[$option]['text'] : '';
            ?>
            <div class="result">
                <h4>Option <?php echo $option; ?>:</h4>
                
                <label>Image for Option <?php echo $option; ?>:</label><br>
                <img id="sqp_image_<?php echo $option; ?>_preview" src="<?php echo esc_url($image); ?>" style="max-width:100%; height:auto; display:<?php echo $image ? 'block' : 'none'; ?>;"><br>
                <input type="hidden" name="sqp_test_results[<?php echo $option; ?>][image]" id="sqp_image_<?php echo $option; ?>" value="<?php echo esc_url($image); ?>">
                <button type="button" class="button sqp-upload-image-button" data-target="#sqp_image_<?php echo $option; ?>">Upload Image</button>
                <button type="button" class="button sqp-remove-image-button" data-target="#sqp_image_<?php echo $option; ?>" style="display:<?php echo $image ? 'inline-block' : 'none'; ?>;">Remove Image</button>
                <br><br>

                <label>Description for Option <?php echo $option; ?>:</label>
                <?php 
                // Zengin içerik editörünü oluştur
                wp_editor($text, 'sqp_test_results_' . $option . '_text', array(
                    'textarea_name' => 'sqp_test_results[' . $option . '][text]',
                    'media_buttons' => false,
                    'textarea_rows' => 5,
                    'teeny' => true
                ));
                ?>
            </div>
            <?php
        }
        ?>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Görsel yükleme butonuna tıklama işlevi
        $('.sqp-upload-image-button').click(function(e) {
            e.preventDefault();
            const targetInput = $(this).data('target');
            const targetPreview = $(targetInput + '_preview');

            const mediaUploader = wp.media({
                title: 'Choose Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false
            }).on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                $(targetInput).val(attachment.url);
                targetPreview.attr('src', attachment.url).show();
                $(targetInput).siblings('.sqp-remove-image-button').show();
            }).open();
        });

        // Görsel kaldırma butonuna tıklama işlevi
        $('.sqp-remove-image-button').click(function(e) {
            e.preventDefault();
            const targetInput = $(this).data('target');
            const targetPreview = $(targetInput + '_preview');
            $(targetInput).val('');
            targetPreview.hide();
            $(this).hide();
        });
    });
    </script>

    <?php
}


function sqp_render_test_questions_meta_box($post)
{
    wp_nonce_field('sqp_save_test_questions', 'sqp_test_questions_nonce');

    // Mevcut soruları al
    $questions = get_post_meta($post->ID, '_sqp_test_questions', true);
    ?>

    <div id="questions-container">
        <button type="button" onclick="addQuestion()">Add Question</button>
        <?php
        if ($questions && is_array($questions)) {
            foreach ($questions as $index => $question) {
                ?>
                <div class="question">
                    <h4>Questions <?php echo ($index + 1); ?>:</h4>
                    <label>Question: </label>
                    <input type="text" name="sqp_test_questions[<?php echo $index; ?>][text]"
                        value="<?php echo esc_attr($question['text']); ?>" required><br>
                    <label>Option A: </label>
                    <input type="text" name="sqp_test_questions[<?php echo $index; ?>][a]"
                        value="<?php echo esc_attr($question['a']); ?>" required><br>
                    <label>Option B: </label>
                    <input type="text" name="sqp_test_questions[<?php echo $index; ?>][b]"
                        value="<?php echo esc_attr($question['b']); ?>" required><br>
                    <label>Option C: </label>
                    <input type="text" name="sqp_test_questions[<?php echo $index; ?>][c]"
                        value="<?php echo esc_attr($question['c']); ?>" required><br>
                    <label>Option D: </label>
                    <input type="text" name="sqp_test_questions[<?php echo $index; ?>][d]"
                        value="<?php echo esc_attr($question['d']); ?>" required><br>
                </div>
                <?php
            }
        }
        ?>
        <div style="margin-top:20px;">
            <label>
                <input type="checkbox"> Should the <code>[ad_sense]</code> ad command be automatically added between
                questions?
            </label>
        </div>


    </div>

    <script>
        function addQuestion() {
            const container = document.getElementById('questions-container');
            const questionCount = container.querySelectorAll('.question').length;

            const questionDiv = document.createElement('div');
            questionDiv.className = 'question';
            questionDiv.innerHTML = `
                <h4>Questions ${questionCount + 1}:</h4>
                <label>Question: </label><input type="text" name="sqp_test_questions[${questionCount}][text]" required><br>
                <label>Option A: </label><input type="text" name="sqp_test_questions[${questionCount}][a]" required><br>
                <label>Option B: </label><input type="text" name="sqp_test_questions[${questionCount}][b]" required><br>
                <label>Option C: </label><input type="text" name="sqp_test_questions[${questionCount}][c]" required><br>
                <label>Option D: </label><input type="text" name="sqp_test_questions[${questionCount}][d]" required><br>
            `;
            container.appendChild(questionDiv);
        }
    </script>
    <?php
}

// Test sorularını veritabanına kaydediyoruz
function sqp_save_test_questions($post_id)
{
    if (!isset($_POST['sqp_test_questions_nonce']) || !wp_verify_nonce($_POST['sqp_test_questions_nonce'], 'sqp_save_test_questions')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['sqp_test_questions']) && is_array($_POST['sqp_test_questions'])) {
        update_post_meta($post_id, '_sqp_test_questions', $_POST['sqp_test_questions']);
    }
}
add_action('save_post', 'sqp_save_test_questions');