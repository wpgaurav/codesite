/**
 * CodeSite Admin JavaScript
 */

(function($) {
    'use strict';

    // Initialize when DOM is ready
    $(document).ready(function() {
        initDeleteHandlers();
        initDuplicateHandlers();
        initSortable();
        initBlockAdder();
    });

    /**
     * Initialize delete handlers
     */
    function initDeleteHandlers() {
        $(document).on('click', '.codesite-delete', function(e) {
            e.preventDefault();

            if (!confirm(codesiteAdmin.strings.confirmDelete)) {
                return;
            }

            var $link = $(this);
            var id = $link.data('id');
            var type = $link.data('type');
            var endpoint = codesiteAdmin.apiUrl + '/' + type + 's/' + id;

            $.ajax({
                url: endpoint,
                method: 'DELETE',
                headers: {
                    'X-WP-Nonce': codesiteAdmin.nonce
                },
                success: function() {
                    $link.closest('tr').fadeOut(function() {
                        $(this).remove();
                    });
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message || 'Unknown error'));
                }
            });
        });
    }

    /**
     * Initialize duplicate handlers
     */
    function initDuplicateHandlers() {
        $(document).on('click', '.codesite-duplicate', function(e) {
            e.preventDefault();

            var $link = $(this);
            var id = $link.data('id');
            var type = $link.data('type');
            var endpoint = codesiteAdmin.apiUrl + '/' + type + 's/' + id + '/duplicate';

            $.ajax({
                url: endpoint,
                method: 'POST',
                headers: {
                    'X-WP-Nonce': codesiteAdmin.nonce
                },
                success: function(response) {
                    // Redirect to edit the new item
                    window.location.href = codesiteAdmin.adminUrl + 'admin.php?page=codesite-' + type + '-editor&id=' + response.id;
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message || 'Unknown error'));
                }
            });
        });
    }

    /**
     * Initialize sortable lists
     */
    function initSortable() {
        if (typeof Sortable === 'undefined') {
            // Basic jQuery UI sortable fallback
            $('.codesite-sortable-list').sortable({
                handle: '.dashicons-menu',
                placeholder: 'sortable-placeholder',
                update: function() {
                    updateBlockOrder();
                }
            });
        }
    }

    /**
     * Initialize block adder
     */
    function initBlockAdder() {
        $('#codesite-add-block').on('click', function() {
            var $select = $('#codesite-available-blocks');
            var blockId = $select.val();
            var blockName = $select.find('option:selected').text();

            if (!blockId) {
                return;
            }

            var $list = $('#codesite-layout-blocks, #codesite-template-blocks');

            // Check if block already exists
            if ($list.find('[data-id="' + blockId + '"]').length) {
                alert('This block is already in the list.');
                return;
            }

            var $item = $('<li data-id="' + blockId + '">' +
                '<span class="dashicons dashicons-menu"></span>' +
                blockName +
                '<button type="button" class="codesite-remove-block">&times;</button>' +
                '</li>');

            $list.append($item);
            $select.val('');
        });

        // Remove block from list
        $(document).on('click', '.codesite-remove-block', function() {
            $(this).closest('li').remove();
        });
    }

    /**
     * Get current block order
     */
    window.getBlockOrder = function() {
        var order = [];
        $('#codesite-layout-blocks li, #codesite-template-blocks li').each(function() {
            order.push(parseInt($(this).data('id'), 10));
        });
        return order;
    };

})(jQuery);
