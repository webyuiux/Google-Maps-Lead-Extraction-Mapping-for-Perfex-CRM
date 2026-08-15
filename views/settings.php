<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-heading">
                        <h4 class="font-medium"><?php echo _l('google_maps_extractor_settings'); ?></h4>
                    </div>
                    <div class="panel-body">
                        <?php echo form_open(admin_url('google_maps_extractor/settings')); ?>
                        
                        <div class="form-group">
                            <label for="google_maps_api_key"><?php echo _l('google_maps_api_key'); ?></label>
                            <input type="text" class="form-control" name="google_maps_api_key" id="google_maps_api_key" value="<?php echo htmlspecialchars($api_key); ?>">
                            <p class="text-muted mtop5"><?php echo _l('google_maps_api_key_help'); ?></p>
                        </div>
                        
                        <div class="form-group">
                            <label for="default_radius"><?php echo _l('default_search_radius'); ?></label>
                            <select name="default_radius" id="default_radius" class="form-control">
                                <option value="1000" <?php echo $default_radius == '1000' ? 'selected' : ''; ?>>1 km</option>
                                <option value="2000" <?php echo $default_radius == '2000' ? 'selected' : ''; ?>>2 km</option>
                                <option value="5000" <?php echo $default_radius == '5000' ? 'selected' : ''; ?>>5 km</option>
                                <option value="10000" <?php echo $default_radius == '10000' ? 'selected' : ''; ?>>10 km</option>
                                <option value="20000" <?php echo $default_radius == '20000' ? 'selected' : ''; ?>>20 km</option>
                                <option value="50000" <?php echo $default_radius == '50000' ? 'selected' : ''; ?>>50 km</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="default_location"><?php echo _l('default_location'); ?></label>
                            <input type="text" class="form-control" name="default_location" id="default_location" value="<?php echo htmlspecialchars($default_location); ?>">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> <?php echo _l('submit'); ?>
                            </button>
                            <a href="<?php echo admin_url('google_maps_extractor'); ?>" class="btn btn-default">
                                <?php echo _l('cancel'); ?>
                            </a>
                        </div>
                        
                        <?php echo form_close(); ?>
                    </div>
                </div>
                
                <div class="panel_s">
                    <div class="panel-heading">
                        <h4 class="font-medium">Google Maps API Setup Instructions</h4>
                    </div>
                    <div class="panel-body">
                        <ol>
                            <li>Go to <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a></li>
                            <li>Create a new project or select an existing one</li>
                            <li>Enable the following APIs:
                                <ul>
                                    <li>Maps JavaScript API</li>
                                    <li>Places API</li>
                                    <li>Geocoding API</li>
                                </ul>
                            </li>
                            <li>Go to Credentials and create an API Key</li>
                            <li>Copy the API key and paste it in the field above</li>
                            <li>Optionally, restrict the API key to your domain for security</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
</body>
</html>
