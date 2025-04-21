<?php
// Prevent direct file access for security
if (!defined('ABSPATH')) {
    exit;
}

// Get the current CPT slug from the URL (e.g., page=edit_cpt_course-name)
$cpt_slug = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';

// Remove the "edit_cpt_" prefix to get the actual post type slug
$cpt_name = str_replace('edit_cpt_', '', $cpt_slug);

// Retrieve stored custom post types from WordPress options
$custom_post_types = get_option('academy_custom_post_types', []);
$current_cpt = null;

// Find the corresponding post type by matching slugified name
foreach ($custom_post_types as $cpt) {
    if (sanitize_title($cpt['name']) == $cpt_name) {
        $current_cpt = $cpt;
        break;
    }
}

// If no match is found, return an error message and stop execution
if (!$current_cpt) {
    wp_die('Fout. Cursus niet gevonden.');
}
?>

<div class="wrap">
    <h2>Cursus bewerken</h2>

    <!-- Edit Course Form -->
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit_cpt">
        <input type="hidden" name="old_cpt_name" value="<?php echo esc_attr($current_cpt['name']); ?>">
        <?php wp_nonce_field('academy_edit_cpt', 'academy_nonce'); // Nonce for security ?>

        <table class="form-table">
            <!-- Course Name Field -->
            <tr valign="top">
                <th scope="row"><label for="new_cpt_name">Cursus naam</label></th>
                <td>
                    <input type="text" id="new_cpt_name" name="new_cpt_name"
                           value="<?php echo esc_attr($current_cpt['name']); ?>" required />
                </td>
            </tr>

            <!-- Image Upload/Preview Field -->
            <tr valign="top">
                <th scope="row"><label for="cpt_image">Afbeelding</label></th>
                <td>
                    <?php if (!empty($current_cpt['image'])) : ?>
                        <img id="image-preview" src="<?php echo wp_get_attachment_url($current_cpt['image']); ?>"
                             style="max-width: 100px; display: block;">
                    <?php else : ?>
                        <img id="image-preview" src="" style="max-width: 100px; display: block;">
                    <?php endif; ?>

                    <input type="hidden" id="cpt_image_id" name="cpt_image_id"
                           value="<?php echo esc_attr($current_cpt['image']); ?>">

                    <input type="button" id="upload_image_button" class="button" value="Selecteer afbeelding">
                    <input type="button" id="remove_image_button" class="button-secondary" value="Verwijder afbeelding">
                </td>
            </tr>
        </table>

        <?php submit_button('Wijzigingen opslaan'); ?>
    </form>

    <!-- Course Deletion Form -->
    <h2>Cursus verwijderen</h2>
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <input type="hidden" name="action" value="delete_cpt">
        <input type="hidden" name="cpt_name" value="<?php echo esc_attr($current_cpt['name']); ?>">
        <?php wp_nonce_field('academy_delete_cpt', 'academy_delete_nonce'); ?>

        <p>Typ de naam van de cursus over ter bevestiging:</p>
        <input style="min-width:250px;" type="text" name="confirm_name"
               placeholder="Typ cursus naam ter bevestiging.." required>

        <?php
        submit_button('Verwijderen', 'delete', '', false, [
            'onclick' => "return confirm('Weet je zeker dat je deze cursus wilt verwijderen? Je verwijderd dan ook permanent alle content van deze cursus.');"
        ]);
        ?>
    </form>
</div>
