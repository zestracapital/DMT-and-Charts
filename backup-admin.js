jQuery(document).ready(function($) {
    console.log('ZC DMT: backup-admin.js loaded');

    // Check if required objects exist
    if (typeof zcDmtBackup === 'undefined') {
        console.error('ZC DMT: zcDmtBackup object not found');
        return;
    }

    console.log('ZC DMT: Client ID:', zcDmtBackup.oauthClientId);

    $('#zc-dmt-connect-drive').on('click', function(e) {
        e.preventDefault();
        console.log('ZC DMT: Connect button clicked');

        if (typeof gapi === 'undefined') {
            console.error('ZC DMT: Google API (gapi) not loaded');
            alert('Google API not loaded. Please refresh the page.');
            return;
        }

        console.log('ZC DMT: Loading gapi client and auth2');
        gapi.load('client:auth2', function() {
            console.log('ZC DMT: gapi client:auth2 loaded');

            gapi.client.init({
                apiKey: '',
                clientId: zcDmtBackup.oauthClientId,
                scope: 'https://www.googleapis.com/auth/drive.file'
            }).then(function() {
                console.log('ZC DMT: gapi client initialized');
                return gapi.auth2.getAuthInstance().signIn();
            }).then(function(user) {
                console.log('ZC DMT: User signed in successfully');
                var profile = user.getBasicProfile();
                var token = user.getAuthResponse().access_token;

                console.log('ZC DMT: Sending token to server');
                $.post(zcDmtBackup.ajaxUrl, {
                    action: 'zc_dmt_save_drive_token',
                    nonce: zcDmtBackup.nonce,
                    token: token,
                    email: profile.getEmail(),
                    name: profile.getName()
                }, function(response) {
                    console.log('ZC DMT: Server response:', response);
                    if (response.success) {
                        alert('Google Drive connected successfully!');
                        location.reload();
                    } else {
                        alert('Connection failed: ' + (response.data || 'Unknown error'));
                    }
                }).fail(function(xhr, status, error) {
                    console.error('ZC DMT: AJAX error:', status, error);
                    alert('AJAX request failed');
                });
            }).catch(function(error) {
                console.error('ZC DMT: OAuth Error:', error);
                alert('Google Drive connection failed: ' + error.error);
            });
        });
    });
});