 
<?php
$options = get_option( 'woovisma_options' );
$msgSettings="";
if(!isset($options["nossl"]) || $options["nossl"]<1)
{
    if($options["client_id"]=="Your Visma Client ID" || $options["client_secret"]=="Your Visma Client Secret" || $options["redirect_uri"]=="https://your/registered/base/url/")
    {
        $msgSettings = "Visma connection not yet established";
    }
}
if(!empty($msgSettings)) 
{
    echo $msgSettings;
    return;
}
if ( isset( $_GET['product_sync_status'] ) && $_GET['product_sync_status'] == '1' )
                        {
                            showErrorMessage("Product Sync Completed");
                        }
                        else if ( isset( $_GET['product_sync_status'] ) && $_GET['product_sync_status'] == '2' )
                        {
                            if(isset($_GET["not_synced"])) 
                            {
                                showErrorMessage("Customer ID {$_GET["not_synced"]} failed");
                            }
                            else
                            {
                                showErrorMessage("Customer Sync Completed");
                            }
                        }
                        else if ( isset( $_GET['product_sync_status'] ) && $_GET['product_sync_status'] == '3' )
                        {
                            $uniwinMessage=uniwinMessage(false);
                            woovisma_addlog("Uniwin Message: ".$uniwinMessage);
                            if(empty($uniwinMessage))
                            {
                                $uniwinMessage="Order Sync Completed";
                            }
							showErrorMessage($uniwinMessage);
                        }
                        else if ( isset( $_GET['product_sync_status'] ) && $_GET['product_sync_status'] == '-3' )
                        {
                            showErrorMessage("No Orders to Sync");
                        }
                        else if ( isset( $_GET['product_sync_status'] ) && $_GET['product_sync_status'] == '99' )
                        {
                            showErrorMessage("There Is No Products To Sync");
                        }
                        else if ( isset( $_GET['product_sync_status'] ) && $_GET['product_sync_status'] == '98' )
                        {
                            showErrorMessage("There Is No Customers To Sync");
                        }
                        else if ( isset( $_GET['product_sync_status'] ) && $_GET['product_sync_status'] == '97' )
                        {
                            showErrorMessage("There Is No Orders To Sync");
                        }
                        else if ( isset( $_GET['product_sync_status'] ) && $_GET['product_sync_status'] == '1001' )
                        {
                            showErrorMessage("License Invalid");
                        }
?>

<form  action="admin.php">
            <input type="hidden" name="page" value="woo-visma-settings" />
            <input type="hidden" name="tab" value="not_synced" />
            <div class="widgetsyncstatus">
                    <table><tr><td>
            <select name="wvaction">
                    <option value="" <?php echo isset($_REQUEST["wvaction"]) && empty($_REQUEST["wvaction"])?"selected":""; ?>>Choose</option>
                    <option value="product" <?php echo isset($_REQUEST["wvaction"]) && $_REQUEST["wvaction"]=="product"?"selected":""; ?>>Product</option>
                    <option value="customer" <?php echo isset($_REQUEST["wvaction"]) && $_REQUEST["wvaction"]=="customer"?"selected":""; ?>>Customer</option>
                    <option value="order" <?php echo isset($_REQUEST["wvaction"]) && $_REQUEST["wvaction"]=="order"?"selected":""; ?>>Order</option>
            </select></td><td>
                    <input type="radio" name="wvactiontype" value="1" <?php echo isset($_REQUEST["wvactiontype"]) && $_REQUEST["wvactiontype"]=="1"?"checked":""; ?> />Synced &nbsp;&nbsp;</td><td><input type="radio" name="wvactiontype" value="0" <?php echo isset($_REQUEST["wvactiontype"]) && empty($_REQUEST["wvactiontype"]) || !isset($_REQUEST["wvactiontype"])?"checked":""; ?> />Not Synced&nbsp;&nbsp;</td> 
            <td><input type="submit" value="Show" /></td></tr></table>
            </div>
    </form>

<?php
function generateCustomerSyncTable($caption,$arrDataSync,$page,$arrHead,$hideSync=false,$hideCheckbox=false)
{
    $options = get_option( 'woovisma_options' );
    $arrProdIDNotSynced=$arrDataSync["data"];
    woovisma_addlog("products:".print_r($arrProdIDNotSynced,true)); 
    $arrTH=array();
    foreach($arrHead as $head=>$dataField)
    {
        $arrTH[]="<th class='manage-column'><b>{$head}</b></th>";
    }
    $strTH=implode("",$arrTH);
    $strSyncTH="";
    if(!$hideSync)
    {
        $strSyncTH="<th class='manage-column'>Sync</th>";
    }
    $strCheckboxTH="";
    if(!$hideCheckbox)
    {
        $strCheckboxTH="<th id='cb' class='manage-column column-cb check-column'><input onchange='if(jQuery(this).attr(\"checked\")){ jQuery(\".checkcol\").attr(\"checked\",true);}else {jQuery(\".checkcol\").attr(\"checked\",false);}' class='cb-select-all-1' type='checkbox' /></th>";
    }
    $table="<form method=\"post\" action=\"admin-post.php\" id=\"syncform\">
        <input type=\"hidden\" name=\"action\" value=\"save_woovisma_sync\" />
        <input type=\"hidden\" name=\"wvaction\" value=\"customer\" />
        <!-- Adding security through hidden referrer field -->
        ".wp_nonce_field( 'woovisma' )."
        <input type=\"hidden\" name=\"client_id\"
    value=\"".esc_html( $options['client_id'] )."\"/><input type=\"hidden\" name=\"client_secret\"
    value=\"".esc_html( $options['client_secret'] )."\"/>
            <input type=\"hidden\" name=\"redirect_uri\"
    value=\"".esc_html( $options['redirect_uri'] )."\"/><h1>{$caption}</h1><table class='synctabcontent  wp-list-table widefat fixed striped pages'>
         <tr class='row-title'>{$strCheckboxTH}
         {$strTH}
         {$strSyncTH}</tr>
         ";
        if($arrProdIDNotSynced)
        foreach($arrProdIDNotSynced as $prodIDNotSynced)
        {
            $table=$table."<tr>";
            $objUser=get_user_by("id", $prodIDNotSynced);//trace($arrProdIDNotSynced);
            //$table=$table."<td>{$objUser->data->user_login}</td><td>:</td><td style='text-align:left;'>{$objUser->data->user_email}</td>";
            //$arrProd=$objProduct->getProductInfo($prodIDNotSynced);
            $arrTD=array();
            foreach($arrHead as $head=>$dataField)
            {
                $data=$objUser->data->$dataField;
                $arrTD[]="<td>{$data}</td>";
            }
            $strTD=implode("",$arrTD);
            $strSyncTD="";
            if(!$hideSync)
            {
                $strSyncTD="<td style='text-align:left;'><a href=\"javascript:void(0);\" onclick=\"submitThisForm({$prodIDNotSynced});\">Sync</a></td>";
            }
            $strCheckboxTD="";
            if(!$hideCheckbox)
            {
                $strCheckboxTD="<th class='check-column'><input class='checkcol' id='cbselect{$prodIDNotSynced}' type='checkbox' name='syncid[]' value='{$prodIDNotSynced}' /></th>";
            }
            $table=$table."{$strCheckboxTD}
                {$strTD}{$strSyncTD}";
            $table=$table."</tr>";
        }
        $pagination="";
        if($arrDataSync["pages"]>0)
        {
            $prev="Prev";
            if($page>1)
            {
                $prev="<a href='admin.php?page=woo-visma-settings&tab=not_synced&wvactiontype=1&wvaction=customer&wvpage=".($page-1)."'>Prev</a>";
            }
            $next="Next";
            if($page<$arrDataSync["pages"])
            {
                $next="<a href='admin.php?page=woo-visma-settings&tab=not_synced&wvactiontype=1&wvaction=customer&wvpage=".($page+1)."'>Next</a>";
            }
            $pagination=" {$prev}  {$arrDataSync["page"]} of {$arrDataSync["pages"]}  {$next}";
        }
        $table=$table."</table>";
        if($caption!="Synced Customers")
        {
            $table=$table."<input type='submit' value='Sync Checked' /> ";
        }
        $table=$table." {$pagination}</form>
    <script>
    function submitThisForm(prodID)
    {
        if(jQuery('#cbselect'+prodID).attr('checked'))
        {
            jQuery('#cbselect'+prodID).attr('checked',false);
        }
        else
        {
            jQuery('#cbselect'+prodID).attr('checked',true);
        }
        jQuery('#syncform').submit();
    }
    </script>
    ";
        /*$table=$table."</table></form>
    <a href='admin.php?page=woo-visma-settings&tab=not_synced&wvactiontype=1&wvaction=product&wvpage=".($page-1)."'>Prev</a>  {$arrDataSync["page"]} of {$arrDataSync["pages"]}  <a href='admin.php?page=woo-visma-settings&tab=not_synced&wvactiontype=1&wvaction=product&wvpage=".($page+1)."'>Next</a> 
    ";*/
    return $table;
}
function generateSyncTable($caption,$arrDataSync,$page,$arrHead,$hideSync=false,$hideCheckbox=false)
{
    $objProduct=new Woovisma_Product();
    $arrProdIDNotSynced=$arrDataSync["data"];
    woovisma_addlog("products:".print_r($arrProdIDNotSynced,true)); 
    $arrTH=array();
    foreach($arrHead as $head=>$dataField)
    {
        $arrTH[]="<th class='manage-column'><b>{$head}</b></th>";
    }
    $strTH=implode("",$arrTH);
    $strSyncTH="";
    if(!$hideSync)
    {
        $strSyncTH="<th class='manage-column'>Sync</th>";
    }
    $options = get_option( 'woovisma_options' ); 
    $strCheckboxTH="";
    if(!$hideCheckbox)
    {
        $strCheckboxTH="<th id='cb' class='manage-column column-cb check-column'><input onchange='if(jQuery(this).attr(\"checked\")){ jQuery(\".checkcol\").attr(\"checked\",true);}else {jQuery(\".checkcol\").attr(\"checked\",false);}' class='cb-select-all-1' type='checkbox' /></th>";
    }
    $table="<form method=\"post\" action=\"admin-post.php\" id=\"syncform\">
        <input type=\"hidden\" name=\"action\" value=\"save_woovisma_sync\" />
        <!-- Adding security through hidden referrer field -->
        ".wp_nonce_field( 'woovisma' )."
        <input type=\"hidden\" name=\"client_id\"
    value=\"".esc_html( $options['client_id'] )."\"/><input type=\"hidden\" name=\"client_secret\"
    value=\"".esc_html( $options['client_secret'] )."\"/>
            <input type=\"hidden\" name=\"redirect_uri\"
    value=\"".esc_html( $options['redirect_uri'] )."\"/><h1>{$caption}</h1><table  class='synctabcontent wp-list-table widefat fixed striped pages'>
         <tr class='row-title'>{$strCheckboxTH}
         {$strTH}
         {$strSyncTH}</tr>
         ";
        if($arrProdIDNotSynced)
        foreach($arrProdIDNotSynced as $prodIDNotSynced)
        {
            $table=$table."<tr>";
            $arrProd=$objProduct->getProductInfo($prodIDNotSynced);
            $arrTD=array();
            foreach($arrHead as $head=>$dataField)
            {
                $arrTD[]="<td>{$arrProd[$dataField]}</td>";
            }
            $strTD=implode("",$arrTD);
            $strSyncTD="";
            if(!$hideSync)
            {
                $strSyncTD="<td style='text-align:left;'><a href=\"javascript:void(0);\" onclick=\"submitThisForm({$prodIDNotSynced});\">Sync</a></td>";
            }
            $strCheckboxTD="";
            if(!$hideCheckbox)
            {
                $strCheckboxTD="<th class='check-column'><input class='checkcol' id='cbselect{$prodIDNotSynced}' type='checkbox' name='syncid[]' value='{$prodIDNotSynced}' /></th>";
            }
            $table=$table."{$strCheckboxTD}
                {$strTD}{$strSyncTD}";
            $table=$table."</tr>";
        }
        $pagination="";
        if($arrDataSync["pages"]>0)
        {
            $prev="Prev";
            if($page>1)
            {
                $prev="<a href='admin.php?page=woo-visma-settings&tab=not_synced&wvactiontype=1&wvaction=product&wvpage=".($page-1)."'>Prev</a>";
            }
            $next="Next";
            if($page<$arrDataSync["pages"])
            {
                if($caption=="Synced Products")
                {
                    $next="<a href='admin.php?page=woo-visma-settings&tab=not_synced&wvactiontype=1&wvaction=product&wvpage=".($page+1)."'>Next</a>";
                }
                else
                {
                    $next="<a href='admin.php?page=woo-visma-settings&tab=not_synced&wvactiontype=0&wvaction=product&wvpage=".($page+1)."'>Next</a>";
                }
            }
            $pagination=" {$prev}  {$arrDataSync["page"]} of {$arrDataSync["pages"]}  {$next}";
        }
        $table=$table."</table>";
        if($caption!="Synced Products")
        {
            $table=$table."<input type='submit' value='Sync Checked' /> ";
        }
        $table=$table." {$pagination}</form>
    <script>
    function submitThisForm(prodID)
    {
        if(jQuery('#cbselect'+prodID).attr('checked'))
        {
            jQuery('#cbselect'+prodID).attr('checked',false);
        }
        else
        {
            jQuery('#cbselect'+prodID).attr('checked',true);
        }
        jQuery('#syncform').submit();
    }
    </script>
    ";
        /*$table=$table."</table></form>
    <a href='admin.php?page=woo-visma-settings&tab=not_synced&wvactiontype=1&wvaction=product&wvpage=".($page-1)."'><<<</a>  {$arrDataSync["page"]} of {$arrDataSync["pages"]}  <a href='admin.php?page=woo-visma-settings&tab=not_synced&wvactiontype=1&wvaction=product&wvpage=".($page+1)."'>>>></a> 
    ";*/
    return "<div style='width:700px;text-align:left'>{$table}</div>";
}
function generateOrdersSyncTable($caption,$arrDataSync,$page,$arrHead,$hideSync=false,$hideCheckbox=false)
{
    $options = get_option( 'woovisma_options' );
    $arrProdIDNotSynced=$arrDataSync["data"];
    woovisma_addlog("products:".print_r($arrProdIDNotSynced,true)); 
    $arrTH=array();
    $arrTH[]="<th class='manage-column'>Order Number</th>";
    foreach($arrHead as $head=>$dataField)
    {
        $arrTH[]="<th class='manage-column'><b>{$head}</b></th>";
    }
    $strTH=implode("",$arrTH);
    $strSyncTH="";
    if(!$hideSync)
    {
        $strSyncTH="<th class='manage-column'>Sync</th>";
    }
    $strCheckboxTH="";
    if(!$hideCheckbox)
    {
        $strCheckboxTH="<th id='cb' class='manage-column column-cb check-column'><input onchange='if(jQuery(this).attr(\"checked\")){ jQuery(\".checkcol\").attr(\"checked\",true);}else {jQuery(\".checkcol\").attr(\"checked\",false);}' class='cb-select-all-1' type='checkbox' /></th>";
    }
    $table="<form method=\"post\" action=\"admin-post.php\" id=\"syncform\">
        <input type=\"hidden\" name=\"action\" value=\"save_woovisma_sync\" />
        <input type=\"hidden\" name=\"wvaction\" value=\"order\" />
        <!-- Adding security through hidden referrer field -->
        ".wp_nonce_field( 'woovisma' )."
        <input type=\"hidden\" name=\"client_id\"
    value=\"".esc_html( $options['client_id'] )."\"/><input type=\"hidden\" name=\"client_secret\"
    value=\"".esc_html( $options['client_secret'] )."\"/>
            <input type=\"hidden\" name=\"redirect_uri\"
    value=\"".esc_html( $options['redirect_uri'] )."\"/><h1>{$caption}</h1><table class='synctabcontent wp-list-table widefat fixed striped pages'>
         <tr class='row-title'>{$strCheckboxTH}
         {$strTH}
         {$strSyncTH}</tr>
         ";
        if($arrProdIDNotSynced)
        foreach($arrProdIDNotSynced as $prodIDNotSynced)
        {
            $table=$table."<tr>";
            $objWCOrder=new WC_Order($prodIDNotSynced);
            //$arrProd=$objProduct->getProductInfo($prodIDNotSynced);
            $arrTD=array();
            $arrTD[]="<td>#{$prodIDNotSynced}</td>";
            foreach($arrHead as $head=>$dataField)
            {
                $data=$objWCOrder->post->$dataField;
                $arrTD[]="<td>{$data}</td>";
            }
            $strTD=implode("",$arrTD);
            $strSyncTD="";
            if(!$hideSync)
            {
                $strSyncTD="<td style='text-align:left;'><a href=\"javascript:void(0);\" onclick=\"submitThisForm({$prodIDNotSynced});\">Sync</a></td>";
            }
            $strCheckboxTD="";
            if(!$hideCheckbox)
            {
                $strCheckboxTD="<th class='check-column'><input class='checkcol' id='cbselect{$prodIDNotSynced}' type='checkbox' name='syncid[]' value='{$prodIDNotSynced}' /></th>";
            }
            $table=$table."{$strCheckboxTD}
                {$strTD}{$strSyncTD}";
            $table=$table."</tr>";
        }
        $pagination="";
        if($arrDataSync["pages"]>0)
        {
            $prev="Prev";
            if($page>1)
            {
                $prev="<a href='admin.php?page=woo-visma-settings&tab=not_synced&wvactiontype=1&wvaction=order&wvpage=".($page-1)."'>Prev</a>";
            }
            $next="Next";
            if($page<$arrDataSync["pages"])
            {
                if($caption=="Synced Orders")
                {
                    $next="<a href='admin.php?page=woo-visma-settings&tab=not_synced&wvactiontype=1&wvaction=order&wvpage=".($page+1)."'>Next</a>";
                }
                else
                {
                    $next="<a href='admin.php?page=woo-visma-settings&tab=not_synced&wvactiontype=0&wvaction=order&wvpage=".($page+1)."'>Next</a>";
                }
            }
            $pagination=" {$prev}  {$arrDataSync["page"]} of {$arrDataSync["pages"]}  {$next}";
        }
        $table=$table."</table>";
        if($caption!="Synced Orders")
        {
            $table=$table."<input type='submit' value='Sync Checked' /> ";
        }
        $table=$table." {$pagination}</form>
    <script>
    function submitThisForm(prodID)
    {
        if(jQuery('#cbselect'+prodID).attr('checked'))
        {
            jQuery('#cbselect'+prodID).attr('checked',false);
        }
        else
        {
            jQuery('#cbselect'+prodID).attr('checked',true);
        }
        jQuery('#syncform').submit();
    }
    </script>
    ";
    return $table;
}
echo "<hr />";
if(isset($_REQUEST["wvaction"]) && !empty($_REQUEST["wvaction"]))
{
    include_once(dirname(dirname(__DIR__))."/includes/class-woovisma-product.php");
    if($_REQUEST["wvaction"]=="product")
    {
        $objProduct=new Woovisma_Product();
        $page=isset($_REQUEST["wvpage"])?$_REQUEST["wvpage"]:1;
        $arrDataSync=$objProduct->getProductsNotSynced($page);
        $arrHead=array("Name"=>"name","SKU"=>"sku");
        if(isset($_REQUEST["wvactiontype"]) && $_REQUEST["wvactiontype"]>0)
        {
            $table=generateSyncTable("Synced Products",$arrDataSync,$page,$arrHead,true,true);
        }
        else
        {
            $table=generateSyncTable("Not Synced Products",$arrDataSync,$page,$arrHead);
        }
        echo $table;
    }
    else if($_REQUEST["wvaction"]=="customer")
    {
        include_once(dirname(dirname(__DIR__))."/includes/class-woovisma-customer.php");
        $objCustomer=  Woovisma_Customer::getInstance();
        $arrCustIDNotSynced=$objCustomer->getCustomersNotSynced();
    
        $page=isset($_REQUEST["wvpage"])?$_REQUEST["wvpage"]:1;
        $arrDataSync=$objCustomer->getCustomersNotSynced($page);
        $arrHead=array("Name"=>"name","SKU"=>"sku");
        $arrHead=array("Login"=>"user_login","EMail"=>"user_email");
        if(isset($_REQUEST["wvactiontype"]) && $_REQUEST["wvactiontype"]>0)
        {
            $table=generateCustomerSyncTable("Synced Customers",$arrDataSync,$page,$arrHead,true,true);
        }
        else
        {
            $table=generateCustomerSyncTable("Not Synced Customers",$arrDataSync,$page,$arrHead);
        }
        echo $table;
    }
    else
    {
        //include_once(dirname(dirname(__DIR__))."/includes/class-woovisma-order.php");
        $obj=  Woovisma_Order::getInstance();
        $obj->init();
        //$objProduct=new Woovisma_Product();
        $page=isset($_REQUEST["wvpage"])?$_REQUEST["wvpage"]:1;
        $arrDataSync=$obj->getOrdersNotSynced($page);
        $arrHead=array("Status"=>"post_status");
        if(isset($_REQUEST["wvactiontype"]) && $_REQUEST["wvactiontype"]>0)
        {
            $table=generateOrdersSyncTable("Synced Orders",$arrDataSync,$page,$arrHead,true,true);
        }
        else
        {
            $table=generateOrdersSyncTable("Not Synced Orders",$arrDataSync,$page,$arrHead);
        }
        echo $table;
        /*
        $arrIDNotSynced=$obj->getOrdersNotSynced();
        $table="<fieldset style='padding: 1em;font:80%/1 sans-serif;border:1px;border-color:#aaa;'>
         <legend><h1>Orders</h1></legend><table width='100%'>
         <tr><th style='text-align:left;width:200px;'>Order Number</th><th></th><th style='text-align:left;'>Status</th></tr>
         ";
        if($arrIDNotSynced)
        foreach($arrIDNotSynced as $idNotSynced)
        {
            $table=$table."<tr>";
            $objWCOrder=new WC_Order($idNotSynced);
            $table=$table."<td>#{$idNotSynced}</td><td>:</td><td style='text-align:left;'>{$objWCOrder->post->post_status}</td>";
            $table=$table."</tr>";
        }
        $table=$table."</table></datalist>
        </fieldset>";
        echo $table;;*/
    }
}
?>
