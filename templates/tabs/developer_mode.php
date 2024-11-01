                        <?php 
                if ( isset( $_GET['message'] ) && $_GET['message'] == '2' )
                {
                    echo "<div id='message' class='updated fade'><p><strong>Sitewide Settings Saved</strong></p></div>";
                }
                else if(isset( $_GET['message'] ) && !empty($_GET['message']))
                {
                    echo "<div id='message' class='updated fade'><p><strong>{$_GET['message']}</strong></p></div>";
                }
                $options = get_option( 'woovisma_options' );
  
                      
?>
<a href="admin-post.php?action=save_woovisma_settings&tab=developer_mode&<?php echo WOOVISMA_PLUGIN_DIRECTORY."-module=setting&".WOOVISMA_PLUGIN_DIRECTORY."-action=clear_log"; ?>">Clear Log</a>
                <form method="post" action="admin-post.php" name="settings" id="settings">
                <input type="hidden" name="action" value="save_woovisma_settings" />
                <input type="hidden" name="tab" value="developer_mode" />
                <?php wp_nonce_field( 'woovisma' ); ?>
                <table class="generaltabcontent"><tr><td colspan="4" style="text-align: center;"><b><div id="errmsg" style="color:red;"></div></b></td></tr>
                        <tr><td colspan="4"><h3><strong>Log Settings</strong></h3></td></tr>
                        <tr>
                                <td class="generaltabcol1"><b>Log</b></td><td class="generaltabcol2">:</td><td class="generaltabcol3">
                                        <select name="woovismaoptname[log]">
                                            <option value="Disabled" <?php echo isset($options["woovismaoptname"]["log"]) && $options["woovismaoptname"]["log"]=="Disabled" ? "selected":""; ?> > Disabled</option>
                                            <option value="Enabled" <?php echo isset($options["woovismaoptname"]["log"]) && $options["woovismaoptname"]["log"]=="Enabled"?"selected":""; ?> >Enabled</option>
                                       
                                        </select>
                                    
                                </td> <td class="generaltabcol4"></td>
                        </tr>
                        <tr>
                                <td class="generaltabcol1"><b>Developer Mode</b></td><td class="generaltabcol2">:</td><td class="generaltabcol3">
                                        <select name="woovismaoptname[developermode]">
                                            <option value="Disabled" <?php echo isset($options["woovismaoptname"]["developermode"]) && $options["woovismaoptname"]["developermode"]=="Disabled" ? "selected":""; ?> > Disabled</option>
                                            <option value="Enabled" <?php echo isset($options["woovismaoptname"]["developermode"]) && $options["woovismaoptname"]["developermode"]=="Enabled"?"selected":""; ?> >Enabled</option>
                                       
                                        </select>
                                      
                                </td> <td class="generaltabcol4"></td>
                        </tr>
                        <tr><td></td><td></td><td colspan="2"><input type="submit" name="settings_submit" value="Save"
                                      class="button-primary" id="settings_save_button" /></td></tr></table>
                </form>
