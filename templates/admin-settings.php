<div class="wrap">
    <h1>Blockchain Lab Logger - Settings</h1>
    
    <div class="bll-settings-container">
        <form method="post" action="">
            <?php wp_nonce_field('bll_settings_save'); ?>
            
            <table class="form-table">
                
                <tr>
                    <th scope="row">
                        <label for="bll_network">Solana Network</label>
                    </th>
                    <td>
                        <select id="bll_network" name="bll_network">
                            <option value="devnet" <?php selected($network, 'devnet'); ?>>Devnet (Development)</option>
                            <option value="testnet" <?php selected($network, 'testnet'); ?>>Testnet</option>
                            <option value="mainnet-beta" <?php selected($network, 'mainnet-beta'); ?>>Mainnet Beta (Production)</option>
                        </select>
                        <p class="description">
                            Select the Solana network. Use <strong>Devnet</strong> for development with Helius free tier.
                        </p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" name="bll_settings_submit" class="button button-primary">
                    Save Settings
                </button>
                <button type="button" id="bll-test-connection" class="button">
                    Test Connection
                </button>
            </p>
        </form>
        
        <div id="bll-connection-result" class="bll-connection-result" style="display:none;"></div>
        
        <hr>
        
        <div class="bll-info-box">
            <h2>Setup Instructions</h2>
            
            <h3>WordPress Configuration</h3>
            <ol>
                <li>Configure your <code>includes/config.php</code> file with your SECRET_KEY</li>
                <li>Select the appropriate network (Devnet for development)</li>
                <li>Click "Test Connection" to verify everything is working</li>
                <li>Add the shortcode <code>[lab_logger]</code> to any page to display the form</li>
            </ol>
            
            <h3>3. Using the Logger</h3>
            <p>Students can visit any page with the <code>[lab_logger]</code> shortcode to:</p>
            <ul>
                <li>Enter experiment details and measurements</li>
                <li>Submit data which will be stored in WordPress AND on Solana blockchain</li>
                <li>View their experiment history</li>
                <li>Verify data integrity at any time</li>
            </ul>
            
            <h3>Shortcode Options</h3>
            <ul>
                <li><code>[lab_logger]</code> - Default form with history</li>
                <li><code>[lab_logger title="My Custom Title"]</code> - Custom title</li>
                <li><code>[lab_logger show_history="no"]</code> - Hide experiment history</li>
            </ul>
        </div>
        
        <div class="bll-info-box bll-warning-box">
            <h3>⚠️ Important Notes</h3>
            <ul>
                <li><strong>Devnet is for testing only.</strong> Use Mainnet Beta for production.</li>
                <li><strong>Keep your wallet.json secure.</strong> Never commit it to version control.</li>
                <li><strong>Helius free tier</strong> provides 100 req/sec on Devnet - perfect for development.</li>
                <li><strong>Each transaction costs ~0.000005 SOL</strong> on Mainnet (~$0.0005 at $100/SOL).</li>
                <li><strong>Blockchain data is permanent.</strong> Once logged, it cannot be changed or deleted.</li>
            </ul>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#bll-test-connection').on('click', function() {
        const button = $(this);
        const result = $('#bll-connection-result');
        
        button.prop('disabled', true).text('Testing...');
        result.hide();
        
        $.post(ajaxurl, {
            action: 'bll_test_connection',
            nonce: bllAdmin.nonce
        }, function(response) {
            button.prop('disabled', false).text('Test Connection');
            
            let html = '';
            if (response.success) {
                html = '<div class="notice notice-success"><p><strong>✅ Connection Successful!</strong></p>';
                html += '<ul>';
                html += '<li><strong>Message:</strong> ' + response.data.message + '</li>';
                html += '<li><strong>Wallet:</strong> <code>' + response.data.wallet + '</code></li>';
                html += '<li><strong>Balance:</strong> ' + response.data.balance + ' SOL</li>';
                html += '<li><strong>Network:</strong> ' + response.data.network + '</li>';
                html += '</ul></div>';
                
                if (response.data.balance < 0.1) {
                    html += '<div class="notice notice-warning"><p>';
                    html += '⚠️ <strong>Low wallet balance!</strong> Request more SOL from the ';
                    html += '<a href="https://faucet.solana.com" target="_blank">Solana Devnet Faucet</a>';
                    html += '</p></div>';
                }
            } else {
                html = '<div class="notice notice-error"><p><strong>❌ Connection Failed</strong></p>';
                html += '<p>' + response.data.message + '</p>';
                html += '<p><strong>Troubleshooting:</strong></p><ul>';
                html += '<li>Ensure the backend server is running (<code>npm start</code>)</li>';
                html += '<li>Check that the Backend URL is correct</li>';
                html += '<li>Verify your Helius API key is valid</li>';
                html += '<li>Check the backend console for errors</li>';
                html += '</ul></div>';
            }
            
            result.html(html).show();
        });
    });
});
</script>

<style>
.bll-settings-container {
    max-width: 900px;
}

.bll-info-box {
    background: #f6f7f7;
    border-left: 4px solid #2271b1;
    padding: 20px;
    margin: 20px 0;
}

.bll-warning-box {
    border-left-color: #f0b849;
    background: #fef8ee;
}

.bll-info-box h2,
.bll-info-box h3 {
    margin-top: 0;
}

.bll-info-box ul,
.bll-info-box ol {
    margin-left: 20px;
}

.bll-info-box code {
    background: white;
    padding: 2px 6px;
    border-radius: 3px;
}

.bll-connection-result {
    margin-top: 20px;
}

.bll-connection-result ul {
    margin-left: 20px;
}

.bll-connection-result code {
    background: #f0f0f1;
    padding: 2px 6px;
    border-radius: 3px;
}
</style>