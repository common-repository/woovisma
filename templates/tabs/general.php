                        <?php 
                        
        
    $objpaymentOptions=new Woovisma_Customer();
    $result=$objpaymentOptions->paymentOptionsList();
	
    woovisma_addlog("Result of payment options");
	woovisma_addlog($result);
	
                if ( isset( $_GET['message'] ) && $_GET['message'] == '2' )
                {
                    echo "<div id='message' class='updated fade'><p><strong>Sitewide Settings Saved</strong></p></div>";
                }
                $options = get_option( 'woovisma_options' );
 
                        ///default values
                        //$arrInitiateOrderSyncSelected=array();
                        $arrInitiateOrderSync=array();
                        if(isset($options["woovismaoptname"]["initiateordersync"]["selected"]["type"]))
                        {
                           $arrInitiateOrderSync["selected"]["type"]=$options["woovismaoptname"]["initiateordersync"]["selected"]["type"];
                           $arrInitiateOrderSync["selected"]["data"]=$options["woovismaoptname"]["initiateordersync"]["type"][$arrInitiateOrderSync["selected"]["type"]]; ///indices of checked elements
                        }
                        else
                        {
                            $arrInitiateOrderSync["selected"]["type"]="event";
                            $arrInitiateOrderSync["selected"]["data"]=array(0=>1); ///indices of checked elements
                        }
                        $arrInitiateOrderSync["type"]["event"][]=array("caption"=>"Checkout Order Processed","selected"=>0);
                        $arrInitiateOrderSync["type"]["status"][]=array("caption"=>"Pending Payment","selected"=>0);
                        $arrInitiateOrderSync["type"]["status"][]=array("caption"=>"Processing","selected"=>0);
                        $arrInitiateOrderSync["type"]["status"][]=array("caption"=>"On Hold","selected"=>0);
                        $arrInitiateOrderSync["type"]["status"][]=array("caption"=>"Completed","selected"=>0);
                        /*$arrInitiateOrderSync["type"]["status"][]=array("caption"=>"Cancelled","selected"=>0);
                        $arrInitiateOrderSync["type"]["status"][]=array("caption"=>"Failed","selected"=>0);*/

                        $arrChosenMethod=$arrInitiateOrderSync["type"][$arrInitiateOrderSync["selected"]["type"]];
                        ///update values from settings
                        /*if(isset($options["woovismaoptname"]["initiateordersync"]))
                        {
                            $initiateordersync =$options["woovismaoptname"]["initiateordersync"];
                            $arrInitiateOrderSync["selected"] = json_decode($initiateordersync);
                            foreach($arrChosenMethod as $ind=>$arrData)
                            {
                                if(isset($arrInitiateOrderSync["selected"]["data"][$ind]) && $arrInitiateOrderSync["selected"]["data"][$ind]>0)
                                {
                                    $arrData["selected"]=1;
                                    $arrChosenMethod[$ind]=$arrData;
                                }
                            }
                        }*/
?>
<script>
    jQuery(document).ready(function ()
    {
<?php
        if($arrInitiateOrderSync["selected"]["type"]=="event")
        {
            echo "jQuery('#initiateordersyncstatus').hide();
            jQuery('#initiateordersyncevent').show();";
        }
        else
        {
            echo "jQuery('#initiateordersyncevent').hide();
            jQuery('#initiateordersyncstatus').show();";
        }
?>
    });
    function changeOrderSync(obj)
    {
        var syncType=jQuery(obj).val();
        if(syncType=="event")
        {
            jQuery("#initiateordersyncstatus").hide();
            jQuery("#initiateordersyncevent").show();
        }
        else
        {
            jQuery("#initiateordersyncevent").hide();
            jQuery("#initiateordersyncstatus").show();
        }
    }
</script>
                <form method="post" action="admin-post.php" name="settings" id="settings">
                <input type="hidden" name="action" value="save_woovisma_settings" />
                <input type="hidden" name="tab" value="general" />
                <?php wp_nonce_field( 'woovisma' ); ?>
                <table class="generaltabcontent"><tr><td colspan="4" style="text-align: center;"><b><div id="errmsg" style="color:red;"></div></b></td></tr>
                        <tr><td colspan="4"><h3><strong>Sync Settings</strong></h3></td></tr>
                        <tr>
                            <td class="generaltabcol1"><b>Automatic sync</b></td><td class="generaltabcol2">:</td><td class="generaltabcol3">
                                        <select name="woovismaoptname[manualsynconly]">
                                        <?php
                                            if(!isset($options["woovismaoptname"]["manualsynconly"]) || $options["woovismaoptname"]["manualsynconly"]>0)
                                            {
                                                echo "<option value='0'>Disabled</option>";
                                                echo "<option value='1' selected>Enabled</option>";
                                            }
                                            else
                                            {
                                                echo "<option value='0' selected>Disabled</option>";
                                                echo "<option value='1'>Enabled</option>";
                                            }
                                            
                                        ?>
                                        </select>
                                        <!--<input type="checkbox" name="woovismaoptname[manualsynconly]" value="1" <?php echo isset($options["woovismaoptname"]["manualsynconly"])?"checked":""; ?>-->
                                </td> 
                        </tr>
                        <tr>
                                 <td><b>Product Prefix</b></td><td>:</td><td><input type="textbox" id="product_prefix" name="woovismaoptname[product_prefix]" value="<?php echo isset($options["woovismaoptname"]["product_prefix"])?$options["woovismaoptname"]["product_prefix"]:""; ?>" /></td><td>The total number of characters in SKU with prefix should not exceed 15</td>
                        </tr>
                         <tr>
                                 <td><b>Product Stock Update Only</b></td><td>:</td><td><input type="checkbox" id="license_key" name="woovismaoptname[stockupdateonly]" value="1" <?php echo isset($options["woovismaoptname"]["stockupdateonly"])?"checked":""; ?> /></td><td></td>
                        </tr>
                        <tr><td colspan="4"><br /><h3><strong>Order sync settings</strong></h3></td></tr>
                        <tr>
                                <td><b>Activate old orders sync</b></td><td>:</td><td colspan="2"><input type="checkbox" id="license_key" name="woovismaoptname[oldordersync]" value="1" <?php echo isset($options["woovismaoptname"]["oldordersync"])?"checked":""; ?> />&nbsp;&nbsp;Also sync orders created before woovisma installation.</td>
                        </tr>
                        <tr>
                                 <td><b>On Checkout</b></td><td>:</td><td>
                                        <select name="woovismaoptname[oncheckout]">
                                                <option value="order" <?php echo isset($options["woovismaoptname"]["oncheckout"]) && $options["woovismaoptname"]["oncheckout"]=="order" ? "selected":""; ?> > Create Order</option>
                                                <option value="invoice" <?php echo isset($options["woovismaoptname"]["oncheckout"]) && $options["woovismaoptname"]["oncheckout"]=="invoice"?"selected":""; ?> >Create Invoice</option>
                                       </select></td>
                                   <td></td>
                        </tr>
                        <tr>
                                <td><b>Terms of payment</b></td><td>:</td><td>
                                    <select name="woovismaoptname[paymentterms]">
						<option selected="selected">- None -</option>			
                                        <?php

                                        foreach($result as $key => $nameVal)
                                        {
                                        ?>
                                            <option value="<?php echo $nameVal['Id']; ?>" <?php if(isset($options["woovismaoptname"]["paymentterms"]) && $nameVal['Id']==$options["woovismaoptname"]["paymentterms"])echo ' selected="selected"';?>><?php echo $nameVal['NameEnglish']; ?></option>
                                        <?php
                                        }
										
                                        ?>
										
                                       </select>
                                </td>
                                <td></td>
                        </tr>
                      
                        
                        <tr>
                                 <td><b>Initiate order sync</b></td><td>:</td><td>
                                        <select name="woovismaoptname[initiateordersync][selected][type]" onchange="changeOrderSync(this);">
                                                <option value="event" <?php echo isset($arrInitiateOrderSync["selected"]["type"]) && $arrInitiateOrderSync["selected"]["type"]=="event"?"selected":""; ?> > Based on an Event</option>
                                                <option value="status" <?php echo isset($arrInitiateOrderSync["selected"]["type"]) && $arrInitiateOrderSync["selected"]["type"]=="status"?"selected":""; ?> >Based on Order status</option>
                                       </select>
                                 </td><td></td>
                        </tr>
                        <tr>
                                 <td></td><td>:</td><td>
                                 <?php
                                 
                                 ob_start();
                                 foreach($arrInitiateOrderSync["type"] as $type=>$arrTypeData)
                                 {
                                     if($type=="event")
                                     {
                                         echo "<table id='initiateordersyncevent'>";
                                        foreach($arrTypeData as $ind=>$arrData)
                                        {
                                            /*if($arrInitiateOrderSync["selected"]["type"]=="event" && isset( $arrInitiateOrderSync["selected"]["data"][$ind]))
                                           {*/
                                                   echo " <tr><td><input type='radio' name='woovismaoptname[initiateordersync][type][event][{$ind}]' checked value='1'></td><td> {$arrData["caption"]}</td></tr>";
                                           /*}
                                           else
                                           {
                                                   echo " <tr><td><input type='radio' name='woovismaoptname[initiateordersync][type][event][{$ind}]' value='1'></td><td> {$arrData["caption"]}</td></tr>";
                                           }*/
                                        }
                                        echo "</table>";
                                     }
                                     else if($type=="status")
                                     {
                                         echo "<table id='initiateordersyncstatus'>";
                                        foreach($arrTypeData as $ind=>$arrData)
                                        {
                                            if($arrInitiateOrderSync["selected"]["type"]=="status" && isset( $arrInitiateOrderSync["selected"]["data"][$ind]))
                                           {
                                                   echo " <tr><td><input type='checkbox' name='woovismaoptname[initiateordersync][type][status][{$ind}]' checked value='1'></td><td> {$arrData["caption"]}</td></tr>";
                                           }
                                           else
                                           {
                                                   echo " <tr><td><input type='checkbox' name='woovismaoptname[initiateordersync][type][status][{$ind}]' value='1'></td><td> {$arrData["caption"]}</td></tr>";
                                           }
                                        }
                                        echo "</table>";
                                     }
                                    
                                 }
        echo ob_get_clean();//trace($tmp);
                                 ?>
                                 </td><td></td>
                        </tr>
                        <tr><td colspan="4"><br /><h3><strong>Default Tax Setting</strong></h3></td></tr>
                        <tr>
                                <td class="generaltabcol1"><b>Product Tax</b></td><td class="generaltabcol2">:</td><td class="generaltabcol3"><?php 
                                $obj=Woovisma_ArticleCode::getInstance();
                                $default=$obj->getSiteWideArticleCode();
                                echo $obj->getProductGroupDropdown($default);
                                ?></td><td class="generaltabcol4">(Select default tax settings to be used for new products. Setting can be changed if needed on the right side top block)</td>
                        </tr>
        <tr><td></td><td></td><td colspan="2"><input type="submit" name="settings_submit" value="Save"
                                      class="button-primary" id="settings_save_button" /></td></tr></table>
                </form>
