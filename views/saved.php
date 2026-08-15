<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <h4 class="font-medium" style="margin: 0;"><?php echo _l('saved_businesses', 'Saved Businesses'); ?></h4>
                            <div class="section-actions">
                                <button class="btn btn-info btn-sm" id="exportSavedExcelBtn">
                                    <i class="fa fa-file-excel-o"></i> <span class="btn-text"><?php echo _l('export_saved_excel'); ?></span>
                                </button>
                                <?php if (has_permission('google_maps_extractor', '', 'delete')): ?>
                                <button class="btn btn-danger btn-sm" id="clearAllBusinesses">
                                    <i class="fa fa-trash"></i> <span class="btn-text"><?php echo _l('clear_all'); ?></span>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($saved_businesses)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="savedBusinessesTable">
                                <thead>
                                    <tr>
                                        <th class="th-checkbox" style="width:40px;">
                                            <input type="checkbox" id="selectAllBusinesses" class="select-all-checkbox">
                                        </th>
                                        <th><?php echo _l('name'); ?></th>
                                        <th class="hidden-xs"><?php echo _l('address'); ?></th>
                                        <th><?php echo _l('phone'); ?></th>
                                        <th class="hidden-xs hidden-sm"><?php echo _l('website'); ?></th>
                                        <th class="hidden-xs"><?php echo _l('rating'); ?></th>
                                        <th><?php echo _l('status'); ?></th>
                                        <th><?php echo _l('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($saved_businesses as $business): ?>
                                    <tr data-id="<?php echo $business['id']; ?>">
                                        <td class="td-checkbox">
                                            <input type="checkbox" class="business-checkbox" value="<?php echo $business['id']; ?>">
                                        </td>
                                        <td><?php echo htmlspecialchars($business['name']); ?></td>
                                        <td class="hidden-xs"><?php echo htmlspecialchars($business['address']); ?></td>
                                        <td>
                                            <?php if (!empty($business['phone'])): ?>
                                            <a href="tel:<?php echo $business['phone']; ?>" class="phone-link"><?php echo htmlspecialchars($business['phone']); ?></a>
                                            <?php endif; ?>
                                        </td>
                                        <td class="hidden-xs hidden-sm">
                                            <?php if (!empty($business['website'])): ?>
                                            <a href="<?php echo $business['website']; ?>" target="_blank" class="website-link"><i class="fa fa-external-link"></i></a>
                                            <?php endif; ?>
                                        </td>
                                        <td class="hidden-xs"><?php echo $business['rating']; ?></td>
                                        <td>
                                            <?php if ($business['is_converted_to_lead']): ?>
                                                <?php if (!empty($business['lead_id'])): ?>
                                                    <a href="<?php echo admin_url('leads/index/' . $business['lead_id']); ?>" class="label label-success" title="<?php echo _l('view_lead'); ?>"><?php echo _l('converted'); ?></a>
                                                <?php else: ?>
                                                    <span class="label label-success"><?php echo _l('converted'); ?></span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <a href="#" class="label label-default convert-to-lead" data-id="<?php echo $business['id']; ?>" title="<?php echo _l('convert_to_lead'); ?>"><?php echo _l('not_converted'); ?></a>
                                            <?php endif; ?>
                                        </td>
                                        <td class="action-buttons">
                                            <?php if ($business['is_converted_to_lead'] && $business['lead_id']): ?>
                                                <a href="<?php echo admin_url('leads/index/' . $business['lead_id']); ?>" class="btn btn-info btn-xs" title="<?php echo _l('view_lead'); ?>">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            <?php elseif (true): ?>
                                                <button class="btn btn-success btn-xs convert-to-lead" data-id="<?php echo $business['id']; ?>" title="<?php echo _l('convert_to_lead'); ?>">
                                                    <i class="fa fa-exchange"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (has_permission('google_maps_extractor', '', 'delete')): ?>
                                                <button class="btn btn-danger btn-xs delete-business" data-id="<?php echo $business['id']; ?>" title="<?php echo _l('delete'); ?>">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="bulk-actions" id="businessBulkActions" style="display:none; margin-top: 15px; padding: 15px; background: #f8f8f8; border-radius: 4px;">
                            <span class="selected-count" style="margin-right: 15px;"><span id="selectedBusinessCount">0</span> <?php echo _l('selected'); ?></span>
                            <button class="btn btn-success btn-sm" id="convertSelectedToLeads">
                                <i class="fa fa-exchange"></i> <?php echo _l('convert_selected'); ?>
                            </button>
                            <?php if (has_permission('google_maps_extractor', '', 'delete')): ?>
                            <button class="btn btn-danger btn-sm" id="deleteSelectedBusinesses">
                                <i class="fa fa-trash"></i> <?php echo _l('delete_selected'); ?>
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info" style="margin-bottom: 0;">
                            <?php echo _l('no_saved_businesses', 'No saved businesses found.'); ?>
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
            var table = $('#savedBusinessesTable').DataTable({
                "order": [],
                "columnDefs": [
                    { "orderable": false, "targets": [0, 7] }
                ],
                "initComplete": function(settings, json) {
                    $('#savedBusinessesTable').removeClass('table-loading');
                    $('#savedBusinessesTable').parents('.table-loading').removeClass('table-loading');
                }
            });
            // Force remove table-loading just in case
            $('#savedBusinessesTable').removeClass('table-loading');
            if($('#savedBusinessesTable').parent().hasClass('table-loading')){
                $('#savedBusinessesTable').parent().removeClass('table-loading');
            }
        }
    });
</script>
<style>
/* Override Perfex CRM's default skeleton loader for offline datatables */
#savedBusinessesTable tbody td,
#savedBusinessesTable.dataTable tbody td {
    color: inherit !important;
    background-color: inherit !important;
}
#savedBusinessesTable_wrapper .table-loading {
    background: none !important;
    animation: none !important;
}
</style>
</body>
</html>
