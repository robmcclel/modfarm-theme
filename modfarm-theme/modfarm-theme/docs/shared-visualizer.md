# Shared ModFarm visualizer

Theme provides the `modfarm-visualizer` admin script and `window.ModFarmVisualizer` contract. `devices` defines desktop 1440×900, tablet 768×1024, mobile 390×844. `measure(availableWidth, device)` calculates one desktop-based scale for all devices and reserves consistent stage space. `fit({container,stage,frame,viewport,note,device})` applies those dimensions and the shared scale. Content adapters own rendering and device selection; the helper owns viewport geometry.

Settings uses one stage, frame, viewport and device toolbar for theme/book samples and rendered PPB layouts. The old PPB-specific device toolbar and scaling handler are removed from the enhanced UI. Appearance samples remain samples; unifying their geometry does not make them a live frontend rendering.

Core Presentation consumes this same service when the ModFarm Theme registers it. On other themes, its existing Presentation geometry remains a compatibility fallback. Deploy Theme first, then the Core adoption PR. No saved settings change.

Validation: JavaScript syntax, PHP parser checks, form-preservation and hierarchy DOM integration tests passed. Geometry checks verify equal scale across devices, fixed viewport sizes, and proportionally smaller phones. Browser visual acceptance remains required on Dev: compare mobile, tablet and desktop between Settings appearance, Settings layout and Content Presentation at the same admin width.

## Plugin block styles in Settings layout previews

Settings renders selected PPB patterns inside a standalone document. During that render, it isolates the WordPress style queue and captures the block-requested frontend styles using a cloned WordPress style printer. Dependencies, versioned URLs, RTL styles, and inline CSS follow WordPress handling. Admin styles are excluded, and the original queue and printer state are retained even if rendering fails. This includes Webcomic Reader Stage/Reader and Series styles without hardcoding plugin paths into the Theme. Preview scripts are not executed by this style capture.

Run `php tests/ppb-visualizer-assets.php` for queue isolation, repeated previews, dependency/inline styles, and failure recovery. Review Series and Episodes in Settings at each device size on staging; these isolated checks do not replace a live visual review.
