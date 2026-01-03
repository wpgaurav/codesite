<?php
/**
 * Dashboard page.
 */

// Security check.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$block_count    = CodeSite_Blocks::count( 'active' );
$layout_count   = CodeSite_Layouts::count( 'active' );
$template_count = CodeSite_Templates::count( 'active' );

$settings       = CodeSite_Database::get_all_settings();
$tangible       = new CodeSite_Tangible_Integration();
$tangible_active = $tangible->is_active();
?>

<div class="wrap codesite-wrap">
    <h1><?php esc_html_e( 'CodeSite Dashboard', 'codesite' ); ?></h1>

    <div class="codesite-dashboard">
        <!-- Stats -->
        <div class="codesite-stats">
            <div class="codesite-stat-card">
                <div class="stat-number"><?php echo esc_html( $block_count ); ?></div>
                <div class="stat-label"><?php esc_html_e( 'Blocks', 'codesite' ); ?></div>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=codesite-blocks' ) ); ?>" class="stat-link">
                    <?php esc_html_e( 'View All', 'codesite' ); ?>
                </a>
            </div>

            <div class="codesite-stat-card">
                <div class="stat-number"><?php echo esc_html( $layout_count ); ?></div>
                <div class="stat-label"><?php esc_html_e( 'Layouts', 'codesite' ); ?></div>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=codesite-layouts' ) ); ?>" class="stat-link">
                    <?php esc_html_e( 'View All', 'codesite' ); ?>
                </a>
            </div>

            <div class="codesite-stat-card">
                <div class="stat-number"><?php echo esc_html( $template_count ); ?></div>
                <div class="stat-label"><?php esc_html_e( 'Templates', 'codesite' ); ?></div>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=codesite-templates' ) ); ?>" class="stat-link">
                    <?php esc_html_e( 'View All', 'codesite' ); ?>
                </a>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="codesite-section">
            <h2><?php esc_html_e( 'Quick Actions', 'codesite' ); ?></h2>
            <div class="codesite-actions">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=codesite-block-editor' ) ); ?>" class="button button-primary">
                    <?php esc_html_e( '+ New Block', 'codesite' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=codesite-layout-editor' ) ); ?>" class="button button-primary">
                    <?php esc_html_e( '+ New Layout', 'codesite' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=codesite-template-editor' ) ); ?>" class="button button-primary">
                    <?php esc_html_e( '+ New Template', 'codesite' ); ?>
                </a>
            </div>
        </div>

        <!-- System Status -->
        <div class="codesite-section">
            <h2><?php esc_html_e( 'System Status', 'codesite' ); ?></h2>
            <ul class="codesite-status-list">
                <li>
                    <?php if ( ! empty( $settings['enabled'] ) ) : ?>
                        <span class="status-ok">&#10003;</span>
                    <?php else : ?>
                        <span class="status-warning">&#9888;</span>
                    <?php endif; ?>
                    <?php esc_html_e( 'CodeSite Frontend Rendering:', 'codesite' ); ?>
                    <strong><?php echo ! empty( $settings['enabled'] ) ? esc_html__( 'Active', 'codesite' ) : esc_html__( 'Disabled', 'codesite' ); ?></strong>
                </li>
                <li>
                    <?php if ( ! empty( $settings['theme_override'] ) ) : ?>
                        <span class="status-ok">&#10003;</span>
                    <?php else : ?>
                        <span class="status-info">&#8226;</span>
                    <?php endif; ?>
                    <?php esc_html_e( 'Theme Override:', 'codesite' ); ?>
                    <strong><?php echo ! empty( $settings['theme_override'] ) ? esc_html__( 'Active', 'codesite' ) : esc_html__( 'Disabled', 'codesite' ); ?></strong>
                </li>
                <li>
                    <?php if ( $tangible_active ) : ?>
                        <span class="status-ok">&#10003;</span>
                    <?php else : ?>
                        <span class="status-info">&#8226;</span>
                    <?php endif; ?>
                    <?php esc_html_e( 'Tangible Loops & Logic:', 'codesite' ); ?>
                    <strong><?php echo $tangible_active ? esc_html__( 'Installed', 'codesite' ) : esc_html__( 'Not Installed', 'codesite' ); ?></strong>
                    <?php if ( ! $tangible_active ) : ?>
                        <a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=tangible+loops&tab=search' ) ); ?>" class="button button-small">
                            <?php esc_html_e( 'Install', 'codesite' ); ?>
                        </a>
                    <?php endif; ?>
                </li>
                <li>
                    <?php if ( $template_count > 0 ) : ?>
                        <span class="status-ok">&#10003;</span>
                    <?php else : ?>
                        <span class="status-warning">&#9888;</span>
                    <?php endif; ?>
                    <?php esc_html_e( 'Templates:', 'codesite' ); ?>
                    <strong>
                        <?php
                        if ( $template_count > 0 ) {
                            printf(
                                /* translators: %d: number of templates */
                                esc_html__( '%d defined', 'codesite' ),
                                $template_count
                            );
                        } else {
                            esc_html_e( 'No templates defined', 'codesite' );
                        }
                        ?>
                    </strong>
                </li>
            </ul>
        </div>

        <!-- Getting Started -->
        <div class="codesite-section">
            <h2><?php esc_html_e( 'Getting Started', 'codesite' ); ?></h2>
            <ol class="codesite-steps">
                <li>
                    <strong><?php esc_html_e( 'Create Blocks', 'codesite' ); ?></strong>
                    <p><?php esc_html_e( 'Blocks are reusable HTML/CSS/JS components. Start by creating your first block.', 'codesite' ); ?></p>
                </li>
                <li>
                    <strong><?php esc_html_e( 'Build Layouts', 'codesite' ); ?></strong>
                    <p><?php esc_html_e( 'Combine blocks into layouts for headers, footers, and reusable sections.', 'codesite' ); ?></p>
                </li>
                <li>
                    <strong><?php esc_html_e( 'Define Templates', 'codesite' ); ?></strong>
                    <p><?php esc_html_e( 'Create templates for different page types (front page, single post, archives, etc.).', 'codesite' ); ?></p>
                </li>
                <li>
                    <strong><?php esc_html_e( 'Enable Theme Override', 'codesite' ); ?></strong>
                    <p><?php esc_html_e( 'Go to Settings to enable theme override for a clean canvas.', 'codesite' ); ?></p>
                </li>
            </ol>
        </div>
    </div>
</div>
