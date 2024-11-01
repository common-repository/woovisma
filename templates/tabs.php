<div id="ch2pho-general" class="wrap" style="width:96%;">
        <link  rel="stylesheet" type="text/css" href="<?php echo WOOVISMA_PLUGIN_URL; ?>css/main.css" />
    <h2 class="nav-tab-wrapper">
            <!--<a href="?page=woo-visma-settings" class="nav-tab <?php //echo $active_tab == "default" ? 'nav-tab-active' : ''; ?>">Welcome</a>-->
            <a href="?page=woo-visma-settings&tab=visma" class="nav-tab <?php echo $active_tab == "visma" ? 'nav-tab-active' : ''; ?>">Connection Settings</a>
            <a href="?page=woo-visma-settings&tab=general" class="nav-tab <?php echo $active_tab == "general" ? 'nav-tab-active' : ''; ?>">General Settings</a>
            <a href="?page=woo-visma-settings&tab=manual" class="nav-tab <?php echo $active_tab == "manual" ? 'nav-tab-active' : ''; ?>">Manual Functions</a>
            <a href="?page=woo-visma-settings&tab=not_synced" class="nav-tab <?php echo $active_tab == "not_synced" ? 'nav-tab-active' : ''; ?>">Synced Status</a>
            <a href="?page=woo-visma-settings&tab=developer_mode" class="nav-tab <?php echo $active_tab == "developer_mode" ? 'nav-tab-active' : ''; ?>">Log Settings</a>
    </h2>
<?php
    if(is_license_key_valid()!="Active")
    {
        echo "<div id='message' class='error fade'><p><strong>WooCommerce Visma Integration: License Key Invalid! </strong>"
        . " <button class='button button-primary' onclick=\"window.open('http://whmcs.onlineforce.net/cart.php?a=add&pid=62&carttpl=flex-web20cart&language=English','_blank');\" style='margin:5px' title='' type='button button-primary'>Get license Key</button></p></div>";
    }
?>
    <div style="width:100%;" class="woovismatab">
            <?php echo $tplContent; ?>
    </div>
</div>
