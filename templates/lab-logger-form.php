<div class="bll-lab-logger-container">
    <div class="bll-header">
        <p class="bll-description">Log your physics/science experiment data with blockchain verification for data integrity.</p>
    </div>
    
    <form id="bll-experiment-form" class="bll-form">
        <div class="bll-form-group">
            <label for="experiment_name">Experiment Name *</label>
            <input 
                type="text" 
                id="experiment_name" 
                name="experiment_name" 
                required 
                placeholder="e.g., Pendulum Motion Analysis"
            >
        </div>
        
        <div class="bll-form-group">
            <label for="student_name">Student Name</label>
            <input 
                type="text" 
                id="student_name" 
                name="student_name" 
                placeholder="Your name (optional)"
            >
        </div>
        
        <div class="bll-measurements-section">
            <h3>Measurements</h3>
            <div id="bll-measurements-container">
                <div class="bll-measurement-row">
                    <input type="text" name="measurement_label[]" placeholder="Measurement name (e.g., Time)" class="bll-measurement-label">
                    <input type="text" name="measurement_value[]" placeholder="Value" class="bll-measurement-value">
                    <input type="text" name="measurement_unit[]" placeholder="Unit (e.g., seconds)" class="bll-measurement-unit">
                    <button type="button" class="bll-remove-measurement" style="display:none;">×</button>
                </div>
            </div>
            <button type="button" id="bll-add-measurement" class="bll-button-secondary">+ Add Measurement</button>
        </div>
        
        <div class="bll-form-group">
            <label for="observations">Observations / Notes</label>
            <textarea 
                id="observations" 
                name="observations" 
                rows="5" 
                placeholder="Record any observations, anomalies, or notes about the experiment..."
            ></textarea>
        </div>
        
        <div class="bll-blockchain-info">
            <p>
                <span class="bll-icon">🔒</span>
                This experiment will be recorded on the Solana blockchain for data integrity verification.
            </p>
        </div>
        
        <button type="submit" class="bll-submit-button">
            <span class="bll-submit-text">Log Experiment to Blockchain</span>
            <span class="bll-submit-loading" style="display:none;">Processing...</span>
        </button>
        
        <div id="bll-result-message" class="bll-message" style="display:none;"></div>
    </form>
    
    <?php if ($atts['show_history'] === 'yes'): ?>
    <div class="bll-experiments-history">
        <h3>Recent Experiments</h3>
        <div id="bll-experiments-list" class="bll-experiments-list">
            <p class="bll-loading">Loading experiments...</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Add measurement row
    $('#bll-add-measurement').on('click', function() {
        const row = `
            <div class="bll-measurement-row">
                <input type="text" name="measurement_label[]" placeholder="Measurement name" class="bll-measurement-label">
                <input type="text" name="measurement_value[]" placeholder="Value" class="bll-measurement-value">
                <input type="text" name="measurement_unit[]" placeholder="Unit" class="bll-measurement-unit">
                <button type="button" class="bll-remove-measurement">×</button>
            </div>
        `;
        $('#bll-measurements-container').append(row);
        updateRemoveButtons();
    });
    
    // Remove measurement row
    $(document).on('click', '.bll-remove-measurement', function() {
        $(this).closest('.bll-measurement-row').remove();
        updateRemoveButtons();
    });
    
    function updateRemoveButtons() {
        const rows = $('.bll-measurement-row');
        if (rows.length === 1) {
            rows.find('.bll-remove-measurement').hide();
        } else {
            rows.find('.bll-remove-measurement').show();
        }
    }
    
    // Form submission
    $('#bll-experiment-form').on('submit', function(e) {
        e.preventDefault();
        
        const submitButton = $(this).find('.bll-submit-button');
        const submitText = submitButton.find('.bll-submit-text');
        const submitLoading = submitButton.find('.bll-submit-loading');
        const resultMessage = $('#bll-result-message');
        
        // Collect measurements
        const measurements = [];
        $('.bll-measurement-row').each(function() {
            const label = $(this).find('.bll-measurement-label').val();
            const value = $(this).find('.bll-measurement-value').val();
            const unit = $(this).find('.bll-measurement-unit').val();
            
            if (label && value) {
                measurements.push({ label, value, unit });
            }
        });
        
        const formData = {
            action: 'bll_submit_experiment',
            nonce: bllFrontend.nonce,
            experiment_name: $('#experiment_name').val(),
            student_name: $('#student_name').val(),
            measurements: measurements,
            observations: $('#observations').val()
        };
        
        // Show loading state
        submitButton.prop('disabled', true);
        submitText.hide();
        submitLoading.show();
        resultMessage.hide();
        
        $.post(bllFrontend.ajaxurl, formData, function(response) {
            submitButton.prop('disabled', false);
            submitText.show();
            submitLoading.hide();
            
            if (response.success) {
                resultMessage
                    .removeClass('bll-error')
                    .addClass('bll-success')
                    .html(generateSuccessMessage(response.data))
                    .show();
                
                // Reset form
                $('#bll-experiment-form')[0].reset();
                $('#bll-measurements-container').html(`
                    <div class="bll-measurement-row">
                        <input type="text" name="measurement_label[]" placeholder="Measurement name" class="bll-measurement-label">
                        <input type="text" name="measurement_value[]" placeholder="Value" class="bll-measurement-value">
                        <input type="text" name="measurement_unit[]" placeholder="Unit" class="bll-measurement-unit">
                        <button type="button" class="bll-remove-measurement" style="display:none;">×</button>
                    </div>
                `);
                
                // Reload experiments list
                loadExperiments();
            } else {
                resultMessage
                    .removeClass('bll-success')
                    .addClass('bll-error')
                    .html('<strong>Error:</strong> ' + response.data.message)
                    .show();
            }
        });
    });
    
    function generateSuccessMessage(data) {
        let message = '<strong>✅ Experiment logged successfully!</strong><br>';
        message += '<strong>Experiment ID:</strong> ' + data.experiment_id + '<br>';
        
        if (data.blockchain_verified) {
            message += '<strong>Blockchain Status:</strong> <span class="bll-verified">Verified ✓</span><br>';
            message += '<strong>Transaction:</strong> <code>' + data.blockchain_signature.substring(0, 20) + '...</code><br>';
            message += '<a href="' + data.explorer_url + '" target="_blank" class="bll-explorer-link">View on Solana Explorer →</a>';
        } else {
            message += '<strong>Status:</strong> <span class="bll-warning">Saved locally (blockchain logging failed)</span>';
        }
        
        return message;
    }
    
    // Load experiments list
    function loadExperiments() {
        $.post(bllFrontend.ajaxurl, {
            action: 'bll_get_experiments',
            nonce: bllFrontend.nonce,
            limit: 10
        }, function(response) {
            if (response.success) {
                displayExperiments(response.data);
            }
        });
    }
    
    function displayExperiments(experiments) {
        const container = $('#bll-experiments-list');
        
        if (experiments.length === 0) {
            container.html('<p class="bll-no-experiments">No experiments logged yet.</p>');
            return;
        }
        
        let html = '<table class="bll-experiments-table"><thead><tr>';
        html += '<th>Date</th><th>Experiment</th><th>Student</th><th>Blockchain</th><th>Actions</th>';
        html += '</tr></thead><tbody>';
        
        experiments.forEach(function(exp) {
            const date = new Date(exp.created_at).toLocaleString();
            const verified = exp.blockchain_verified ? 
                '<span class="bll-badge bll-badge-success">Verified</span>' :
                '<span class="bll-badge bll-badge-warning">Local Only</span>';
            
            // Make experiment name clickable if verified
            let experimentNameHtml = exp.experiment_name;
            if (exp.blockchain_verified && exp.blockchain_signature) {
                const network = '<?php echo esc_js(get_option("bll_network", "devnet")); ?>';
                const explorerUrl = 'https://explorer.solana.com/tx/' + exp.blockchain_signature + '?cluster=' + network;
                experimentNameHtml = '<a href="' + explorerUrl + '" target="_blank" class="bll-experiment-link">' + exp.experiment_name + '</a>';
            }
            
            html += '<tr>';
            html += '<td>' + date + '</td>';
            html += '<td>' + experimentNameHtml + '</td>';
            html += '<td>' + (exp.student_name || '-') + '</td>';
            html += '<td>' + verified + '</td>';
            html += '<td><button class="bll-verify-btn" data-id="' + exp.id + '">Verify</button></td>';
            html += '</tr>';
        });
        
        html += '</tbody></table>';
        container.html(html);
    }
    
    // Verify experiment
    $(document).on('click', '.bll-verify-btn', function() {
        const id = $(this).data('id');
        const button = $(this);
        
        button.prop('disabled', true).text('Verifying...');
        
        $.post(bllFrontend.ajaxurl, {
            action: 'bll_verify_experiment',
            nonce: bllFrontend.nonce,
            id: id
        }, function(response) {
            button.prop('disabled', false).text('Verify');
            
            if (response.success) {
                const data = response.data;
                let message = 'Verification Results:\n\n';
                message += 'Data Integrity: ' + (data.hash_matches ? '✅ INTACT' : '❌ COMPROMISED') + '\n';
                message += 'Overall Status: ' + data.integrity.toUpperCase();
                
                alert(message);
            } else {
                alert('Verification failed: ' + response.data.message);
            }
        });
    });
    
    // Initial load
    <?php if ($atts['show_history'] === 'yes'): ?>
    loadExperiments();
    <?php endif; ?>
});
</script>