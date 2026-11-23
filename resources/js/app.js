import './bootstrap';
import { Html5Qrcode } from 'html5-qrcode';
import Chart from 'chart.js/auto';

window.Chart = Chart;

window.kukangQrScanner = function () {
    return {
        html5QrCode: null,
        scanning: false,
        cameraError: null,
        manualInput: '',

        async startScanner() {
            this.cameraError = null;
            if (this.scanning) return;

            try {
                this.html5QrCode = new Html5Qrcode('qr-reader');
                this.scanning = true;

                await this.html5QrCode.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 230, height: 230 } },
                    async (decodedText) => {
                        await this.stopScanner();
                        this.$wire.verifyScannedToken(decodedText, 'qr_scan');
                    },
                    () => {}
                );
            } catch (err) {
                this.scanning = false;
                this.cameraError = 'Akses kamera gagal. Pastikan izin kamera diberikan dan akses menggunakan HTTPS atau localhost, atau gunakan kode manual di bawah.';
            }
        },

        async stopScanner() {
            if (this.html5QrCode && this.scanning) {
                try {
                    await this.html5QrCode.stop();
                    await this.html5QrCode.clear();
                } catch (_) {}
            }
            this.scanning = false;
        }
    };
};
