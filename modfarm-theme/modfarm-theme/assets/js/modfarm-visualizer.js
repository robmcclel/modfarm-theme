(function () {
  'use strict';
  const devices = Object.freeze({ desktop: {width:1440,height:900}, tablet: {width:768,height:1024}, mobile: {width:390,height:844} });
  function measure(availableWidth, device) {
    const dimensions = devices[device] || devices.desktop;
    const scale = Math.min(1, Math.max(1, availableWidth - 32) / (devices.desktop.width + 32));
    return { ...dimensions, scale, outerWidth:(dimensions.width + 32) * scale, outerHeight:(dimensions.height + 42) * scale, stageHeight:(devices.tablet.height + 42) * scale + 32 };
  }
  function fit({container, stage, frame, viewport, note, device}) {
    if (!container || !stage || !frame || !viewport) return;
    const geometry = measure(container.clientWidth, device);
    container.dataset.device = device;
    frame.dataset.device = device;
    frame.style.width = geometry.width + 'px';
    frame.style.boxSizing = 'content-box';
    viewport.style.width = geometry.width + 'px';
    viewport.style.height = geometry.height + 'px';
    frame.style.transformOrigin = 'top left';
    frame.style.transform = 'scale(' + geometry.scale + ')';
    stage.style.width = geometry.outerWidth + 'px';
    stage.style.height = geometry.outerHeight + 'px';
    container.style.minHeight = geometry.stageHeight + 'px';
    if (note) note.textContent = device.charAt(0).toUpperCase() + device.slice(1) + ' preview · ' + geometry.width + ' × ' + geometry.height + ' px viewport · ' + Math.round(geometry.scale * 100) + '% shared scale';
    return geometry;
  }
  window.ModFarmVisualizer = Object.freeze({devices, measure, fit});
})();
