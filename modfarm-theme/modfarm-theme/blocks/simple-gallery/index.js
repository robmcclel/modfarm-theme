(function () {
  const { registerBlockType } = wp.blocks;
  const { createElement: el, Fragment, useEffect, useRef, useCallback } = wp.element;
  const { subscribe, select, dispatch } = wp.data;
  const {
    PanelBody, SelectControl, RangeControl, Button, Spinner, Notice, Tooltip, ToggleControl,
    ToolbarGroup, ToolbarButton
  } = wp.components;
  const { MediaUpload, InspectorControls, useBlockProps, BlockControls } = wp.blockEditor || wp.editor;
  const ServerSideRender = wp.serverSideRender;
  const { __ } = wp.i18n;

  // Open Inspector in post & site editors
  function openInspectorSidebar() {
    try {
      const iface = dispatch('core/interface');
      if (iface?.enableComplementaryArea) {
        iface.enableComplementaryArea('core/edit-post', 'edit-post/block-inspector');
        iface.enableComplementaryArea('core/edit-site', 'edit-site/block-inspector');
      }
      const ep = dispatch('core/edit-post'); if (ep?.openGeneralSidebar) ep.openGeneralSidebar('edit-post/block');
      const es = dispatch('core/edit-site'); if (es?.openGeneralSidebar) es.openGeneralSidebar('edit-site/block-inspector');
    } catch(e){}
  }

  function getImageSizes() {
    const s = (wp.data && wp.data.select && wp.data.select('core/block-editor') && wp.data.select('core/block-editor').getSettings()) || {};
    const sizes = (s.imageSizes || []).map(i => i.slug);
    const set = sizes.length ? sizes : ['thumbnail','medium','large','full'];
    return set.map(slug => ({ label: slug, value: slug }));
  }

  function Thumb(p) {
    const img = p.img;
    const preview = img && (img.url || (img.sizes && img.sizes.thumbnail && img.sizes.thumbnail.url)) || '';
    return el('div', { className:'mfsg-admin-thumb' },
      preview ? el('img', { src: preview, alt: img.alt || '' }) : el(Spinner, null),
      el('div', { className:'mfsg-admin-actions' },
        el(Tooltip, { text: __('Move left','mfsg') },
          el(Button, { icon:'arrow-left', disabled:p.index===0, onClick: p.onMoveLeft })
        ),
        el(Tooltip, { text: __('Move right','mfsg') },
          el(Button, { icon:'arrow-right', disabled:p.index===p.total-1, onClick: p.onMoveRight })
        ),
        el(Tooltip, { text: __('Remove','mfsg') },
          el(Button, { icon:'no', isDestructive:true, onClick:p.onRemove })
        )
      )
    );
  }

  // Inline editor CSS (thumb grid only shows when selected anyway)
  const editorCSS = `
  .mfsg-editor-wrap { position: relative; }
  .mfsg-admin { display:flex; flex-direction:column; gap:12px; }
  .mfsg-admin__grid { display:grid; gap:8px; grid-template-columns: repeat(auto-fill, minmax(96px,1fr)); }
  .mfsg-admin-thumb { position:relative; border:1px dashed #c7c7c7; padding:6px; border-radius:8px; background:#fff; }
  .mfsg-admin-thumb img { display:block; width:100%; height:auto; border-radius:6px; }
  .mfsg-admin-actions { display:flex; gap:6px; justify-content:space-between; margin-top:6px; }
  `;
  (function injectOnce(){
    if (document.getElementById('mfsg-admin-css')) return;
    const style = document.createElement('style');
    style.id = 'mfsg-admin-css';
    style.innerHTML = editorCSS;
    document.head.appendChild(style);
  })();

  registerBlockType('modfarm/simple-gallery', {
    title: 'Simple Gallery (ModFarm)',
    icon: 'format-gallery',
    category: 'media',
    supports: { html: false },
    attributes: {
      images:       { type:'array',  default: [] }, // [{id,url,alt}]
      columns:      { type:'number', default: 3 },
      size:         { type:'string', default: 'medium' },
      linkMode:     { type:'string', default: 'lightbox' }, // lightbox|file|none
      responsive:   { type:'boolean', default: true },
      visibleCount: { type:'number', default: 0 },          // 0 = show all
    },

    edit: (props) => {
      const { attributes, setAttributes, clientId, isSelected } = props;
      const { images, columns, size, linkMode, responsive, visibleCount } = attributes;

      // Make the block reliably selectable & open inspector on selection
      const blockProps = useBlockProps({
        className: 'mfsg-editor-wrap',
        onMouseDown: () => {
          const sel = select('core/block-editor').getSelectedBlockClientId?.();
          if (sel !== clientId) dispatch('core/block-editor').selectBlock(clientId);
        },
        onClick: () => openInspectorSidebar()
      });

      // Auto-open when selected via keyboard/ListView
      const openedRef = useRef(false);
      useEffect(() => {
        if (isSelected && !openedRef.current) { openInspectorSidebar(); openedRef.current = true; }
        if (!isSelected && openedRef.current) { openedRef.current = false; }
      }, [isSelected]);
      useEffect(() => {
        const unsub = subscribe(() => {
          const sel = select('core/block-editor').getSelectedBlockClientId?.();
          if (sel === clientId && !openedRef.current) { openInspectorSidebar(); openedRef.current = true; }
          if (sel !== clientId && openedRef.current) { openedRef.current = false; }
        });
        return () => unsub && unsub();
      }, [clientId]);

      // Media & ordering
      function onSelectImages(newImages) {
        const norm = (newImages || []).map(m => ({ id:m.id, url:m.url, alt:m.alt }));
        setAttributes({ images: norm });
      }
      function removeAt(i) { const next = images.slice(); next.splice(i,1); setAttributes({ images: next }); }
      function moveLeft(i) { if (i<=0) return; const n = images.slice(); [n[i-1], n[i]] = [n[i], n[i-1]]; setAttributes({ images:n }); }
      function moveRight(i){ if (i>=images.length-1) return; const n = images.slice(); [n[i], n[i+1]] = [n[i+1], n[i]]; setAttributes({ images:n }); }

      // Toolbar actions (cleaner than buttons inside canvas)
      const toolbar = el(BlockControls, {},
        el(ToolbarGroup, {},
          el(MediaUpload, {
            multiple:true, gallery:true, allowedTypes:['image'],
            value: images.map(i=>i.id),
            onSelect: onSelectImages,
            render: ({ open }) => el(ToolbarButton, { icon:'update', label: __('Replace images','mfsg'), onClick: open })
          }),
          images.length
            ? el(ToolbarButton, {
                icon:'trash', label: __('Clear','mfsg'),
                isDestructive:true, onClick: ()=> setAttributes({ images: [] })
              })
            : null
        )
      );

      // Sidebar controls
      const sidebar = el(InspectorControls, {},
        el(PanelBody, { title: __('Gallery Settings','mfsg'), initialOpen: true },
          el(RangeControl, { label: __('Columns','mfsg'), min:1, max:8, value: columns, onChange:(v)=> setAttributes({ columns:v }) }),
          el(SelectControl, { label: __('Image size','mfsg'), value: size, options: getImageSizes(), onChange:(v)=> setAttributes({ size:v }) }),
          el(SelectControl, {
            label: __('Link behavior','mfsg'), value: linkMode,
            options: [
              { label: __('Lightbox','mfsg'), value:'lightbox' },
              { label: __('Open file (new tab)','mfsg'), value:'file' },
              { label: __('None','mfsg'), value:'none' }
            ],
            onChange:(v)=> setAttributes({ linkMode:v })
          }),
          el(ToggleControl, {
            label: __('Responsive layout','mfsg'),
            checked: !!responsive,
            help: responsive ? __('Columns may reduce on small screens.','mfsg') : __('Columns stay fixed at all sizes.','mfsg'),
            onChange:(v)=> setAttributes({ responsive: !!v })
          }),
          el(RangeControl, {
            label: __('Visible thumbnails','mfsg'),
            help: __('0 shows all thumbnails; extra images are lightbox-only.','mfsg'),
            min: 0, max: Math.max(0, images.length), value: visibleCount,
            onChange:(v)=> setAttributes({ visibleCount: Number(v)||0 })
          })
        )
      );

      // “Busy” admin grid (ONLY when selected)
      const adminGrid = isSelected && images.length
        ? el('div', { className:'mfsg-admin' },
            el('div', { className:'mfsg-admin__grid' },
              images.map((im, i) => el(Thumb, {
                key:i, img:im, index:i, total:images.length,
                onRemove: ()=> removeAt(i),
                onMoveLeft: ()=> moveLeft(i),
                onMoveRight: ()=> moveRight(i),
              }))
            )
          )
        : null;

      // Live server-side preview (always shown)
      const preview = images.length
        ? el(ServerSideRender, { block:'modfarm/simple-gallery', attributes })
        : el(Notice, { status:'info', isDismissible:false }, __('Select images to build your gallery.','mfsg'));

      // When not selected -> user sees only preview (front-end look).
      // When selected -> toolbar + inspector + admin grid appear; preview still visible.
      return el('div', { ...blockProps },
        toolbar,
        sidebar,
        preview,
        adminGrid
      );
    },

    save: () => null // server-rendered
  });
})();