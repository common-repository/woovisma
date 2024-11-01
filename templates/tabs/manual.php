<?php $options = get_option( 'woovisma_options' ); 

                        if ( isset( $_GET['product_sync_status'] ) && $_GET['product_sync_status'] == '1' )
                        {
                            echo "<div id='message' class='updated fade'><p><strong>Product Sync Completed</strong></p></div>";
                        }
                        else if ( isset( $_GET['product_sync_status'] ) && $_GET['product_sync_status'] == '2' )
                        {
                            echo "<div id='message' class='updated fade'><p><strong>Customer Sync Completed</strong></p></div>";
                        }
                        else if ( isset( $_GET['product_sync_status'] ) && $_GET['product_sync_status'] == '3' )
                        {
                            echo "<div id='message' class='updated fade'><p><strong>Order Sync Completed</strong></p></div>";
                        }
                        else if ( isset( $_GET['product_sync_status'] ) && $_GET['product_sync_status'] == '-3' )
                        {
                            echo "<div id='message' class='updated fade'><p><strong>No Orders to Sync</strong></p></div>";
                        }
                        else if ( isset( $_GET['product_sync_status'] ) && $_GET['product_sync_status'] == '99' )
                        {
                            echo "<div id='message' class='updated fade'><p><strong>There Is No Products To Sync</strong></p></div>";
                        }
                        else if ( isset( $_GET['product_sync_status'] ) && $_GET['product_sync_status'] == '98' )
                        {
                            echo "<div id='message' class='updated fade'><p><strong>There Is No Customers To Sync</strong></p></div>";
                        }
                        else if ( isset( $_GET['product_sync_status'] ) && $_GET['product_sync_status'] == '97' )
                        {
                            echo "<div id='message' class='updated fade'><p><strong>There Is No Orders To Sync</strong></p></div>";
                        }
                        else if ( isset( $_GET['product_sync_status'] ) && $_GET['product_sync_status'] == '1001' )
                        {
                            echo "<div id='message' class='updated fade'><p><strong>License Invalid</strong></p></div>";
                        }
        ?>
    <form method="post" action="admin-post.php">
        <input type="hidden" name="action" value="save_woovisma_sync" />
        <!-- Adding security through hidden referrer field -->
        <?php wp_nonce_field( 'woovisma' ); ?>
        <input type="hidden" name="client_id"
    value="<?php echo esc_html( $options['client_id'] );
    ?>"/><input type="hidden" name="client_secret"
    value="<?php echo esc_html( $options['client_secret'] );
    ?>"/>
            <input type="hidden" name="redirect_uri"
    value="<?php echo esc_html( $options['redirect_uri'] );
    ?>"/>
           <table class="manualtabcontent"><caption><center><h3><strong>Manual Data Sync</strong></h3></center></caption>
               <tr>
                   <td> 
                       <input type="submit" name="submit" value="Product Sync WooCommerce -> Visma"
                                  class="button-primary" /> 
                   </td>
                   <td>

                   </td>

                   <td>Send all products to your visma eAccounting. If you have many products, it may take a while.
                   </td>
               </tr>
               <tr>
                    <td> 
                        <input type="submit" name="submit" value="Customer Sync WooCommerce -> Visma"
                          class="button-primary" />
                    </td>
                    <td>

                   </td>
                   <td>Send all customers to your visma eAccounting. If you have many customers, it may take a while.
                   </td>
               </tr>
               <tr>
                    <td> 
                        <input type="submit" name="submit" value="Order Sync WooCommerce -> Visma"
                          class="button-primary" />
                    </td>
                    <td>

                   </td>
                   <td>Send all orders to your visma eAccounting. If you have many orders, it may take a while.
                   </td>
               </tr>
                <tr>
                    <td> 
                        <input type="submit" name="submit" value="Product Sync Visma -> WooCommerce"
                          class="button-primary" />
                    </td>
                    <td>

                   </td>
                   <td>Get all products from your visma eAccounting. If you have many products, it may take a while.
                   </td>
               </tr>
               <tr>
                    <td> 
                        <input type="submit" name="submit" value="Customer Sync Visma -> WooCommerce"
                          class="button-primary" />
                    </td>
                    <td>

                   </td>
                   <td>Get all customers from your visma eAccounting. If you have many customers, it may take a while.
                   </td>
               </tr>
               <tr>
                    <td> 
                        <input type="submit" name="submit" value="Order Sync Visma -> WooCommerce"
                          class="button-primary" />
                    </td>
                    <td>

                   </td>
                   <td>Get all orders from your visma eAccounting. If you have many orders, it may take a while.
                   </td>
               </tr>
           </table>
    </form>
