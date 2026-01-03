<?php
/**
 * Settings page.
 */

// Security check.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$settings = CodeSite_Database::get_all_settings();

$enabled            = isset( $settings['enabled'] ) ? $settings['enabled'] : true;
$theme_override     = isset( $settings['theme_override'] ) ? $settings['theme_override'] : false;
$keep_admin_bar     = isset( $settings['keep_admin_bar'] ) ? $settings['keep_admin_bar'] : true;
$output_mode        = isset( $settings['output_mode'] ) ? $settings['output_mode'] : 'inline';
$minify_output      = isset( $settings['minify_output'] ) ? $settings['minify_output'] : false;
$default_header     = isset( $settings['default_header'] ) ? $settings['default_header'] : null;
$default_footer     = isset( $settings['default_footer'] ) ? $settings['default_footer'] : null;
$tangible_support   = isset( $settings['tangible_support'] ) ? $settings['tangible_support'] : true;
$acf_support        = isset( $settings['acf_support'] ) ? $settings['acf_support'] : true;

$all_layouts = CodeSite_Layouts::get_all();
$headers     = array_filter( $all_layouts, function( $l ) { return $l->type === 'header'; } );
$footers     = array_filter( $all_layouts, function( $l ) { return $l->type === 'footer'; } );
?>

<div class="wrap codesite-wrap">
    <h1><?php esc_html_e( 'CodeSite Settings', 'codesite' ); ?></h1>

    <form id="codesite-settings-form" class="codesite-settings-form">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e( 'General', 'codesite' ); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="enabled" value="1" <?php checked( $enabled ); ?>>
                                <?php esc_html_e( 'Enable CodeSite frontend rendering', 'codesite' ); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e( 'When enabled, CodeSite will render pages using your templates.', 'codesite' ); ?>
                            </p>
                        </fieldset>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'Theme Override', 'codesite' ); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="theme_override" value="1" <?php checked( $theme_override ); ?>>
                                <?php esc_html_e( 'Disable active theme completely', 'codesite' ); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e( 'Removes all theme CSS, JS, and templates. Use a blank canvas.', 'codesite' ); ?>
                            </p>

                            <br>

                            <label>
                                <input type="checkbox" name="keep_admin_bar" value="1" <?php checked( $keep_admin_bar ); ?>>
                                <?php esc_html_e( 'Keep WordPress admin bar on frontend', 'codesite' ); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'Performance', 'codesite' ); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="radio" name="output_mode" value="inline" <?php checked( $output_mode, 'inline' ); ?>>
                                <?php esc_html_e( 'Inline CSS/JS (simpler, no extra requests)', 'codesite' ); ?>
                            </label>
                            <br>
                            <label>
                                <input type="radio" name="output_mode" value="external" <?php checked( $output_mode, 'external' ); ?>>
                                <?php esc_html_e( 'External CSS/JS files (better caching)', 'codesite' ); ?>
                            </label>

                            <br><br>

                            <label>
                                <input type="checkbox" name="minify_output" value="1" <?php checked( $minify_output ); ?>>
                                <?php esc_html_e( 'Minify CSS/JS output', 'codesite' ); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'Default Layouts', 'codesite' ); ?></th>
                    <td>
                        <p>
                            <label for="default_header"><?php esc_html_e( 'Default Header Layout', 'codesite' ); ?></label>
                            <select name="default_header" id="default_header">
                                <option value=""><?php esc_html_e( 'None', 'codesite' ); ?></option>
                                <?php foreach ( $headers as $header ) : ?>
                                    <option value="<?php echo esc_attr( $header->id ); ?>" <?php selected( $default_header, $header->id ); ?>>
                                        <?php echo esc_html( $header->name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                        <p>
                            <label for="default_footer"><?php esc_html_e( 'Default Footer Layout', 'codesite' ); ?></label>
                            <select name="default_footer" id="default_footer">
                                <option value=""><?php esc_html_e( 'None', 'codesite' ); ?></option>
                                <?php foreach ( $footers as $footer ) : ?>
                                    <option value="<?php echo esc_attr( $footer->id ); ?>" <?php selected( $default_footer, $footer->id ); ?>>
                                        <?php echo esc_html( $footer->name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'Integrations', 'codesite' ); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="tangible_support" value="1" <?php checked( $tangible_support ); ?>>
                                <?php esc_html_e( 'Enable Tangible Loops & Logic support', 'codesite' ); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="acf_support" value="1" <?php checked( $acf_support ); ?>>
                                <?php esc_html_e( 'Enable ACF dynamic field support', 'codesite' ); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary" id="codesite-save-settings">
                <?php esc_html_e( 'Save Settings', 'codesite' ); ?>
            </button>
            <span id="codesite-settings-status"></span>
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    $('#codesite-settings-form').on('submit', function(e) {
        e.preventDefault();

        var $btn = $('#codesite-save-settings');
        var $status = $('#codesite-settings-status');

        $btn.prop('disabled', true);
        $status.text(codesiteAdmin.strings.saving);

        var formData = {};
        $(this).serializeArray().forEach(function(item) {
            formData[item.name] = item.value;
        });

        // Handle checkboxes (unchecked ones don't appear in serializeArray)
        ['enabled', 'theme_override', 'keep_admin_bar', 'minify_output', 'tangible_support', 'acf_support'].forEach(function(name) {
            if (!formData[name]) {
                formData[name] = false;
            } else {
                formData[name] = true;
            }
        });

        // Handle select values
        if (formData.default_header === '') formData.default_header = null;
        if (formData.default_footer === '') formData.default_footer = null;

        $.ajax({
            url: codesiteAdmin.apiUrl + '/settings',
            method: 'POST',
            headers: {
                'X-WP-Nonce': codesiteAdmin.nonce
            },
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function() {
                $btn.prop('disabled', false);
                $status.text(codesiteAdmin.strings.saved).css('color', 'green');
                setTimeout(function() {
                    $status.text('');
                }, 2000);
            },
            error: function() {
                $btn.prop('disabled', false);
                $status.text(codesiteAdmin.strings.error).css('color', 'red');
            }
        });
    });
});
</script>
