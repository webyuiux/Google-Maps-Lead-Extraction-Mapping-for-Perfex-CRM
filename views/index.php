<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <?php if (empty($api_key)): ?>
            <div class="alert alert-warning">
                <?php echo _l('no_api_key_configured'); ?>
                <?php if (is_admin()): ?>
                    <a href="<?php echo admin_url('google_maps_extractor/settings'); ?>" class="alert-link">
                        <?php echo _l('google_maps_extractor_settings'); ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="extractor-app">
            <div class="extractor-left">
                <div class="search-form panel-body">
                    <div class="form-group">
                        <label for="keyword"><?php echo _l('business_type'); ?></label>
                        <div class="input-group">
                            <input type="text" id="keyword" class="form-control" placeholder="<?php echo _l('business_type_placeholder'); ?>" value="<?php echo htmlspecialchars($this->input->get('keyword') ?? ''); ?>" />
                            <span class="input-group-addon"><i class="fa fa-search"></i></span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="locationInput"><?php echo _l('location'); ?></label>
                        <div class="input-group">
                            <input type="text" id="locationInput" class="form-control" placeholder="<?php echo _l('location_placeholder'); ?>" value="<?php echo htmlspecialchars($this->input->get('location') ?? $default_location); ?>" />
                            <span class="input-group-btn">
                                <button class="btn btn-default" type="button" id="locateBtn" title="<?php echo _l('use_current_location'); ?>">
                                    <i class="fa fa-crosshairs"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="searchRadius"><?php echo _l('search_radius'); ?></label>
                        <select id="searchRadius" class="form-control">
                            <option value="1000" <?php echo $default_radius == '1000' ? 'selected' : ''; ?>>1 km</option>
                            <option value="2000" <?php echo $default_radius == '2000' ? 'selected' : ''; ?>>2 km</option>
                            <option value="5000" <?php echo $default_radius == '5000' ? 'selected' : ''; ?>>5 km</option>
                            <option value="10000" <?php echo $default_radius == '10000' ? 'selected' : ''; ?>>10 km</option>
                            <option value="20000" <?php echo $default_radius == '20000' ? 'selected' : ''; ?>>20 km</option>
                            <option value="50000" <?php echo $default_radius == '50000' ? 'selected' : ''; ?>>50 km</option>
                        </select>
                    </div>
                    
                    <button class="btn btn-primary btn-block" id="searchBtn">
                        <i class="fa fa-search"></i> <?php echo _l('search'); ?>
                    </button>
                </div>
                
                <div class="results-panel panel-body">
                    <div class="results-header">
                        <span id="resultCount">0 <?php echo _l('results'); ?></span>
                        <div class="results-header-actions">
                            <button class="btn btn-success btn-xs" id="saveAllBtn" style="display:none;">
                                <i class="fa fa-save"></i> <span class="btn-text"><?php echo _l('save_to_crm'); ?></span>
                            </button>
                            <button class="btn btn-danger btn-xs btn-clear-results" id="clearResultsBtn" style="display:none;">
                                <i class="fa fa-times"></i> <span class="btn-text"><?php echo _l('clear_results'); ?></span>
                            </button>
                        </div>
                    </div>
                    <div class="results-list" id="resultsList"></div>
                    <div class="load-more text-center mtop10">
                        <button class="btn btn-default" id="loadMoreBtn" style="display:none;">
                            <?php echo _l('load_more'); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="extractor-right">

                
                <div class="map-container panel-body">
                    <div id="map"></div>
                </div>
                
                <div class="preview-panel panel-body mtop15">
                    <div class="clearfix mbot15">
                        <h4 class="font-medium pull-left mbot0 mtop5"><?php echo _l('preview_table'); ?></h4>
                        <button class="btn btn-info pull-right btn-sm" id="exportExcelBtn">
                            <i class="fa fa-file-excel-o"></i> <span class="btn-text"><?php echo _l('export_excel'); ?></span>
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="previewTable">
                            <thead>
                                <tr>
                                    <th style="width: 40px; text-align: center;"><input type="checkbox" id="selectAllExport"></th>
                                    <th><?php echo _l('name'); ?></th>
                                    <th class="hidden-xs"><?php echo _l('address'); ?></th>
                                    <th><?php echo _l('phone'); ?></th>
                                    <th class="hidden-xs hidden-sm"><?php echo _l('website'); ?></th>
                                    <th><?php echo _l('rating'); ?></th>
                                    <th><?php echo _l('actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
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
</body>
</html>
