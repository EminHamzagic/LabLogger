<?php
/**
 * Experiments Table Template
 * Displays all lab experiments in a comprehensive table
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get all experiments
$logger = new BLL_Lab_Logger();
$all_experiments = $logger->get_all_experiments(1000); // Get all experiments

// Get network for explorer links
$network = get_option('bll_network', 'devnet');

?>

<div class="bll-experiments-table-container">
    <div class="bll-table-header">
        <h2><?php echo esc_html($atts['title']); ?></h2>
        <?php if ($atts['show_stats'] === 'yes'): ?>
        <div class="bll-stats-bar">
            <div class="bll-stat-item">
                <span class="bll-stat-number"><?php echo count($all_experiments); ?></span>
                <span class="bll-stat-label">Total Experiments</span>
            </div>
            <div class="bll-stat-item">
                <span class="bll-stat-number">
                    <?php echo count(array_filter($all_experiments, function($e) { return $e['blockchain_verified']; })); ?>
                </span>
                <span class="bll-stat-label">Blockchain Verified</span>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($atts['show_search'] === 'yes'): ?>
    <div class="bll-table-controls">
        <input type="text" id="bll-table-search" class="bll-search-input" placeholder="Search experiments...">
        <select id="bll-table-filter" class="bll-filter-select">
            <option value="all">All Experiments</option>
            <option value="verified">Blockchain Verified Only</option>
            <option value="unverified">Unverified Only</option>
        </select>
    </div>
    <?php endif; ?>

    <?php if (empty($all_experiments)): ?>
        <div class="bll-no-data">
            <p>No experiments have been logged yet.</p>
        </div>
    <?php else: ?>
        <div class="bll-table-wrapper">
            <table class="bll-experiments-table" id="bll-experiments-table">
                <thead>
                    <tr>
                        <th class="bll-col-id">ID</th>
                        <th class="bll-col-experiment-id">Experiment ID</th>
                        <th class="bll-col-name">Experiment Name</th>
                        <th class="bll-col-student">Student</th>
                        <th class="bll-col-observations">Observations</th>
                        <th class="bll-col-date">Date Created</th>
                        <th class="bll-col-status">Blockchain Status</th>
                        <th class="bll-col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_experiments as $exp): 
                        $exp_data = json_decode($exp['experiment_data'], true);
                        $measurements = isset($exp_data['measurements']) ? $exp_data['measurements'] : array();
                        $observations = isset($exp_data['observations']) ? $exp_data['observations'] : '';
                        
                        // Create associative array of measurements
                        $measurement_values = array();
                        foreach ($measurements as $m) {
                            if (isset($m['label'])) {
                                $key = sanitize_key($m['label']);
                                $value = isset($m['value']) ? $m['value'] : '';
                                $unit = isset($m['unit']) ? $m['unit'] : '';
                                $measurement_values[$key] = $value . ($unit ? ' ' . $unit : '');
                            }
                        }
                        
                        $verified_class = $exp['blockchain_verified'] ? 'verified' : 'unverified';
                    ?>
                    <tr class="bll-table-row <?php echo esc_attr($verified_class); ?>" data-experiment-id="<?php echo esc_attr($exp['id']); ?>">
                        <td class="bll-col-id"><?php echo esc_html($exp['id']); ?></td>
                        
                        <td class="bll-col-experiment-id">
                            <code><?php echo esc_html($exp['experiment_id']); ?></code>
                        </td>
                        
                        <td class="bll-col-name">
                            <strong><?php echo esc_html($exp['experiment_name']); ?></strong>
                        </td>
                        
                        <td class="bll-col-student">
                            <?php echo $exp['student_name'] ? esc_html($exp['student_name']) : '<em>—</em>'; ?>
                        </td>
                        
                        <td class="bll-col-observations">
                            <?php if ($observations): ?>
                                <div class="bll-observations-cell">
                                    <span class="bll-observations-preview">
                                        <?php echo esc_html(wp_trim_words($observations, 10)); ?>
                                    </span>
                                    <?php if (str_word_count($observations) > 10): ?>
                                        <button class="bll-show-more" data-full-text="<?php echo esc_attr($observations); ?>">
                                            Show more
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <em>—</em>
                            <?php endif; ?>
                        </td>
                        
                        <td class="bll-col-date">
                            <?php 
                            $date = new DateTime($exp['created_at']);
                            echo esc_html($date->format('Y-m-d H:i')); 
                            ?>
                        </td>
                        
                        <td class="bll-col-status">
                            <?php if ($exp['blockchain_verified']): ?>
                                <span class="bll-status-badge bll-status-verified">
                                    <span class="bll-status-icon">✓</span>
                                    Verified
                                </span>
                            <?php else: ?>
                                <span class="bll-status-badge bll-status-unverified">
                                    <span class="bll-status-icon">⚠</span>
                                    Local Only
                                </span>
                            <?php endif; ?>
                        </td>
                        
                        <td class="bll-col-actions">
                            <div class="bll-action-buttons">
                                <?php if ($exp['blockchain_verified'] && $exp['blockchain_signature']): ?>
                                    <a href="https://explorer.solana.com/tx/<?php echo esc_attr($exp['blockchain_signature']); ?>?cluster=<?php echo esc_attr($network); ?>" 
                                       target="_blank" 
                                       class="bll-btn bll-btn-blockchain"
                                       title="View on Solana Explorer">
                                        <span class="bll-btn-icon">🔗</span>
                                        Blockchain
                                    </a>
                                <?php endif; ?>
                                
                                <button class="bll-btn bll-btn-verify" 
                                        data-experiment-id="<?php echo esc_attr($exp['id']); ?>"
                                        title="Verify data integrity">
                                    <span class="bll-btn-icon">🔒</span>
                                    Verify
                                </button>
                                
                                <button class="bll-btn bll-btn-details" 
                                        data-experiment-id="<?php echo esc_attr($exp['id']); ?>"
                                        title="View all details">
                                    <span class="bll-btn-icon">👁</span>
                                    Details
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($atts['show_pagination'] === 'yes'): ?>
        <div class="bll-table-pagination">
            <div class="bll-pagination-info">
                Showing <span id="bll-showing-count"><?php echo count($all_experiments); ?></span> of 
                <span id="bll-total-count"><?php echo count($all_experiments); ?></span> experiments
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Details Modal -->
<div id="bll-details-modal" class="bll-modal" style="display:none;">
    <div class="bll-modal-overlay"></div>
    <div class="bll-modal-content">
        <div class="bll-modal-header">
            <h3>Experiment Details</h3>
            <button class="bll-modal-close">&times;</button>
        </div>
        <div class="bll-modal-body" id="bll-modal-details-content">
            <p>Loading...</p>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const table = $('#bll-experiments-table');
    const searchInput = $('#bll-table-search');
    const filterSelect = $('#bll-table-filter');
    
    // Search functionality
    searchInput.on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        table.find('tbody tr').each(function() {
            const row = $(this);
            const text = row.text().toLowerCase();
            
            if (text.indexOf(searchTerm) > -1) {
                row.show();
            } else {
                row.hide();
            }
        });
        
        updateCounts();
    });
    
    // Filter functionality
    filterSelect.on('change', function() {
        const filter = $(this).val();
        
        table.find('tbody tr').each(function() {
            const row = $(this);
            
            if (filter === 'all') {
                row.show();
            } else if (filter === 'verified') {
                if (row.hasClass('verified')) {
                    row.show();
                } else {
                    row.hide();
                }
            } else if (filter === 'unverified') {
                if (row.hasClass('unverified')) {
                    row.show();
                } else {
                    row.hide();
                }
            }
        });
        
        updateCounts();
    });
    
    // Show more observations
    $(document).on('click', '.bll-show-more', function(e) {
        e.preventDefault();
        const button = $(this);
        const fullText = button.data('full-text');
        const preview = button.siblings('.bll-observations-preview');
        
        if (button.text() === 'Show more') {
            preview.text(fullText);
            button.text('Show less');
        } else {
            preview.text(fullText.split(' ').slice(0, 10).join(' ') + '...');
            button.text('Show more');
        }
    });
    
    // View details
    $(document).on('click', '.bll-btn-details', function() {
        const expId = $(this).data('experiment-id');
        showExperimentDetails(expId);
    });
    
    // Verify experiment
    $(document).on('click', '.bll-btn-verify', function() {
        const button = $(this);
        const expId = button.data('experiment-id');
        
        // Disable button and show loading state
        button.prop('disabled', true);
        const originalText = button.html();
        button.html('<span class="bll-btn-icon">⏳</span>Verifying...');
        
        $.post(bllFrontend.ajaxurl, {
            action: 'bll_verify_experiment',
            nonce: bllFrontend.nonce,
            id: expId
        }, function(response) {
            // Re-enable button
            button.prop('disabled', false);
            button.html(originalText);
            
            if (response.success) {
                const data = response.data;
                
                // Build verification result message
                let message = '🔍 Verification Results\n\n';
                
                // Data hash integrity
                message += '📊 Data Hash Integrity:\n';
                if (data.hash_matches) {
                    message += '✅ INTACT - Data matches stored hash\n\n';
                } else {
                    message += '❌ COMPROMISED - Data has been modified!\n\n';
                }
                
                // Blockchain verification
                if (data.blockchain_verified) {
                    message += '⛓️ Blockchain Verification:\n';
                    message += '✅ Transaction verified on Solana\n';
                    message += 'Signature: ' + data.blockchain_signature.substring(0, 20) + '...\n\n';
                    
                    // Blockchain hash match
                    if (data.blockchain_hash_matches) {
                        message += '✅ Blockchain hash matches database\n\n';
                    } else {
                        message += '❌ Blockchain hash MISMATCH!\n\n';
                    }
                } else {
                    message += '⚠️ Blockchain Verification:\n';
                    message += 'Not verified on blockchain (local only)\n\n';
                }
                
                // Overall integrity
                message += '📋 Overall Status:\n';
                if (data.integrity === 'intact') {
                    message += '✅ DATA INTEGRITY CONFIRMED\n';
                    message += 'This experiment\'s data is authentic and unmodified.';
                } else {
                    message += '❌ DATA INTEGRITY COMPROMISED\n';
                    message += 'This experiment\'s data may have been tampered with!';
                }
                
                // Show result
                alert(message);
                
                // Optional: Update row styling based on result
                if (data.integrity === 'intact') {
                    button.closest('tr').addClass('bll-verified-intact').removeClass('bll-verified-compromised');
                } else {
                    button.closest('tr').addClass('bll-verified-compromised').removeClass('bll-verified-intact');
                }
            } else {
                alert('❌ Verification failed:\n\n' + (response.data.message || 'Unknown error'));
            }
        }).fail(function() {
            button.prop('disabled', false);
            button.html(originalText);
            alert('❌ Verification request failed. Please try again.');
        });
    });
    
    function showExperimentDetails(id) {
        const modal = $('#bll-details-modal');
        const content = $('#bll-modal-details-content');
        
        content.html('<div class="bll-loading">Loading experiment details...</div>');
        modal.fadeIn(200);
        
        $.post(bllFrontend.ajaxurl, {
            action: 'bll_get_experiment_details',
            nonce: bllFrontend.nonce,
            id: id
        }, function(response) {
            if (response.success) {
                const exp = response.data;
                const expData = JSON.parse(exp.experiment_data);
                
                let html = '<div class="bll-details-grid">';
                
                // Basic info
                html += '<div class="bll-detail-section">';
                html += '<h4>Basic Information</h4>';
                html += '<dl>';
                html += '<dt>Experiment ID:</dt><dd><code>' + exp.experiment_id + '</code></dd>';
                html += '<dt>Experiment Name:</dt><dd>' + exp.experiment_name + '</dd>';
                html += '<dt>Student:</dt><dd>' + (exp.student_name || '—') + '</dd>';
                html += '<dt>Date Created:</dt><dd>' + exp.created_at + '</dd>';
                html += '</dl>';
                html += '</div>';
                
                // Measurements
                if (expData.measurements && expData.measurements.length > 0) {
                    html += '<div class="bll-detail-section">';
                    html += '<h4>Measurements</h4>';
                    html += '<table class="bll-measurements-table">';
                    html += '<thead><tr><th>Measurement</th><th>Value</th><th>Unit</th></tr></thead>';
                    html += '<tbody>';
                    expData.measurements.forEach(function(m) {
                        html += '<tr>';
                        html += '<td>' + (m.label || '—') + '</td>';
                        html += '<td>' + (m.value || '—') + '</td>';
                        html += '<td>' + (m.unit || '—') + '</td>';
                        html += '</tr>';
                    });
                    html += '</tbody></table>';
                    html += '</div>';
                }
                
                // Observations
                if (expData.observations) {
                    html += '<div class="bll-detail-section">';
                    html += '<h4>Observations</h4>';
                    html += '<p>' + expData.observations + '</p>';
                    html += '</div>';
                }
                
                // Blockchain info
                html += '<div class="bll-detail-section bll-blockchain-section">';
                html += '<h4>Blockchain Information</h4>';
                html += '<dl>';
                
                if (exp.blockchain_verified) {
                    html += '<dt>Status:</dt><dd><span class="bll-status-badge bll-status-verified">✓ Verified</span></dd>';
                    html += '<dt>Transaction:</dt><dd><code>' + exp.blockchain_signature + '</code></dd>';
                    html += '<dt>Data Hash:</dt><dd><code>' + exp.data_hash + '</code></dd>';
                    html += '<dt>Explorer:</dt><dd>';
                    html += '<a href="https://explorer.solana.com/tx/' + exp.blockchain_signature + '?cluster=<?php echo esc_js($network); ?>" target="_blank" class="bll-explorer-link">';
                    html += 'View on Solana Explorer →';
                    html += '</a></dd>';
                } else {
                    html += '<dt>Status:</dt><dd><span class="bll-status-badge bll-status-unverified">⚠ Local Only</span></dd>';
                    html += '<dt>Data Hash:</dt><dd><code>' + exp.data_hash + '</code></dd>';
                    html += '<dt>Note:</dt><dd>This experiment was not verified on the blockchain.</dd>';
                }
                
                html += '</dl>';
                html += '</div>';
                
                html += '</div>';
                
                content.html(html);
            } else {
                content.html('<div class="bll-error">Failed to load experiment details.</div>');
            }
        });
    }
    
    // Close modal
    $('.bll-modal-close, .bll-modal-overlay').on('click', function() {
        $('#bll-details-modal').fadeOut(200);
    });
    
    // Update counts
    function updateCounts() {
        const visibleRows = table.find('tbody tr:visible').length;
        $('#bll-showing-count').text(visibleRows);
    }
});
</script>