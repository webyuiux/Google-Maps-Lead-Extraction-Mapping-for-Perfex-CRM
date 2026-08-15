<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <h4 class="font-medium" style="margin: 0;"><?php echo _l('search_history', 'Search History'); ?></h4>
                            <div class="section-actions">
                                <button class="btn btn-danger btn-sm" id="clearAllSearches">
                                    <i class="fa fa-trash"></i> <span class="btn-text"><?php echo _l('clear_all'); ?></span>
                                </button>
                            </div>
                        </div>
                        
                        <?php if (!empty($recent_searches)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="recentSearchesTable">
                                <thead>
                                    <tr>
                                        <th class="th-checkbox" style="width:40px;">
                                            <input type="checkbox" id="selectAllSearches" class="select-all-checkbox">
                                        </th>
                                        <th><?php echo _l('business_type'); ?></th>
                                        <th><?php echo _l('location'); ?></th>
                                        <th class="hidden-xs"><?php echo _l('results'); ?></th>
                                        <th class="hidden-xs"><?php echo _l('extraction_date'); ?></th>
                                        <th><?php echo _l('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_searches as $search): ?>
                                    <tr class="recent-search-row" data-id="<?php echo $search['id']; ?>" data-keyword="<?php echo htmlspecialchars($search['keyword']); ?>" data-location="<?php echo htmlspecialchars($search['location']); ?>">
                                        <td class="td-checkbox">
                                            <input type="checkbox" class="search-checkbox" value="<?php echo $search['id']; ?>">
                                        </td>
                                        <td class="clickable-cell"><?php echo htmlspecialchars($search['keyword']); ?></td>
                                        <td class="clickable-cell"><?php echo htmlspecialchars($search['location']); ?></td>
                                        <td class="hidden-xs"><?php echo $search['results_count']; ?></td>
                                        <td class="hidden-xs"><?php echo _dt($search['created_at']); ?></td>
                                        <td class="action-buttons">
                                            <button class="btn btn-primary btn-xs use-search" data-keyword="<?php echo htmlspecialchars($search['keyword']); ?>" data-location="<?php echo htmlspecialchars($search['location']); ?>" title="<?php echo _l('use_search', 'Use Search'); ?>">
                                                <i class="fa fa-search"></i>
                                            </button>
                                            <button class="btn btn-danger btn-xs delete-search" data-id="<?php echo $search['id']; ?>" title="<?php echo _l('delete'); ?>">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="bulk-actions" id="searchBulkActions" style="display:none; margin-top: 15px; padding: 15px; background: #f8f8f8; border-radius: 4px;">
                            <span class="selected-count" style="margin-right: 15px;"><span id="selectedSearchCount">0</span> <?php echo _l('selected'); ?></span>
                            <button class="btn btn-danger btn-sm" id="deleteSelectedSearches">
                                <i class="fa fa-trash"></i> <?php echo _l('delete_selected'); ?>
                            </button>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info" style="margin-bottom: 0;">
                            <?php echo _l('no_search_history', 'No search history found.'); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="loadingOverlay" class="loading-overlay">
    <div class="loading-spinner"></div>
</div>

<input type="hidden" id="admin_url" value="<?php echo admin_url(); ?>">
<input type="hidden" id="csrf_token_name" value="<?php echo $this->security->get_csrf_token_name(); ?>">
<input type="hidden" id="csrf_token_value" value="<?php echo $this->security->get_csrf_hash(); ?>">

<?php init_tail(); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof $ !== 'undefined' && $.fn.DataTable) {
            $('#recentSearchesTable').DataTable({
                "order": [],
                "columnDefs": [
                    { "orderable": false, "targets": [0, 5] }
                ],
                "initComplete": function(settings, json) {
                    $('#recentSearchesTable').removeClass('table-loading');
                    $('#recentSearchesTable').parents('.table-loading').removeClass('table-loading');
                }
            });
            // Force remove table-loading just in case
            $('#recentSearchesTable').removeClass('table-loading');
            if($('#recentSearchesTable').parent().hasClass('table-loading')){
                $('#recentSearchesTable').parent().removeClass('table-loading');
            }
        }
    });

    // In case the use-search button is clicked, we need to redirect to the main page with params
    document.querySelectorAll('.use-search').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            var keyword = encodeURIComponent(this.dataset.keyword);
            var location = encodeURIComponent(this.dataset.location);
            window.location.href = adminUrl + 'google_maps_extractor?keyword=' + keyword + '&location=' + location;
        });
    });
</script>
<style>
/* Override Perfex CRM's default skeleton loader for offline datatables */
#recentSearchesTable tbody td,
#recentSearchesTable.dataTable tbody td {
    color: inherit !important;
    background-color: inherit !important;
}
#recentSearchesTable_wrapper .table-loading {
    background: none !important;
    animation: none !important;
}
</style>
</body>
</html>
