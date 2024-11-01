<table style="width:772px;">
        <tr><td colspan="2">
                <img src="<?php echo WOOVISMA_PLUGIN_URL; ?>img/banner-772x250.png">                        	</td></tr>
<tr class="defaultbottom">
            <td class="col-twothird">
                <iframe src="//player.vimeo.com/video/38627647" width="500" height="281" frameborder="0" webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen=""></iframe>
            </td><td class="mailsupport">
                            	    <form method="post" id="support">
                            	        <input type="hidden" value="send_support_mail" name="action">
                            	        <table class="form-table">
						<caption><center><h3><strong>Support</strong></h3></center></caption>		
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
                                    </form></td>
</tr></table>