<div class="wrap">
    <h1>All Experiments</h1>
    
    <div class="bll-admin-stats">
        <div class="bll-stat-card">
            <div class="bll-stat-number"><?php echo count($experiments); ?></div>
            <div class="bll-stat-label">Total Experiments</div>
        </div>
        <div class="bll-stat-card">
            <div class="bll-stat-number">
                <?php echo count(array_filter($experiments, function($e) { return $e['blockchain_verified']; })); ?>
            </div>
            <div class="bll-stat-label">Blockchain Verified</div>
        </div>
        <div class="bll-stat-card">
            <div class="bll-stat-number">
                <?php echo count(array_filter($experiments, function($e) { return !$e['blockchain_verified']; })); ?>
            </div>
            <div class="bll-stat-label">Local Only</div>
        </div>
    </div>
    
    <?php if (empty($experiments)): ?>
        <div class="notice notice-info">
            <p>No experiments have been logged yet. Add the shortcode <code>[lab_logger]</code> to any page to start logging experiments.</p>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Experiment ID</th>
                    <th>Experiment Name</th>
                    <th>Student</th>
                    <th>Date</th>
                    <th>Blockchain Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($experiments as $exp): ?>
                <tr>
                    <td><?php echo esc_html($exp['id']); ?></td>
                    <td><code><?php echo esc_html($exp['experiment_id']); ?></code></td>
                    <td><strong><?php echo esc_html($exp['experiment_name']); ?></strong></td>
                    <td><?php echo esc_html($exp['student_name'] ?: '-'); ?></td>
                    <td><?php echo esc_html(date('Y-m-d H:i:s', strtotime($exp['created_at']))); ?></td>
                    <td>
                        <?php if ($exp['blockchain_verified']): ?>
                            <span class="bll-badge bll-badge-success">✓ Verified</span>
                            <br><small><code><?php echo esc_html(substr($exp['blockchain_signature'], 0, 20)); ?>...</code></small>
                        <?php else: ?>
                            <span class="bll-badge bll-badge-warning">Local Only</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="button bll-view-details" data-id="<?php echo esc_attr($exp['id']); ?>">
                            View Details
                        </button>
                        <?php if ($exp['blockchain_verified']): ?>
                            <a href="https://explorer.solana.com/tx/<?php echo esc_attr($exp['blockchain_signature']); ?>?cluster=<?php echo esc_attr(get_option('bll_network', 'devnet')); ?>" 
                               target="_blank" 
                               class="button">
                                Explorer →
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Details Modal -->
<div id="bll-details-modal" class="bll-modal" style="display:none;">
    <div class="bll-modal-content">
        <span class="bll-modal-close">&times;</span>
        <h2>Experiment Details</h2>
        <div id="bll-details-content">
            <p>Loading...</p>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // View details
    $('.bll-view-details').on('click', function() {
        const id = $(this).data('id');
        showExperimentDetails(id);
    });
    
    function showExperimentDetails(id) {
        const modal = $('#bll-details-modal');
        const content = $('#bll-details-content');
        
        content.html('<p>Loading experiment details...</p>');
        modal.show();
        
        $.post(ajaxurl, {
            action: 'bll_verify_experiment',
            nonce: bllAdmin.nonce,
            id: id
        }, function(response) {
            if (response.success) {
                const data = response.data;
                let html = '<div class="bll-details">';
                
                // Integrity status
                html += '<div class="bll-integrity-status ' + 
                    (data.integrity === 'intact' ? 'bll-intact' : 'bll-compromised') + '">';
                html += '<h3>' + (data.integrity === 'intact' ? '✅ Data Integrity: INTACT' : '❌ Data Integrity: COMPROMISED') + '</h3>';
                html += '</div>';
                
                // Hash comparison
                html += '<div class="bll-detail-section">';
                html += '<h4>Hash Verification</h4>';
                html += '<p><strong>Stored Hash:</strong> <code>' + data.stored_hash + '</code></p>';
                html += '<p><strong>Calculated Hash:</strong> <code>' + data.calculated_hash + '</code></p>';
                html += '<p><strong>Match:</strong> ' + (data.hash_matches ? '✅ Yes' : '❌ No') + '</p>';
                html += '</div>';
                
                // Blockchain verification
                if (data.blockchain_verified) {
                    html += '<div class="bll-detail-section">';
                    html += '<h4>Blockchain Verification</h4>';
                    html += '<p><strong>Status:</strong> ✅ Verified on blockchain</p>';
                    html += '<p><strong>Transaction:</strong> <code>' + data.blockchain_signature + '</code></p>';
                    
                    if (data.blockchain_data && data.blockchain_data.memo) {
                        html += '<p><strong>Block Time:</strong> ' + new Date(data.blockchain_data.blockTime * 1000).toLocaleString() + '</p>';
                        html += '<p><strong>Blockchain Hash:</strong> <code>' + data.blockchain_data.memo.dataHash + '</code></p>';
                        html += '<p><strong>Hash Match:</strong> ' + (data.blockchain_hash_matches ? '✅ Yes' : '❌ No') + '</p>';
                    }
                    html += '</div>';
                } else {
                    html += '<div class="bll-detail-section">';
                    html += '<h4>Blockchain Verification</h4>';
                    html += '<p><strong>Status:</strong> ⚠️ Not verified on blockchain</p>';
                    html += '</div>';
                }
                
                html += '</div>';
                content.html(html);
            } else {
                content.html('<p class="error">Failed to load experiment details: ' + response.data.message + '</p>');
            }
        });
    }
    
    // Close modal
    $('.bll-modal-close, .bll-modal').on('click', function(e) {
        if (e.target === this) {
            $('#bll-details-modal').hide();
        }
    });
});
</script>

<style>
.bll-admin-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.bll-stat-card {
    background: white;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
}

.bll-stat-number {
    font-size: 36px;
    font-weight: bold;
    color: #2271b1;
}

.bll-stat-label {
    font-size: 14px;
    color: #646970;
    margin-top: 5px;
}

.bll-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
}

.bll-badge-success {
    background: #00a32a;
    color: white;
}

.bll-badge-warning {
    background: #f0b849;
    color: #000;
}

.bll-modal {
    display: none;
    position: fixed;
    z-index: 100000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.bll-modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 30px;
    border-radius: 8px;
    width: 80%;
    max-width: 800px;
    max-height: 80vh;
    overflow-y: auto;
    position: relative;
}

.bll-modal-close {
    position: absolute;
    right: 20px;
    top: 20px;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.bll-modal-close:hover {
    color: #d63638;
}

.bll-integrity-status {
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.bll-intact {
    background: #d5f4e6;
    border-left: 4px solid #00a32a;
}

.bll-compromised {
    background: #fde7e9;
    border-left: 4px solid #d63638;
}

.bll-detail-section {
    background: #f6f7f7;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 15px;
}

.bll-detail-section h4 {
    margin-top: 0;
}

.bll-detail-section code {
    background: white;
    padding: 2px 6px;
    border-radius: 3px;
    word-break: break-all;
}
</style>