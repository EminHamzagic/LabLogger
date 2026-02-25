<div class="wrap">
    <h1>How to Use Blockchain Lab Logger</h1>
    
    <div class="bll-help-container">
        <div class="bll-help-section">
            <h2>🎯 Overview</h2>
            <p>
                The Blockchain Lab Logger is a WordPress plugin that allows students to log physics and science 
                experiment data with blockchain verification. Each experiment is stored both in your WordPress 
                database and on the Solana blockchain, ensuring data integrity and providing an immutable record.
            </p>
        </div>
        
        <div class="bll-help-section">
            <h2>📝 For Students: Logging an Experiment</h2>
            <ol>
                <li>Navigate to a page with the lab logger form (look for "[lab_logger]" shortcode)</li>
                <li>Fill in the experiment details:
                    <ul>
                        <li><strong>Experiment Name:</strong> Give your experiment a descriptive name</li>
                        <li><strong>Student Name:</strong> (Optional) Your name</li>
                        <li><strong>Measurements:</strong> Add as many measurements as needed (e.g., "Time: 5.2 seconds")</li>
                        <li><strong>Observations:</strong> Note any observations or anomalies</li>
                    </ul>
                </li>
                <li>Click "Log Experiment to Blockchain"</li>
                <li>Wait for confirmation (usually 5-10 seconds)</li>
                <li>You'll receive:
                    <ul>
                        <li>A unique Experiment ID</li>
                        <li>Blockchain verification status</li>
                        <li>A transaction signature</li>
                        <li>Link to view on Solana Explorer</li>
                    </ul>
                </li>
            </ol>
        </div>
        
        <div class="bll-help-section">
            <h2>🔍 Verifying Data Integrity</h2>
            <p>To verify that experiment data hasn't been tampered with:</p>
            <ol>
                <li>Click the "Verify" button next to any experiment</li>
                <li>The system will:
                    <ul>
                        <li>Recalculate the data hash from the current database record</li>
                        <li>Compare it to the original hash</li>
                        <li>Verify the blockchain transaction</li>
                        <li>Compare the blockchain hash to the database hash</li>
                    </ul>
                </li>
                <li>You'll see one of two results:
                    <ul>
                        <li><strong>✅ INTACT:</strong> Data matches the blockchain record perfectly</li>
                        <li><strong>❌ COMPROMISED:</strong> Data has been altered since original logging</li>
                    </ul>
                </li>
            </ol>
        </div>
        
        <div class="bll-help-section">
            <h2>🎓 For Teachers: Using the Plugin</h2>
            
            <h3>Setup</h3>
            <ol>
                <li>Install and activate the plugin</li>
                <li>Configure your <code>includes/config.php</code> file with your SECRET_KEY</li>                <li>Go to Settings and Test the connection</li>
            </ol>
            
            <h3>Adding the Form to Pages</h3>
            <p>Use the shortcode <code>[lab_logger]</code> on any page or post:</p>
            <pre><code>[lab_logger]</code></pre>
            
            <p>With custom options:</p>
            <pre><code>[lab_logger title="Physics Lab 101" show_history="yes"]</code></pre>
            
            <h3>Managing Experiments</h3>
            <ul>
                <li>View all experiments in the admin area</li>
                <li>Click "View Details" to see full verification info</li>
                <li>Click "Explorer →" to view the blockchain transaction</li>
                <li>Export data for analysis (future feature)</li>
            </ul>
        </div>
        
        <div class="bll-help-section">
            <h2>🔬 Understanding the Technology</h2>
            
            <h3>How It Works</h3>
            <ol>
                <li><strong>Data Entry:</strong> Student enters experiment data in the form</li>
                <li><strong>Hash Calculation:</strong> System creates a SHA-256 hash of the data</li>
                <li><strong>Blockchain Logging:</strong> Hash is sent to Solana blockchain via the backend</li>
                <li><strong>Transaction Confirmation:</strong> Solana confirms and returns a transaction signature</li>
                <li><strong>Database Storage:</strong> WordPress saves the data and transaction signature</li>
            </ol>
            
            <h3>Why Blockchain?</h3>
            <ul>
                <li><strong>Immutability:</strong> Once written to blockchain, data cannot be changed</li>
                <li><strong>Timestamp:</strong> Proves when data was recorded</li>
                <li><strong>Transparency:</strong> Anyone can verify the data on a public blockchain explorer</li>
                <li><strong>Data Integrity:</strong> Even if WordPress database is compromised, blockchain record remains</li>
            </ul>
            
            <h3>Technical Components</h3>
            <ul>
                <li><strong>WordPress:</strong> User interface and database storage</li>
                <li><strong>Node.js Backend:</strong> Handles Solana transactions</li>
                <li><strong>Solana Blockchain:</strong> Immutable data storage</li>
                <li><strong>Helius RPC:</strong> Connection to Solana network</li>
            </ul>
        </div>
        
        <div class="bll-help-section">
            <h2>💡 Best Practices</h2>
            
            <h3>For Students</h3>
            <ul>
                <li>Be as detailed as possible in your measurements</li>
                <li>Include units for all measurements</li>
                <li>Note any experimental conditions (temperature, pressure, etc.)</li>
                <li>Record observations immediately after the experiment</li>
                <li>Save your Experiment ID for future reference</li>
            </ul>
            
            <h3>For Teachers/Administrators</h3>
            <ul>
                <li>Use Devnet for testing and student projects</li>
                <li>Only use Mainnet for official records requiring permanence</li>
                <li>Regularly backup your WordPress database</li>
                <li>Keep the backend server running and monitored</li>
                <li>Periodically verify old experiments to demonstrate integrity</li>
            </ul>
        </div>
        
        <div class="bll-help-section">
            <h2>❓ Frequently Asked Questions</h2>
            
            <h3>Q: What happens if the blockchain logging fails?</h3>
            <p>
                The experiment is still saved to the WordPress database but flagged as "Local Only". 
                You can manually retry blockchain logging later, but the timestamp will be from the retry time.
            </p>
            
            <h3>Q: Can I delete an experiment from the blockchain?</h3>
            <p>
                No. Blockchain data is permanent and immutable. This is by design - it ensures data integrity. 
                You can delete records from WordPress, but the blockchain record remains forever.
            </p>
            
            <h3>Q: How much does it cost?</h3>
            <p>
                <strong>Devnet:</strong> Free! Use as much as you want for testing and student projects.<br>
                <strong>Mainnet:</strong> ~0.000005 SOL per transaction (~$0.0005 at $100/SOL). Very affordable!
            </p>
            
            <h3>Q: Is my data private?</h3>
            <p>
                Only the hash (fingerprint) of your data is stored on the blockchain, not the actual data. 
                The actual experiment details are stored in your WordPress database. However, anyone with 
                access to both can verify data integrity.
            </p>
            
            <h3>Q: What if I lose my wallet?</h3>
            <p>
                Existing blockchain records remain intact and verifiable forever. However, you won't be 
                able to log new experiments. Always backup your wallet.json file securely.
            </p>
            
            <h3>Q: Can I use this for production/official records?</h3>
            <p>
                Yes! Switch to Mainnet Beta and fund your wallet with real SOL. The same plugin works 
                for both development and production.
            </p>
        </div>
        
        <div class="bll-help-section bll-troubleshooting">
            <h2>🔧 Troubleshooting</h2>
            
            <h3>Backend Connection Failed</h3>
            <ul>
                <li>Check that the backend server is running (<code>npm start</code>)</li>
                <li>Verify the Backend URL in Settings</li>
                <li>Check backend console for errors</li>
                <li>Ensure firewall allows connections</li>
            </ul>
            
            <h3>Transaction Failed</h3>
            <ul>
                <li>Check wallet balance (need at least 0.01 SOL)</li>
                <li>Request airdrop from <a href="https://faucet.solana.com" target="_blank">Solana Faucet</a></li>
                <li>Verify Helius API key is valid</li>
                <li>Check Solana network status</li>
            </ul>
            
            <h3>Form Not Showing</h3>
            <ul>
                <li>Ensure shortcode is correct: <code>[lab_logger]</code></li>
                <li>Check that plugin is activated</li>
                <li>Clear WordPress cache</li>
                <li>Check browser console for JavaScript errors</li>
            </ul>
        </div>
        
        <div class="bll-help-section bll-support">
            <h2>📞 Support & Resources</h2>
            <ul>
                <li><strong>Solana Documentation:</strong> <a href="https://docs.solana.com" target="_blank">docs.solana.com</a></li>
                <li><strong>Helius Documentation:</strong> <a href="https://docs.helius.dev" target="_blank">docs.helius.dev</a></li>
                <li><strong>Solana Explorer:</strong> <a href="https://explorer.solana.com" target="_blank">explorer.solana.com</a></li>
                <li><strong>Devnet Faucet:</strong> <a href="https://faucet.solana.com" target="_blank">faucet.solana.com</a></li>
            </ul>
        </div>
    </div>
</div>

<style>
.bll-help-container {
    max-width: 900px;
}

.bll-help-section {
    background: white;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 25px;
    margin-bottom: 20px;
}

.bll-help-section h2 {
    margin-top: 0;
    color: #2271b1;
    border-bottom: 2px solid #f0f0f1;
    padding-bottom: 10px;
}

.bll-help-section h3 {
    color: #1d2327;
    margin-top: 20px;
}

.bll-help-section ul,
.bll-help-section ol {
    margin-left: 25px;
    line-height: 1.8;
}

.bll-help-section pre {
    background: #f6f7f7;
    padding: 15px;
    border-radius: 4px;
    overflow-x: auto;
}

.bll-help-section code {
    background: #f6f7f7;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: monospace;
}

.bll-help-section pre code {
    background: transparent;
    padding: 0;
}

.bll-troubleshooting {
    border-left: 4px solid #f0b849;
}

.bll-support {
    background: #e7f5fe;
    border-left: 4px solid #2271b1;
}
</style>