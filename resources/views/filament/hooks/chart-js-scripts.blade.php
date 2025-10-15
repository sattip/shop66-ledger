<script>
console.log('[ChartJS] Loading Chart.js from CDN...');
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
console.log('[ChartJS] Chart.js loaded:', typeof Chart !== 'undefined');
if (typeof Chart !== 'undefined') {
    console.log('[ChartJS] Chart.js version:', Chart.version);
}
</script>
