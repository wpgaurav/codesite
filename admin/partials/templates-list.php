<?php
/**
 * Templates list page.
 */

// Security check.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$templates      = CodeSite_Templates::get_all( array( 'status' => null ) );
$template_types = CodeSite_Templates::get_template_types();
?>

<div class="wrap codesite-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Templates', 'codesite' ); ?></h1>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=codesite-template-editor' ) ); ?>" class="page-title-action">
        <?php esc_html_e( 'Add New', 'codesite' ); ?>
    </a>
    <hr class="wp-header-end">

    <?php if ( empty( $templates ) ) : ?>
        <div class="codesite-empty-state">
            <h2><?php esc_html_e( 'No templates yet', 'codesite' ); ?></h2>
            <p><?php esc_html_e( 'Create templates to control how different page types are rendered.', 'codesite' ); ?></p>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=codesite-template-editor' ) ); ?>" class="button button-primary button-hero">
                <?php esc_html_e( 'Create Your First Template', 'codesite' ); ?>
            </a>
        </div>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped codesite-table">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-title column-primary"><?php esc_html_e( 'Name', 'codesite' ); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e( 'Type', 'codesite' ); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e( 'Priority', 'codesite' ); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e( 'Header', 'codesite' ); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e( 'Footer', 'codesite' ); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e( 'Status', 'codesite' ); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e( 'Updated', 'codesite' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $templates as $template ) : ?>
                    <?php
                    $header = $template->header_layout_id ? CodeSite_Layouts::get( $template->header_layout_id ) : null;
                    $footer = $template->footer_layout_id ? CodeSite_Layouts::get( $template->footer_layout_id ) : null;
                    $type_label = isset( $template_types[ $template->template_type ] ) ? $template_types[ $template->template_type ] : $template->template_type;
                    ?>
                    <tr data-id="<?php echo esc_attr( $template->id ); ?>">
                        <td class="title column-title column-primary">
                            <strong>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=codesite-template-editor&id=' . $template->id ) ); ?>" class="row-title">
                                    <?php echo esc_html( $template->name ); ?>
                                </a>
                            </strong>
                            <div class="row-actions">
                                <span class="edit">
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=codesite-template-editor&id=' . $template->id ) ); ?>">
                                        <?php esc_html_e( 'Edit', 'codesite' ); ?>
                                    </a> |
                                </span>
                                <span class="trash">
                                    <a href="#" class="codesite-delete" data-id="<?php echo esc_attr( $template->id ); ?>" data-type="template">
                                        <?php esc_html_e( 'Delete', 'codesite' ); ?>
                                    </a>
                                </span>
                            </div>
                        </td>
                        <td><?php echo esc_html( $type_label ); ?></td>
                        <td><?php echo esc_html( $template->priority ); ?></td>
                        <td><?php echo $header ? esc_html( $header->name ) : '—'; ?></td>
                        <td><?php echo $footer ? esc_html( $footer->name ) : '—'; ?></td>
                        <td>
                            <span class="codesite-status codesite-status-<?php echo esc_attr( $template->status ); ?>">
                                <?php echo esc_html( ucfirst( $template->status ) ); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html( human_time_diff( strtotime( $template->updated_at ), current_time( 'timestamp' ) ) ) . ' ago'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
