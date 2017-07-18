<?php
/**
  * The file that shows the settings screen
  */
?>
<div class="wrap">
    <h2><?php _e("Settings", DATADOME_PLUGIN_SLUG__);?></h2>

    <?php if ($this->notice) { ?>
    <div class="updated"><p><?php echo $this->notice;?></p></div>
    <?php } ?>

    <?php if ($this->error) { ?>
    <div class="error"><p><?php echo $this->error;?></p></div>
    <?php } ?>

    <form method="post" action="">
        <table class="form-table">
            <?php if (!$key) { ?>
            <tr valign="top">
                <td colspan="2">
                    <p class="description"><?php echo sprintf(__("Create account on %s to get a license key", DATADOME_PLUGIN_SLUG__), "<a href='http://datadome.co' target='_new'>http://datadome.co</a>");?></p>
                </td>
            </tr>
            <?php } ?>
            <tr valign="top">
                <th scope="row"><?php _e("API Key", DATADOME_PLUGIN_SLUG__);?></th>
                <td>
                    <input type="text" name="key" id="key" value="<?php echo $key;?>">
                    <span style="display: none" id="invalid-key"><br><?php _e("Invalid license key", DATADOME_PLUGIN_SLUG__);?></span>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e("JS Key", DATADOME_PLUGIN_SLUG__);?></th>
                <td>
                    <input type="text" name="jskey" id="jskey" value="<?php echo $jskey;?>" class="regular-text">
                    <span style="display: none" id="invalid-js-key"><br><?php _e("Invalid JS key", DATADOME_PLUGIN_SLUG__);?></span>
                </td>
            </tr>
            <?php if ($key && $jskey) { ?>
            <tr valign="top">
                <th scope="row"><?php _e("Send statistics to DataDome", DATADOME_PLUGIN_SLUG__);?></th>
                <td>
                    <input type="checkbox" name="sendstats" id="sendstats" value="1" <?php echo self::getOption("sendstats") == "1" ? "checked" : ""?>>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e("API Endpoint", DATADOME_PLUGIN_SLUG__);?></th>
                <td>
                    <select name="server">
                    <?php
                        $list = self::getOption("servers");
                        foreach ($list as $array) {
                            $extra  = $array["host"] == self::getOption("server") ? "selected" : "";
                    ?>
                        <option value="<?php echo $array["host"];?>" <?php echo $extra;?>><?php echo $array["name"];?>&nbsp;(<?php echo $array["time"];?>ms)</option>
                    <?php
                        }
                    ?>
                    </select>
                    <input type="button" name="dd-refresh" id="dd-refresh" value="<?php _e("Detest Best Server", DATADOME_PLUGIN_SLUG__);?>" class="button button-secondary">
                    <span style="display: none" id="detect-msg"><br><?php _e("Latency Benchmark test in progress", DATADOME_PLUGIN_SLUG__);?>...</span>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e("Timeout", DATADOME_PLUGIN_SLUG__);?></th>
                <td>
                    <input type="number" name="timeout" id="timeout" value="<?php echo self::getOption("timeout");?>" min="30" max="1000">
                    <p class="description">milliseconds</p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row" colspan="2"><h2><?php _e("Advanced Settings", DATADOME_PLUGIN_SLUG__);?></h2></th>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e("Enable HTTPS", DATADOME_PLUGIN_SLUG__);?></th>
                <td>
                    <input type="checkbox" name="https" id="https" value="1" <?php echo self::getOption("https") == "1" ? "checked" : ""?>>
                    <p class="description"><?php _e("Activating HTTPS will increase the latency", DATADOME_PLUGIN_SLUG__);?></p>
                </td>
            </tr>
            <?php } ?>
        </table>
    
        <?php submit_button(__("Save Changes", DATADOME_PLUGIN_SLUG__), "primary", "dd-submit"); ?>
    </form>

    <form action="" method="post" id="refresh-form">
        <input type="hidden" name="refresh" value="1">
    </form>
</div>