function resetz() {
    mc.resetZoom();
}
function toggle() {
    mc.data.datasets.forEach(function(ds) {
        ds.hidden = !ds.hidden;
    });
    mc.update();
}