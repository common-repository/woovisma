<?php $options = get_option( 'woovisma_options' ); ?>
                        <form method="post" action="admin-post.php" name="settings" id="settings">
                        <input type="hidden" name="action" value="save_woovisma_options" />
                        <input type="hidden" name="tab" value="visma" />
                        <!-- Adding security through hidden referrer field -->
                        <!--<h3><strong>Visma Access Settings</strong></h3>-->
                        <table class="generaltabcontent"><tr><td colspan="3" style="text-align: center;"><b><div id="errmsg" style="color:red;"></div></b></td></tr>
                            <tr><td colspan="4"><h3><strong>Visma Access Settings</strong></h3></td></tr>
                                <tr style="display:none;">
                                        <!--<td class="generaltabcol1"><strong>Https/Http</strong></td><td class="generaltabcol2">: </td>
                                        <td class="generaltabcol3"> HTTPS <input onchange="submitSettings()" class="nossl" type="radio" name="nossl" value="0" <?php echo !isset($options['nossl']) || $options['nossl']<1 ? "checked" : ""; ?> /> HTTP <input  onchange="submitSettings()" class="nossl" type="radio" name="nossl" value="1" <?php echo isset($options['nossl']) && $options['nossl']>0 ? "checked" : ""; ?> /><br /></td>
                                        <td class="generaltabcol4"></td>-->
                                    <td class="generaltabcol1"><strong>Https/Http</strong></td><td class="generaltabcol2">: </td>
                                        <td class="generaltabcol3"> HTTPS <input onchange="submitSettings()" class="nossl" type="radio" name="nossl" value="0" <?php echo !isset($options['nossl']) || $options['nossl']<1 ? "checked" : ""; ?> /> HTTP <input  onchange="submitSettings()" class="nossl" type="radio" name="nossl" value="1" <?php echo isset($options['nossl']) && $options['nossl']>0 ? "checked" : ""; ?> /><br /></td>
                                        <td class="generaltabcol4"></td>
                                </tr>
                                <tr><td class="generaltabcol1"><?php wp_nonce_field( 'woovisma' ); ?>
                                                <strong>Client ID</strong></td><td class="generaltabcol2">: </td><td class="generaltabcol3"><input onchange="showHideTestButton();" type="text" name="client_id" id="client_id"
                        value="<?php echo esc_html( $options['client_id'] );
                        ?>"/></td>
                                <td class="generaltabcol4"></td>
                                </tr>
                                <tr><td class="tblcol1"><strong>Client Secret</strong></td><td class="tblcol2">: </td><td class="tblcol3"><input onchange="showHideTestButton();" type="text" name="client_secret" id="client_secret"
                        value="<?php echo esc_html( $options['client_secret'] );
                        ?>"/><br /></td>
                                <td></td>
                                </tr>
                                <tr><td class="tblcol1"><strong>Registered URI</strong></td><td class="tblcol2">: </td><td class="tblcol3"><input onchange="showHideTestButton();" type="text" name="redirect_uri" id="redirect_uri"
                        value="<?php echo esc_html( $options['redirect_uri'] );
                        ?>"/><br /></td>
                                <td>HTTPS redirect URL is needed to connect Visma</td>
                                </tr>
                                <tr><td></td><td></td><td colspan="2"><input type="submit" name="submit" value="Save"
                                              class="button-primary" id="save_button" /><input type="submit" name="submit" value="Authenticate" id="authenticate_button"
                                              class="button-primary" /></td>
                                <td></td>
                                </tr></table>
                        </form><br />
                                              <form method="post" action="admin-post.php" name="settings" id="settings">
                <input type="hidden" name="action" value="save_woovisma_settings" />
                <input type="hidden" name="tab" value="visma" />
                <?php wp_nonce_field( 'woovisma' ); ?>
                <!--<h3><strong>License key</strong></h3>-->
                <table class="generaltabcontent">
                    <tr><td colspan="4"><h3><strong>License key</strong></h3></td></tr>
                            <tr>
                                <td class="generaltabcol1"><b>License key</b></td><td class="generaltabcol2">:</td><td class="generaltabcol3"><input type="text" id="license_key" name="license_key" value="<?php echo isset($options["license_key"])?$options["license_key"]:""; ?>" /></td>
                                <td class="generaltabcol4">(This is the License key you received from us by mail.)</td>
                        </tr>
                        <tr>
                                <td></td><td></td><td><input type="submit" name="settings_submit" value="Save"
                                      class="button-primary" id="settings_save_button" />
                                </td><td></td>
                        </tr>
                                              </table>
                        </form><br />
<form method="post" action="admin-post.php" name="settings" id="settings">
                        <input type="hidden" name="action" value="save_woovisma_options" />
                        <input type="hidden" name="tab" value="visma" />
                        <!-- Adding security through hidden referrer field -->
                        <!--<br /><h3><strong>Test Connection</strong></h3>-->
                        <table class="generaltabcontent">
                            <tr><td colspan="4"><h3><strong>Test Connection</strong></h3></td></tr>
                                <tr><td class="generaltabcol1"><b>Test Connection</b></td><td class="generaltabcol2">:</td><td class="generaltabcol3"><input type="submit" name="submit" value="Test" id="test_button"
                                              class="button-primary" /></td>
                                <td class="generaltabcol4"></td>
                                </tr></table>
                        </form><br />