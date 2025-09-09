jQuery(document).ready(function($) {
    // Google Drive OAuth Integration
    $('#zc-dmt-connect-drive').on('click', function(e) {
        e.preventDefault();

        // Initialize Google API
        gapi.load('client:auth2', function() {
            gapi.client.init({
                apiKey: '',
                clientId: zcDmtBackup.oauthClientId || 'YOUR_GOOGLE_CLIENT_ID',
                scope: 'https://www.googleapis.com/auth/drive.file'
            }).then(function() {
                // Sign in to Google
                return gapi.auth2.getAuthInstance().signIn();
            }).then(function(user) {
                var profile = user.getBasicProfile();
                var token = user.getAuthResponse().access_token;

                // Send token to WordPress via AJAX
                $.post(zcDmtBackup.ajaxUrl, {
                    action: 'zc_dmt_save_drive_token',
                    nonce: zcDmtBackup.nonce,
                    token: token,
                    email: profile.getEmail(),
                    name: profile.getName()
                }, function(response) {
                    if (response.success) {
                        alert('Google Drive connected successfully!');
                        location.reload();
                    } else {
                        alert('Connection failed: ' + response.data);
                    }
                });
            }).catch(function(error) {
                console.error('OAuth Error:', error);
                alert('Google Drive connection failed');
            });
        });
    });

    // Manual backup trigger
    $(document).on('click', '#zc-dmt-backup-now', function(e) {
        e.preventDefault();
        var btn = $(this);
        btn.prop('disabled', true).text('Creating Backup...');

        $.post(zcDmtBackup.ajaxUrl, {
            action: 'zc_dmt_manual_backup',
            nonce: zcDmtBackup.nonce
        }, function(response) {
            btn.prop('disabled', false).text('Backup Now');
            if (response.success) {
                alert('Backup created successfully!');
            } else {
                alert('Backup failed: ' + response.data);
            }
        });
    });
});