<?php
/**
 * Layouts list page.
 */

// Security check.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$layouts = CodeSite_Layouts::get_all( array( 'status' => null ) );
?>

<div class="wrap codesite-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Layouts', 'codesite' ); ?></h1>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=codesite-layout-editor' ) ); ?>" class="page-title-action">
        <?php esc_html_e( 'Add New', 'codesite' ); ?>
    </a>
    <hr class="wp-header-end">

    <?php if ( empty( $layouts ) ) : ?>
        <div class="codesite-empty-state">
            <h2><?php esc_html_e( 'No layouts yet', 'codesite' ); ?></h2>
            <p><?php esc_html_e( 'Create your first layout for headers, footers, or reusable sections.', 'codesite' ); ?></p>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=codesite-layout-editor' ) ); ?>" class="button button-primary button-hero">
                <?php esc_html_e( 'Create Your First Layout', 'codesite' ); ?>
            </a>
        </div>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped codesite-table">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-title column-primary"><?php esc_html_e( 'Name', 'codesite' ); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e( 'Slug', 'codesite' ); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e( 'Type', 'codesite' ); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e( 'Mode', 'codesite' ); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e( 'Status', 'codesite' ); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e( 'Updated', 'codesite' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $layouts as $layout ) : ?>
                    <tr data-id="<?php echo esc_attr( $layout->id ); ?>">
                        <td class="title column-title column-primary">
                            <strong>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=codesite-layout-editor&id=' . $layout->id ) ); ?>" class="row-title">
                                    <?php echo esc_html( $layout->name ); ?>
                                </a>
                            </strong>
                            <div class="row-actions">
                                <span class="edit">
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=codesite-layout-editor&id=' . $layout->id ) ); ?>">
                                        <?php esc_html_e( 'Edit', 'codesite' ); ?>
                                    </a> |
                                </span>
                                <span class="trash">
                                    <a href="#" class="codesite-delete" data-id="<?php echo esc_attr( $layout->id ); ?>" data-type="layout">
                                        <?php esc_html_e( 'Delete', 'codesite' ); ?>
                                    </a>
                                </span>
                            </div>
                        </td>
                        <td><code><?php echo esc_html( $layout->slug ); ?></code></td>
                        <td><?php echo esc_html( ucfirst( $layout->type ) ); ?></td>
                        <td><?php echo $layout->use_blocks ? esc_html__( 'Blocks', 'codesite' ) : esc_html__( 'Custom HTML', 'codesite' ); ?></td>
                        <td>
                            <span class="codesite-status codesite-status-<?php echo esc_attr( $layout->status ); ?>">
                                <?php echo esc_html( ucfirst( $layout->status ) ); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html( human_time_diff( strtotime( $layout->updated_at ), current_time( 'timestamp' ) ) ) . ' ago'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
