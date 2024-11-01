<div id="ch2pho-general" class="wrap" style="float:left;margin-left:20px;">
        <div>
                        <center><h2><strong>Visma Access Settings</strong></h2></center>
                        <div class="login">
                        <form method="post" action="admin-post.php" name="settings" id="settings">
                        <input type="hidden" name="action" value="save_woovisma_options" />
                        <!-- Adding security through hidden referrer field -->
                        <table><tr><td colspan="3" style="text-align: center;"><b><div id="errmsg" style="color:red;"></div></b></td></tr>
                                <tr>
                                        <td><strong>Https/Http</strong></td><td>: </td>
                                        <td> HTTPS <input onchange="submitSettings()" class="nossl" type="radio" name="nossl" value="0" <?php echo !isset($options['nossl']) || $options['nossl']<1 ? "checked" : ""; ?> /> HTTP <input  onchange="submitSettings()" class="nossl" type="radio" name="nossl" value="1" <?php echo isset($options['nossl']) && $options['nossl']>0 ? "checked" : ""; ?> /><br /></td>
                                </tr>
                                <tr><td><?php wp_nonce_field( 'woovisma' ); ?>
                                                <strong>Client ID</strong></td><td>: </td><td><input onchange="showHideTestButton();" type="text" name="client_id" id="client_id"
                        value="<?php echo esc_html( $options['client_id'] );
                        ?>"/></td></tr>
                                <tr><td><strong>Client Secret</strong></td><td>: </td><td><input onchange="showHideTestButton();" type="text" name="client_secret" id="client_secret"
                        value="<?php echo esc_html( $options['client_secret'] );
                        ?>"/><br /></td></tr>
                                <tr><td><strong>Registered URI</strong></td><td>: </td><td><input onchange="showHideTestButton();" type="text" name="redirect_uri" id="redirect_uri"
                        value="<?php echo esc_html( $options['redirect_uri'] );
                        ?>"/><br /></td></tr>
                                <tr><td colspan="3" style="text-align: center;"><input type="submit" name="submit" value="Save"
                                              class="button-primary" id="save_button" /><input type="submit" name="submit" value="Test" id="test_button"
                                              class="button-primary" /></td></tr></table>
                        </form>
                                 </div>
        </div>
        <div>
                <center><h2><strong>Sitewide Settings</strong></h2></center>
                <?php 
                if ( isset( $_GET['message'] ) && $_GET['message'] == '2' )
                {
                    echo "<div id='message' class='updated fade'><p><strong>Sitewide Settings Saved</strong></p></div>";
                }
                ?>
                <div class="login">
                <form method="post" action="admin-post.php" name="settings" id="settings">
                <input type="hidden" name="action" value="save_woovisma_settings" />
                <?php wp_nonce_field( 'woovisma' ); ?>
                <table><tr><td colspan="3" style="text-align: center;"><b><div id="errmsg" style="color:red;"></div></b></td></tr>
                        <tr>
                                <td><b>Product Code</b> <br />(The values available after the products synced from visma)</td><td>:</td><td><?php 
                                $obj=Woovisma_ArticleCode::getInstance();
                                $default=$obj->getSiteWideArticleCode();
                                echo $obj->getProductGroupDropdown($default);
                                ?></td>
                        </tr>
        <tr><td colspan="3" style="text-align: center;"><input type="submit" name="settings_submit" value="Save"
                                      class="button-primary" id="settings_save_button" /></td></tr></table>
                </form>
                </div>
        </div>
</div>
<script>
            showHideTestButton();
            </script>
<hr />
<div class="mailsupport"  style="float:right;margin-right:20px;">
                            		<h2><?php echo 'Support'; ?></h2>
                            	    <form method="post" id="support">
                            	        <input type="hidden" value="send_support_mail" name="action">
                            	        <table class="form-table">
								
                            	            <tbody>
                            	            <tr valign="top">
                            	                <td>
                            	                    <input type="text" value="" placeholder="<?php echo 'Company'; ?>" name="company">
                            	                </td>
                            	            </tr>
                            	            <tr valign="top">
                            	                <td>
                            	                    <input type="text" value="" placeholder="<?php echo 'Name'; ?>" name="name">
                            	                </td>
                            	            </tr>
                            	            <tr valign="top">
                            	                <td>
                            	                    <input type="text" value="" placeholder="<?php echo 'Phone'; ?>" name="telephone">
                            	                </td>
                            	            </tr>
                            	            <tr valign="top">
                            	                <td>
                            	                    <input type="text" value="" placeholder="<?php echo 'Email'; ?>" name="email">
                            	                </td>
                            	            </tr> 
                            	            <tr valign="top">
                            	                <td>
                            	                    <textarea placeholder="<?php echo 'Subject'; ?>" name="subject"></textarea>
                            	                </td>
                            	            </tr>
                            	            <tr valign="top">
                            	                <td>
                                                	<input type="hidden" name="supportForm" value="support" />
                            	                    <button type="button" class="button button-primary" title="send_support_mail" style="margin:5px" onclick="send_support_mail('support')"><?php echo 'Send'; ?></button>
                            	                </td>
                            	            </tr>
                            	            </tbody>
                            	        </table>
                            	        <!-- p class="submit">
                            	           <button type="button" class="button button-primary" title="send_support_mail" style="margin:5px" onclick="send_support_mail()">Skicka</button> 
                            	        </p -->
                            	    </form>
                            	</div>