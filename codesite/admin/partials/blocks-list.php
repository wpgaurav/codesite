<?php
/**
 * Blocks list page.
 */

// Security check.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$blocks     = CodeSite_Blocks::get_all( array( 'status' => null ) );
$categories = CodeSite_Blocks::get_categories();
?>

<div class="wrap codesite-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Blocks', 'codesite' ); ?></h1>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=codesite-block-editor' ) ); ?>" class="page-title-action">
        <?php esc_html_e( 'Add New', 'codesite' ); ?>
    </a>
    <hr class="wp-header-end">

    <?php if ( empty( $blocks ) ) : ?>
        <div class="codesite-empty-state">
            <h2><?php esc_html_e( 'No blocks yet', 'codesite' ); ?></h2>
            <p><?php esc_html_e( 'Create your first block to get started.', 'codesite' ); ?></p>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=codesite-block-editor' ) ); ?>" class="button button-primary button-hero">
                <?php esc_html_e( 'Create Your First Block', 'codesite' ); ?>
            </a>
        </div>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped codesite-table">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-title column-primary"><?php esc_html_e( 'Name', 'codesite' ); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e( 'Slug', 'codesite' ); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e( 'Category', 'codesite' ); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e( 'CSS Scope', 'codesite' ); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e( 'Status', 'codesite' ); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e( 'Updated', 'codesite' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $blocks as $block ) : ?>
                    <tr data-id="<?php echo esc_attr( $block->id ); ?>">
                        <td class="title column-title column-primary">
                            <strong>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=codesite-block-editor&id=' . $block->id ) ); ?>" class="row-title">
                                    <?php echo esc_html( $block->name ); ?>
                                </a>
                            </strong>
                            <div class="row-actions">
                                <span class="edit">
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=codesite-block-editor&id=' . $block->id ) ); ?>">
                                        <?php esc_html_e( 'Edit', 'codesite' ); ?>
                                    </a> |
                                </span>
                                <span class="duplicate">
                                    <a href="#" class="codesite-duplicate" data-id="<?php echo esc_attr( $block->id ); ?>" data-type="block">
                                        <?php esc_html_e( 'Duplicate', 'codesite' ); ?>
                                    </a> |
                                </span>
                                <span class="trash">
                                    <a href="#" class="codesite-delete" data-id="<?php echo esc_attr( $block->id ); ?>" data-type="block">
                                        <?php esc_html_e( 'Delete', 'codesite' ); ?>
                                    </a>
                                </span>
                            </div>
                        </td>
                        <td><code><?php echo esc_html( $block->slug ); ?></code></td>
                        <td><?php echo esc_html( $block->category ); ?></td>
                        <td><?php echo esc_html( ucfirst( $block->css_scope ) ); ?></td>
                        <td>
                            <span class="codesite-status codesite-status-<?php echo esc_attr( $block->status ); ?>">
                                <?php echo esc_html( ucfirst( $block->status ) ); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html( human_time_diff( strtotime( $block->updated_at ), current_time( 'timestamp' ) ) ) . ' ago'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
