
import './bootstrap';


// Send original room photographs separately to stay within PHP's request limit.
window.roomPhotoUpload = () => ({
    uploadError: '',
    uploading: false,
    progress: '',
    async upload(event) {
        if (this.uploading) return;
        const files = Array.from(this.$refs.photos.files);
        this.uploadError = '';
        if (!files.length || files.length > 10) {
            this.uploadError = 'Choose between 1 and 10 photographs.';
            return;
        }
        if (files.some(file => file.size > 30 * 1024 * 1024)) {
            this.uploadError = 'Each photograph must be 30 MB or smaller.';
            return;
        }
        this.uploading = true;
        let completed = 0;
        try {
            for (const file of files) {
                this.progress = `Uploading ${completed + 1} of ${files.length}…`;
                const body = new FormData();
                body.append('_token', event.target.querySelector('[name="_token"]').value);
                body.append('images[]', file);
                const response = await fetch(event.target.action, {
                    method: 'POST', body, credentials: 'same-origin',
                    headers: { Accept: 'application/json' },
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(Object.values(data.errors || {}).flat().join(' ') || data.message || 'Upload failed. Please try again.');
                }
                completed++;
            }
            window.location.reload();
        } catch (error) {
            this.uploadError = `${completed} of ${files.length} photographs uploaded. ${error.message} Reload to see saved photographs; retry only the remaining files.`;
        } finally {
            this.uploading = false;
        }
    },
});

if (document.getElementById('room-calendar')) {
    import('./room-calendar');
}
