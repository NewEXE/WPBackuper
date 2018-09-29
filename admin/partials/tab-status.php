<?php if ( $with_fs_info ): ?>
    <div class="wrap">

        <div id="poststuff">

            <div id="post-body" class="metabox-holder columns-2">

                <!-- main content -->
                <div id="post-body-content">

                    <div class="meta-box-sortables ui-sortable">

                        <div class="postbox">

                            <h2 class="hndle" style="cursor: auto"><mark class="warning"></mark> <span class="dashicons dashicons-warning"></span> <span><?php esc_attr_e( 'Filesystem connection problem', 'wpb' ); ?></span>
                            </h2>

                            <div class="inside">
                                <p><?php _e( '
<p>Unable to get direct access to your file system. <b>Creating backup by cron (on schedule) will not work</b>. You need to store filesystem credentials permanently.</p>
<p>There is a way to store the credentials permanently using the <code>wp-config.php</code> file.</p>

<p>Use these options to store the FTP or SSH credentials:</p>

<ol>
<li><code>FTP_HOST</code>: The host name of the server.</li>
<li><code>FTP_USER</code>: The username to use while connecting.</li>
<li><code>FTP_PASS</code>: The password to use while connecting.</li>
<li><code>FTP_PUBKEY</code>: The path of the public key which will be used while using SSH connection.</li>
<li><code>FTP_PRIKEY</code>: The path of the private key which will be used while using SSH connection.</li>
</ol>

<p>Example:<p>
<pre class="programmlisting">
define( "FTP_HOST", "ftp.example.org" );
define( "FTP_USER", "username" );
define( "FTP_PASS", "password" );
define( "FTP_PUBKEY", "/home/username/.ssh/id_rsa.pub" );
define( "FTP_PRIKEY", "/home/username/.ssh/id_rsa" );
</pre>
', 'wpb' ); ?></p>
                            </div>
                            <!-- .inside -->

                        </div>
                        <!-- .postbox -->

                    </div>
                    <!-- .meta-box-sortables .ui-sortable -->

                </div>
                <!-- post-body-content -->

                <!-- sidebar -->
                <div id="postbox-container-1" class="postbox-container">

                    <div class="meta-box-sortables">

                        <div class="postbox">

                            <h2 class="hndle" style="cursor: auto"><span><?php esc_attr_e(
										'Addition information links', 'wpb'
									); ?></span></h2>

                            <div class="inside">
                                <p><?php _e( '
<a href="https://codex.wordpress.org/Editing_wp-config.php#WordPress_Upgrade_Constants">Editing wp-config.php on Codex</a><br />
<a href="https://roots.io/bedrock/docs/configuration-files/">Configuration Files (Bedrock)</a><br />
<a href="https://codex.wordpress.org/Filesystem_API">Filesystem API docs</a>
', 'wpb' ); ?></p>
                            </div>
                            <!-- .inside -->

                        </div>
                        <!-- .postbox -->

                    </div>
                    <!-- .meta-box-sortables -->

                </div>
                <!-- #postbox-container-1 .postbox-container -->

            </div>
            <!-- #post-body .metabox-holder .columns-2 -->

        </div>
        <!-- #poststuff -->

    </div> <!-- .wrap -->
<?php endif; ?>
<br class="clear /">

<table class="widefat">
	<thead>
	<tr>
		<th class="row-title"><?php esc_html_e( 'Name', 'wpb' ); ?></th>
		<th><?php esc_html_e( 'Status', 'wpb' ); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php $i = 0; ?>
    <?php foreach ($items as $item): ?>
        <tr <?php echo $i % 2 ? 'class="alternate"': '' ?>>
            <td class="row-title"><?php echo $item['name'] ?> <p class="description" style="font-weight: 400;"><?php echo $item['hint'] ?></p></td>
            <td><?php echo $item['true'] ?
		            '<mark class="yes"></mark> <span class="dashicons dashicons-yes"></span> ' . $item['description_true'] :
		            '<mark class="warning"></mark> <span class="dashicons dashicons-warning"></span> ' . $item['description_false'] ?></td>
        </tr>
        <?php $i++; ?>
    <?php endforeach; ?>
	</tbody>
	<tfoot>
	<tr>
		<th class="row-title"><?php esc_html_e( 'Name', 'wpb' ); ?></th>
		<th><?php esc_html_e( 'Status', 'wpb' ); ?></th>
	</tr>
	</tfoot>
</table>