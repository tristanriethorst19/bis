<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// Load existing custom post types created via the plugin
$custom_post_types = get_option('academy_custom_post_types', []);
?>

<!-- Course Creation Form -->
<div class="wrap">
    <h2>Cursus aanmaken</h2>

    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
        <!-- Define action for admin_post hook -->
        <input type="hidden" name="action" value="create_cpt">

        <!-- Security nonce to verify form submission -->
        <?php wp_nonce_field('academy_create_cpt', 'academy_nonce'); ?>

        <table class="form-table">
            <!-- Course Name Input -->
            <tr valign="top">
                <th scope="row"><label for="cpt_name">Cursus naam</label></th>
                <td>
                    <input type="text" id="cpt_name" name="cpt_name" required />
                </td>
            </tr>

            <!-- Course Image Selector -->
            <tr valign="top">
                <th scope="row"><label for="cpt_image">Afbeelding</label></th>
                <td>
                    <img id="image-preview" src="" style="max-width: 100px; display: block;">
                    <input type="hidden" id="cpt_image_id" name="cpt_image_id" value="">
                    <input type="button" id="upload_image_button" class="button" value="Afbeelding uploaden">
                    <input type="button" id="remove_image_button" class="button" value="Afbeelding verwijderen">
                </td>
            </tr>
        </table>

        <!-- Submit Button -->
        <?php submit_button('Cursus aanmaken'); ?>
    </form>
</div>

<!-- Course Management Table -->
<div class="wrap">
    <h2>Beheer cursussen</h2>

    <?php if (!empty($custom_post_types)) : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Cursus ID</th>
                    <th>Cursus naam</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($custom_post_types as $cpt) : ?>
                    <tr>
                        <td><?php echo esc_html(isset($cpt['id']) ? $cpt['id'] : 'N/A'); ?></td>
                        <td><?php echo esc_html($cpt['name']); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=edit_cpt_' . strtolower(sanitize_title($cpt['name']))); ?>">
                                Bewerk
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p>Geen cursussen gevonden</p>
    <?php endif; ?>
</div>
